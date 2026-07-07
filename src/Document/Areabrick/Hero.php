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
use OpenDxp\Model\Document\Editable\Image;

#[AsAreabrick(id: 'cui-hero')]
class Hero extends AbstractCuiAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Hero';
    }

    public function getDescription(): string
    {
        return 'Full-width hero band with eyebrow, title, lead, actions and optional media or background image.';
    }

    public function getEditableDialogBoxConfiguration(Editable $area, ?Info $info): EditableDialogBoxConfiguration
    {
        return $this->dialog([
            $this->tabs([
                'Content' => [
                    $this->inputField('eyebrow', 'Eyebrow'),
                    $this->inputField('title', 'Title'),
                    $this->textareaField('lead', 'Lead paragraph'),
                ],
                'Appearance' => [
                    $this->selectField('variant', 'Variant', [
                        ['', 'Auto (split with media, text without)'],
                        ['split', 'Split'],
                        ['background', 'Background'],
                        ['overlay', 'Overlay'],
                        ['text', 'Text'],
                    ]),
                    $this->selectField('title_tag', 'Title tag', [
                        ['h1', 'H1'],
                        ['h2', 'H2'],
                    ]),
                    $this->selectField('align', 'Alignment', [
                        ['', 'Start'],
                        ['center', 'Center'],
                    ]),
                    $this->selectField('width', 'Width', [
                        ['', 'Default'],
                        ['narrow', 'Narrow'],
                        ['wide', 'Wide'],
                    ]),
                    $this->selectField('media_position', 'Media position', [
                        ['', 'End'],
                        ['start', 'Start'],
                    ]),
                ],
                'Background' => [
                    (new Image())
                        ->setName('background')
                        ->setLabel('Background image')
                        ->setDialogDescription('Shown by the "background" and "overlay" variants.'),
                ],
            ]),
        ], 560);
    }
}
