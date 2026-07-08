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

use OpenDxp\Model\Tool\SettingsStore;

/**
 * Persists the theme token overrides in the OpenDXP settings store. Only overridden tokens are
 * stored; an empty override set removes the entry entirely so `cui_theme_tokens()` can skip the
 * stylesheet link when the theme is untouched.
 */
final class ThemeSettings
{
    private const SCOPE = 'combat_ui';

    private const ID = 'theme_tokens';

    /**
     * @return array<string, array<string, string>>
     */
    public function getOverrides(): array
    {
        $entry = SettingsStore::get(self::ID, self::SCOPE);
        if ($entry === null) {
            return [];
        }

        $decoded = json_decode((string) $entry->getData(), true);
        $tokens = is_array($decoded) ? ($decoded['tokens'] ?? null) : null;

        return is_array($tokens) ? $tokens : [];
    }

    /**
     * @param array<string, array<string, string>> $tokens normalized overrides
     */
    public function saveOverrides(array $tokens): void
    {
        if ($tokens === []) {
            SettingsStore::delete(self::ID, self::SCOPE);

            return;
        }

        SettingsStore::set(self::ID, json_encode(['tokens' => $tokens], JSON_THROW_ON_ERROR), 'string', self::SCOPE);
    }

    /**
     * Cache-busting hash of the current overrides, or null when nothing is overridden.
     */
    public function getVersionHash(): ?string
    {
        $overrides = $this->getOverrides();
        if ($overrides === []) {
            return null;
        }

        return substr(md5((string) json_encode($overrides)), 0, 12);
    }
}
