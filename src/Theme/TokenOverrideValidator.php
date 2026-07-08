<?php

declare(strict_types=1);

/*
 * Combat UI OpenDXP Bundle
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * @copyright Copyright (c) 2026 Combat Jongerenmarketing en -communicatie B.V. (https://www.combat.nl)
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 */

namespace CombatUI\CombatUIOpenDxpBundle\Theme;

/**
 * Normalizes and validates token override payloads coming from the theme editor before they are
 * persisted and later interpolated into a stylesheet: only tokens known to the schema are
 * accepted, color tokens take light/dark slots while every other token takes a single value slot,
 * and values that could break out of a CSS custom-property declaration are rejected.
 */
final class TokenOverrideValidator
{
    private const FORBIDDEN_SUBSTRINGS = [';', '{', '}', '<', '>', '\\', '/*', '*/', '@', 'url('];

    private const MAX_VALUE_LENGTH = 400;

    public function __construct(private readonly TokenSchemaProvider $schema)
    {
    }

    /**
     * @param array<string, mixed> $tokens
     *
     * @return array<string, array<string, string>> cleaned overrides; empty values are dropped
     *
     * @throws \InvalidArgumentException when a token, slot or value is not acceptable
     */
    public function normalize(array $tokens): array
    {
        $known = $this->schema->getTokens();

        $result = [];
        foreach ($tokens as $name => $slots) {
            if (!isset($known[$name])) {
                throw new \InvalidArgumentException(sprintf('Unknown design token "%s".', $name));
            }
            if (!is_array($slots)) {
                throw new \InvalidArgumentException(sprintf('Override for "%s" must be an object of values.', $name));
            }

            $allowedSlots = $known[$name]['kind'] === 'color' ? ['light', 'dark'] : ['value'];
            $clean = [];
            foreach ($slots as $slot => $value) {
                if (!in_array($slot, $allowedSlots, true)) {
                    throw new \InvalidArgumentException(sprintf('Unknown value slot "%s" for token "%s".', $slot, $name));
                }
                if (!is_string($value)) {
                    throw new \InvalidArgumentException(sprintf('Value for token "%s" must be a string.', $name));
                }

                $value = trim((string) preg_replace('/\s+/', ' ', $value));
                if ($value === '') {
                    continue;
                }

                $this->assertSafeCssValue($name, $value);
                $clean[$slot] = $value;
            }

            if ($clean !== []) {
                $result[$name] = $clean;
            }
        }

        return $result;
    }

    private function assertSafeCssValue(string $name, string $value): void
    {
        if (mb_strlen($value) > self::MAX_VALUE_LENGTH) {
            throw new \InvalidArgumentException(sprintf('Value for token "%s" is too long.', $name));
        }

        foreach (self::FORBIDDEN_SUBSTRINGS as $forbidden) {
            if (stripos($value, $forbidden) !== false) {
                throw new \InvalidArgumentException(sprintf(
                    'Value for token "%s" contains the forbidden sequence "%s".',
                    $name,
                    $forbidden,
                ));
            }
        }
    }
}
