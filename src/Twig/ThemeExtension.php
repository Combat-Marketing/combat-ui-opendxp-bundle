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

namespace CombatUI\CombatUIOpenDxpBundle\Twig;

use CombatUI\CombatUIOpenDxpBundle\Theme\ThemeSettings;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * `cui_theme_tokens()` renders the stylesheet link for the theme-editor overrides. Place it after
 * `cui_assets()` in the layout so the overrides win over the framework tokens by source order.
 * Renders nothing while no token is overridden.
 */
final class ThemeExtension extends AbstractExtension
{
    public function __construct(
        private readonly ThemeSettings $settings,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cui_theme_tokens', $this->renderThemeTokensLink(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderThemeTokensLink(): string
    {
        $hash = $this->settings->getVersionHash();
        if ($hash === null) {
            return '';
        }

        $url = $this->urlGenerator->generate('combat_ui_theme_css', ['ver' => $hash]);

        return sprintf('<link rel="stylesheet" href="%s">', htmlspecialchars($url, ENT_QUOTES));
    }
}
