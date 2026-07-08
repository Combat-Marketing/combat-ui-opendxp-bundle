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

namespace CombatUI\CombatUIOpenDxpBundle\Controller;

use CombatUI\CombatUIOpenDxpBundle\Theme\ThemeCssGenerator;
use CombatUI\CombatUIOpenDxpBundle\Theme\ThemeSettings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Serves the generated token-override stylesheet. `cui_theme_tokens()` links it with the current
 * override hash as `?ver=` (`v` is reserved by OpenDXP's public document-version preview and its
 * ElementListener rejects non-integer values), so a matching request may be cached immutably for a
 * year; any other
 * request revalidates via ETag.
 */
final class ThemeCssController
{
    public function __construct(
        private readonly ThemeSettings $settings,
        private readonly ThemeCssGenerator $generator,
    ) {
    }

    #[Route('/cui/theme.css', name: 'combat_ui_theme_css', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $css = $this->generator->generate($this->settings->getOverrides());

        $response = new Response($css, Response::HTTP_OK, ['Content-Type' => 'text/css; charset=UTF-8']);
        $response->setPublic();
        $response->setEtag(md5($css));

        $hash = $this->settings->getVersionHash();
        if ($hash !== null && $request->query->getString('ver') === $hash) {
            $response->setMaxAge(31536000);
            $response->setImmutable();
        } else {
            $response->setMaxAge(0);
            $response->headers->addCacheControlDirective('must-revalidate');
        }

        $response->isNotModified($request);

        return $response;
    }
}
