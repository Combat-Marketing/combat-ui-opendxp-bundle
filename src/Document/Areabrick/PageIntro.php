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

#[AsAreabrick(id: 'cui-page-intro')]
class PageIntro extends AbstractCuiAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Page intro';
    }

    public function getDescription(): string
    {
        return 'Page header strip with eyebrow, title, lead, meta line and actions.';
    }

    public function getEditableDialogBoxConfiguration(Editable $area, ?Info $info): EditableDialogBoxConfiguration
    {
        return $this->dialog([
            $this->tabs([
                'Content' => [
                    $this->inputField('eyebrow', 'Eyebrow'),
                    $this->inputField('title', 'Title'),
                    $this->textareaField('lead', 'Lead paragraph'),
                    $this->textareaField('meta', 'Meta entries', 'One entry per line (e.g. author, date, reading time).'),
                ],
                'Appearance' => [
                    $this->selectField('variant', 'Variant', [
                        ['', 'Default'],
                        ['landing', 'Landing'],
                        ['case', 'Case'],
                        ['vacancy', 'Vacancy'],
                        ['campaign', 'Campaign'],
                        ['full-bleed', 'Full bleed'],
                    ]),
                    $this->selectField('title_tag', 'Title tag', [
                        ['h1', 'H1'],
                        ['h2', 'H2'],
                    ]),
                    $this->selectField('tone', 'Tone', [
                        ['', 'Default'],
                        ['inverse', 'Inverse'],
                    ]),
                    $this->selectField('align', 'Alignment', [
                        ['', 'Start'],
                        ['center', 'Center'],
                    ]),
                ],
            ]),
        ], 560);
    }
}
