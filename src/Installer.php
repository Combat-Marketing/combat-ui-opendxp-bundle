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

namespace CombatUI\CombatUIOpenDxpBundle;

use CombatUI\CombatUIOpenDxpBundle\Controller\Admin\ThemeController;
use OpenDxp\Db;
use OpenDxp\Extension\Bundle\Installer\SettingsStoreAwareInstaller;

/**
 * Registers the theme-editor user permission; the settings-store flag from the parent class
 * tracks the installed state so the bundle can be enabled and disabled from the bundle manager.
 */
class Installer extends SettingsStoreAwareInstaller
{
    protected const USER_PERMISSIONS_CATEGORY = 'Combat UI';

    protected const USER_PERMISSIONS = [
        ThemeController::PERMISSION,
    ];

    public function install(): void
    {
        $this->addUserPermissions();
        parent::install();
    }

    public function uninstall(): void
    {
        $this->removeUserPermissions();
        parent::uninstall();
    }

    private function addUserPermissions(): void
    {
        $db = Db::get();

        foreach (static::USER_PERMISSIONS as $permission) {
            $exists = $db->fetchOne(
                'SELECT `key` FROM users_permission_definitions WHERE `key` = ?',
                [$permission],
            );
            if ($exists) {
                continue;
            }

            $db->insert('users_permission_definitions', [
                $db->quoteIdentifier('key') => $permission,
                $db->quoteIdentifier('category') => static::USER_PERMISSIONS_CATEGORY,
            ]);
        }
    }

    private function removeUserPermissions(): void
    {
        $db = Db::get();

        foreach (static::USER_PERMISSIONS as $permission) {
            $db->delete('users_permission_definitions', [
                $db->quoteIdentifier('key') => $permission,
            ]);
        }
    }
}
