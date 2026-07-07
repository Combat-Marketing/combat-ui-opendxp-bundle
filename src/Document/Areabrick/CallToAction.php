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

#[AsAreabrick(id: 'cui-cta')]
class CallToAction extends AbstractCuiAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Call to action';
    }

    public function getDescription(): string
    {
        return 'Call-to-action block with title, lead and action buttons.';
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
                        ['', 'Simple'],
                        ['split', 'Split'],
                        ['full-bleed', 'Full bleed'],
                        ['sticky', 'Sticky'],
                    ]),
                    $this->selectField('title_tag', 'Title tag', [
                        ['h2', 'H2'],
                        ['h3', 'H3'],
                    ]),
                    $this->selectField('tone', 'Tone', [
                        ['', 'Default'],
                        ['inverse', 'Inverse'],
                    ]),
                    $this->selectField('spacing', 'Section spacing', [
                        ['', 'Default'],
                        ['compact', 'Compact'],
                        ['spacious', 'Spacious'],
                    ]),
                ],
            ]),
        ], 520);
    }
}
