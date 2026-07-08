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

namespace CombatUI\CombatUIOpenDxpBundle\Controller\Admin;

use CombatUI\CombatUIOpenDxpBundle\Theme\ThemeSettings;
use CombatUI\CombatUIOpenDxpBundle\Theme\TokenOverrideValidator;
use CombatUI\CombatUIOpenDxpBundle\Theme\TokenSchemaProvider;
use OpenDxp\Cache;
use OpenDxp\Controller\Traits\JsonHelperTrait;
use OpenDxp\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Backend endpoints for the theme editor panel: the token schema with defaults and current
 * overrides, and the save endpoint that validates and persists an override set.
 */
#[Route('/theme')]
class ThemeController extends UserAwareController
{
    use JsonHelperTrait;

    public const PERMISSION = 'combat_ui_theme';

    public function __construct(
        private readonly TokenSchemaProvider $schema,
        private readonly TokenOverrideValidator $validator,
        private readonly ThemeSettings $settings,
    ) {
    }

    #[Route('/data', name: 'combat_ui_admin_theme_data', methods: ['GET'])]
    public function dataAction(): JsonResponse
    {
        $this->checkPermission(self::PERMISSION);

        return $this->jsonResponse([
            'groups' => $this->schema->getGroups(),
            'overrides' => (object) $this->settings->getOverrides(),
        ]);
    }

    #[Route('/preview', name: 'combat_ui_admin_theme_preview', methods: ['GET'])]
    public function previewAction(): Response
    {
        $this->checkPermission(self::PERMISSION);

        // Sample page shown in the theme editor's preview iframe. The editor injects a <style>
        // element with the current (unsaved) field values, so the saved override stylesheet is
        // deliberately not linked here.
        $response = $this->render('@CombatUIOpenDxp/admin/theme-preview.html.twig');
        $response->setPrivate();
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        return $response;
    }

    #[Route('/save', name: 'combat_ui_admin_theme_save', methods: ['PUT', 'POST'])]
    public function saveAction(Request $request): JsonResponse
    {
        $this->checkPermission(self::PERMISSION);

        $payload = $this->decodeJson($request->getContent());
        $tokens = is_array($payload) && is_array($payload['tokens'] ?? null) ? $payload['tokens'] : [];

        try {
            $normalized = $this->validator->normalize($tokens);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->settings->saveOverrides($normalized);

        // Pages cached by the full-page output cache still link the previous stylesheet hash.
        Cache::clearTag('output');

        return $this->jsonResponse(['success' => true, 'overrideCount' => count($normalized)]);
    }
}
