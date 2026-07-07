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

namespace CombatUI\CombatUIOpenDxpBundle\Document\Areabrick;

use OpenDxp\Extension\Document\Areabrick\Attribute\AsAreabrick;

#[AsAreabrick(id: 'cui-tabs')]
class Tabs extends AbstractCuiAreabrick
{
    public function getName(): string
    {
        return 'Tabs';
    }

    public function getDescription(): string
    {
        return 'Tabbed panels, each with a label and rich-text content.';
    }
}
