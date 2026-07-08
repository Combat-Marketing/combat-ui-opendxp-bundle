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

#[AsAreabrick(id: 'cui-team')]
class Team extends AbstractCuiAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Team';
    }

    public function getDescription(): string
    {
        return 'Grid of team member cards; the leadership style switches to row cards with bios.';
    }

    public function getEditableDialogBoxConfiguration(Editable $area, ?Info $info): EditableDialogBoxConfiguration
    {
        return $this->dialog([
            $this->tabs([
                'Cards' => [
                    $this->selectField('variant', 'Card style', [
                        ['', 'Default'],
                        ['flat', 'Flat'],
                        ['borderless', 'Borderless'],
                        ['leadership', 'Leadership (row cards with bios)'],
                    ]),
                    $this->selectField('photo_shape', 'Photo shape', [
                        ['', 'Circular'],
                        ['rounded', 'Rounded'],
                        ['square', 'Square'],
                    ]),
                    $this->selectField('align', 'Card alignment', [
                        ['', 'Start'],
                        ['center', 'Center'],
                    ]),
                    $this->selectField('columns', 'Columns', [
                        ['', 'Auto (2 for leadership)'],
                        ['2', '2'],
                        ['3', '3'],
                        ['4', '4'],
                    ]),
                    $this->selectField('gap', 'Gap', [
                        ['', 'Default (spacious for leadership)'],
                        ['compact', 'Compact'],
                        ['spacious', 'Spacious'],
                    ]),
                ],
                'Section' => $this->sectionFields(),
            ]),
        ], 500);
    }
}
