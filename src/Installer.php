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

use OpenDxp\Extension\Bundle\Installer\SettingsStoreAwareInstaller;

/**
 * The bundle has no database schema or permissions to set up; the settings-store flag alone tracks the
 * installed state so the bundle can be enabled and disabled from the OpenDXP bundle manager.
 */
class Installer extends SettingsStoreAwareInstaller
{
}
