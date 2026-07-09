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

#[AsAreabrick(id: 'cui-media')]
class Media extends AbstractCuiAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Media';
    }

    public function getDescription(): string
    {
        return 'Media block: figure with caption, full-width media, media card or overlay card.';
    }

    public function getEditableDialogBoxConfiguration(Editable $area, ?Info $info): EditableDialogBoxConfiguration
    {
        return $this->dialog([
            $this->tabs([
                'Media' => [
                    $this->selectField('style', 'Style', [
                        ['', 'Figure with caption'],
                        ['full', 'Full-width media'],
                        ['banner', 'Banner (linked image)'],
                        ['card', 'Media card'],
                        ['overlay', 'Overlay card'],
                    ]),
                    $this->selectField('ratio', 'Aspect ratio', [
                        ['', 'Default (16:9)'],
                        ['square', 'Square'],
                        ['portrait', 'Portrait'],
                        ['wide', 'Wide'],
                        ['auto', 'Intrinsic (auto)'],
                    ]),
                    $this->selectField('media_type', 'Media type', [
                        ['', 'Image'],
                        ['video', 'Video'],
                    ], 'Choose whether this block shows an image or a video. The chosen media is edited inline on the page.'),
                ],
                'Layout' => [
                    $this->selectField('width', 'Width', [
                        ['', 'Default container'],
                        ['narrow', 'Narrow container'],
                        ['wide', 'Wide container'],
                        ['none', 'Full width'],
                    ]),
                    $this->selectField('align', 'Alignment', [
                        ['', 'Default'],
                        ['center', 'Center (figure and overlay styles)'],
                        ['end', 'End (overlay style)'],
                    ]),
                    $this->checkboxField('orient_row', 'Media beside body (card style)'),
                    $this->checkboxField('reverse', 'Reverse order: content before media (card style)'),
                    $this->selectField('card_style', 'Card chrome (card style)', [
                        ['', 'Default'],
                        ['flat', 'Flat (no shadow)'],
                        ['borderless', 'Borderless'],
                    ]),
                    $this->selectField('scrim', 'Scrim (overlay style)', [
                        ['', 'Bottom gradient'],
                        ['solid', 'Solid'],
                        ['top', 'Top gradient'],
                        ['none', 'None'],
                    ]),
                    $this->checkboxField('no_radius', 'Remove rounded corners (full-width style)'),
                ],
            ]),
        ], 560);
    }
}
