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
use OpenDxp\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use OpenDxp\Extension\Document\Areabrick\EditableDialogBoxInterface;
use OpenDxp\Model\Document\Editable;
use OpenDxp\Model\Document\Editable\Area\Info;

#[AsAreabrick(id: 'cui-map')]
class Map extends AbstractCuiAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Map';
    }

    public function getDescription(): string
    {
        return 'Interactive map with markers, clustering and theme-aware tiles.';
    }

    public function getEditableDialogBoxConfiguration(Editable $area, ?Info $info): EditableDialogBoxConfiguration
    {
        return $this->dialog([
            $this->inputField('center', 'Center', 'Initial center as "lat,lng", e.g. 52.37,4.89.'),
            $this->numericField('zoom', 'Zoom level', 'Defaults to 11.'),
            $this->checkboxField('cluster', 'Cluster nearby markers'),
            $this->checkboxField('fit_bounds', 'Auto-zoom to fit all markers'),
            $this->checkboxField('scroll_wheel_zoom', 'Enable scroll-wheel zoom'),
        ], 400);
    }
}
