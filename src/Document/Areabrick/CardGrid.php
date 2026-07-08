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

#[AsAreabrick(id: 'cui-card-grid')]
class CardGrid extends AbstractCuiAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Card grid';
    }

    public function getDescription(): string
    {
        return 'Responsive grid of article cards with image, category, title, excerpt and link.';
    }

    public function getEditableDialogBoxConfiguration(Editable $area, ?Info $info): EditableDialogBoxConfiguration
    {
        return $this->dialog([
            $this->tabs([
                'Grid' => [
                    $this->selectField('columns', 'Columns', [
                        ['', 'Auto (fit by card width)'],
                        ['2', '2'],
                        ['3', '3'],
                        ['4', '4'],
                    ]),
                    $this->selectField('gap', 'Gap', [
                        ['', 'Default'],
                        ['compact', 'Compact'],
                        ['spacious', 'Spacious'],
                    ]),
                    $this->selectField('card_variant', 'Card style', [
                        ['', 'Default'],
                        ['featured', 'Featured'],
                        ['compact', 'Compact (no media)'],
                        ['flat', 'Flat'],
                        ['borderless', 'Borderless'],
                    ]),
                    $this->checkboxField('show_filter', 'Show category filter chips', 'Client-side filter built from the card category values.'),
                    $this->inputField('filter_label', 'Filter label', 'Defaults to "Filter".'),
                ],
                'Section' => [
                    $this->selectField('spacing', 'Section spacing', [
                        ['', 'Default'],
                        ['compact', 'Compact'],
                        ['spacious', 'Spacious'],
                    ]),
                    $this->selectField('tone', 'Tone', [
                        ['', 'Default'],
                        ['muted', 'Muted'],
                        ['inverse', 'Inverse'],
                        ['accent', 'Accent'],
                    ]),
                    $this->selectField('container', 'Container width', [
                        ['', 'Default'],
                        ['narrow', 'Narrow'],
                        ['wide', 'Wide'],
                        ['none', 'None (full width)'],
                    ]),
                ],
            ]),
        ], 480);
    }
}
