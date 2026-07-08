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

#[AsAreabrick(id: 'cui-logo-strip')]
class LogoStrip extends AbstractCuiAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Logo strip';
    }

    public function getDescription(): string
    {
        return 'Grid of client or partner logos.';
    }

    public function getEditableDialogBoxConfiguration(Editable $area, ?Info $info): EditableDialogBoxConfiguration
    {
        return $this->dialog($this->sectionFields(), 400);
    }
}
