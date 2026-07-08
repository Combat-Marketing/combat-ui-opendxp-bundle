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

use Composer\InstalledVersions;

/**
 * Shapes the token groups from the Combat UI blocks manifest (blocks.json, schema >= 1.2.0) into
 * the schema the theme editor works with. The three framework color groups (color-base,
 * color-dark, color-light) are merged into a single "colors" group whose tokens carry a light and
 * a dark default; every other group keeps a single default value per token.
 *
 * @phpstan-type ColorToken array{name: string, description: string, light: string, dark: string}
 * @phpstan-type ValueToken array{name: string, description: string, value: string}
 * @phpstan-type TokenGroup array{name: string, label: string, kind: 'color'|'value', summary: string, tokens: list<ColorToken>|list<ValueToken>}
 */
final class TokenSchemaProvider
{
    private const COLOR_GROUPS = ['color-base', 'color-dark', 'color-light'];

    private const GROUP_LABELS = [
        'typography' => 'Typography',
        'colors' => 'Colors',
        'spacing' => 'Spacing',
        'section-spacing' => 'Section spacing',
        'surface' => 'Surfaces',
        'control' => 'Controls',
        'containers' => 'Containers',
        'content-measures' => 'Content measures',
        'layout' => 'Layout primitives',
        'shell' => 'App shell',
    ];

    /** @var list<TokenGroup>|null */
    private ?array $groups = null;

    public function __construct(private readonly ?string $blocksManifestPath = null)
    {
    }

    /**
     * @return list<TokenGroup>
     */
    public function getGroups(): array
    {
        return $this->groups ??= $this->buildGroups();
    }

    /**
     * Flat token index for validation, keyed by token name.
     *
     * @return array<string, array{kind: 'color'|'value'}>
     */
    public function getTokens(): array
    {
        $tokens = [];
        foreach ($this->getGroups() as $group) {
            foreach ($group['tokens'] as $token) {
                $tokens[$token['name']] = ['kind' => $group['kind']];
            }
        }

        return $tokens;
    }

    /**
     * @return list<TokenGroup>
     */
    private function buildGroups(): array
    {
        $manifestPath = $this->resolveManifestPath();
        $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($manifest) || !is_array($manifest['tokenGroups'] ?? null)) {
            throw new \RuntimeException(sprintf('Blocks manifest "%s" has no tokenGroups.', $manifestPath));
        }

        $groups = [];
        $colorSources = [];
        $colorsPosition = null;
        foreach ($manifest['tokenGroups'] as $tokenGroup) {
            $name = (string) ($tokenGroup['name'] ?? '');
            if (in_array($name, self::COLOR_GROUPS, true)) {
                $colorSources[$name] = $tokenGroup;
                // The merged colors group takes the position of color-base in the source order.
                $colorsPosition ??= $name === 'color-base' ? count($groups) : null;

                continue;
            }

            $groups[] = [
                'name' => $name,
                'label' => $this->labelFor($name),
                'kind' => 'value',
                'summary' => (string) ($tokenGroup['summary'] ?? ''),
                'tokens' => array_values(array_map(
                    static fn (array $token): array => [
                        'name' => (string) $token['name'],
                        'description' => (string) ($token['description'] ?? ''),
                        'value' => (string) ($token['value'] ?? ''),
                    ],
                    $tokenGroup['tokens'] ?? [],
                )),
            ];
        }

        if ($colorSources !== []) {
            array_splice($groups, $colorsPosition ?? count($groups), 0, [$this->buildColorsGroup($colorSources)]);
        }

        return $groups;
    }

    /**
     * @param array<string, array<string, mixed>> $sources
     *
     * @return TokenGroup
     */
    private function buildColorsGroup(array $sources): array
    {
        $tokens = [];
        // color-base provides light defaults and canonical order; color-dark provides dark defaults
        // and appends the inverse-* tokens; color-light fills light defaults for tokens that only
        // exist in the theme override blocks.
        $slotByGroup = ['color-base' => 'light', 'color-dark' => 'dark', 'color-light' => 'light'];
        foreach ($slotByGroup as $groupName => $slot) {
            foreach ($sources[$groupName]['tokens'] ?? [] as $token) {
                $name = (string) $token['name'];
                $tokens[$name] ??= ['name' => $name, 'description' => '', 'light' => '', 'dark' => ''];
                if ($tokens[$name]['description'] === '') {
                    $tokens[$name]['description'] = (string) ($token['description'] ?? '');
                }
                if ($tokens[$name][$slot] === '') {
                    $tokens[$name][$slot] = (string) ($token['value'] ?? '');
                }
            }
        }

        return [
            'name' => 'colors',
            'label' => $this->labelFor('colors'),
            'kind' => 'color',
            'summary' => 'Color tokens. Each token has a light-theme and a dark-theme value.',
            'tokens' => array_values($tokens),
        ];
    }

    private function labelFor(string $name): string
    {
        return self::GROUP_LABELS[$name] ?? ucfirst(str_replace('-', ' ', $name));
    }

    private function resolveManifestPath(): string
    {
        $candidates = [];
        if ($this->blocksManifestPath !== null) {
            $candidates[] = $this->blocksManifestPath;
        } else {
            if (InstalledVersions::isInstalled('combat-ui/core-bundle')) {
                $installPath = InstalledVersions::getInstallPath('combat-ui/core-bundle');
                if ($installPath !== null) {
                    $candidates[] = $installPath . '/public/build/blocks.json';
                }
            }
            // Sibling checkouts cover development setups where the released core-bundle package
            // does not ship the manifest yet (mirrors tests/Support/Twig/BrickTwigEnvironment).
            $candidates[] = \dirname(__DIR__, 3) . '/combat-ui-bundle/public/build/blocks.json';
            $candidates[] = \dirname(__DIR__, 3) . '/combat-ui-core/dist/blocks.json';
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new \RuntimeException(sprintf(
            'Combat UI blocks manifest not found (tried: %s). Ship blocks.json with the core bundle build or configure the path explicitly.',
            implode(', ', $candidates),
        ));
    }
}
