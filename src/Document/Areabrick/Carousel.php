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

#[AsAreabrick(id: 'cui-carousel')]
class Carousel extends AbstractCuiAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Carousel';
    }

    public function getDescription(): string
    {
        return 'Image carousel with optional captions, autoplay and slide/fade transitions.';
    }

    public function getEditableDialogBoxConfiguration(Editable $area, ?Info $info): EditableDialogBoxConfiguration
    {
        return $this->dialog([
            $this->tabs([
                'Behaviour' => [
                    $this->checkboxField('autoplay', 'Autoplay'),
                    $this->numericField('interval', 'Autoplay interval (ms)', 'Defaults to 5000.'),
                    $this->selectField('transition', 'Transition', [
                        ['', 'Slide'],
                        ['fade', 'Fade'],
                        ['none', 'None'],
                    ]),
                    $this->checkboxField('no_loop', 'Stop at the last slide (disable looping)'),
                    $this->checkboxField('hide_controls', 'Hide previous/next arrows'),
                    $this->checkboxField('hide_pagination', 'Hide dot pagination'),
                ],
                'Layout' => [
                    $this->selectField('width', 'Width', [
                        ['', 'Default container'],
                        ['wide', 'Wide container'],
                        ['none', 'Full width'],
                    ]),
                ],
            ]),
        ], 460);
    }
}
