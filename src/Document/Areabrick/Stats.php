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

#[AsAreabrick(id: 'cui-stats')]
class Stats extends AbstractCuiAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Statistics';
    }

    public function getDescription(): string
    {
        return 'Row of stat tiles with a large value and label.';
    }

    public function getEditableDialogBoxConfiguration(Editable $area, ?Info $info): EditableDialogBoxConfiguration
    {
        return $this->dialog([
            $this->tabs([
                'Grid' => [
                    $this->selectField('columns', 'Columns', [
                        ['', 'Auto (fit by tile width)'],
                        ['2', '2'],
                        ['3', '3'],
                        ['4', '4'],
                    ]),
                    $this->selectField('gap', 'Gap', [
                        ['', 'Default'],
                        ['compact', 'Compact'],
                        ['spacious', 'Spacious'],
                    ]),
                    $this->selectField('align', 'Tile alignment', [
                        ['', 'Start'],
                        ['center', 'Center'],
                    ]),
                ],
                'Section' => $this->sectionFields(),
            ]),
        ], 440);
    }
}
