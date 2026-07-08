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

use OpenDxp\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use OpenDxp\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use OpenDxp\Model\Document\Editable\Checkbox;
use OpenDxp\Model\Document\Editable\Input;
use OpenDxp\Model\Document\Editable\Numeric;
use OpenDxp\Model\Document\Editable\Select;
use OpenDxp\Model\Document\Editable\Textarea;

/**
 * Base class for all Combat UI bricks: templates live in this bundle (templates/areas/<brick-id>/view.html.twig)
 * and the dialog-box helpers below keep the per-brick configuration declarative.
 */
abstract class AbstractCuiAreabrick extends AbstractTemplateAreabrick
{
    public function getTemplateLocation(): string
    {
        return static::TEMPLATE_LOCATION_BUNDLE;
    }

    /**
     * @param array<int, mixed> $items
     */
    protected function dialog(array $items, int $height = 500, int $width = 620): EditableDialogBoxConfiguration
    {
        return (new EditableDialogBoxConfiguration())
            ->setWidth($width)
            ->setHeight($height)
            ->setReloadOnClose(true)
            ->setItems($items);
    }

    /**
     * @param array<string, array<int, mixed>> $panels label => dialog items
     *
     * @return array<string, mixed>
     */
    protected function tabs(array $panels): array
    {
        $items = [];
        foreach ($panels as $title => $panelItems) {
            $items[] = [
                'type' => 'panel',
                'title' => $title,
                'items' => $panelItems,
            ];
        }

        return [
            'type' => 'tabpanel',
            'items' => $items,
        ];
    }

    /**
     * @param array<int, array{0: string, 1: string}> $store value/label pairs
     */
    protected function selectField(string $name, string $label, array $store, ?string $description = null): Select
    {
        $select = (new Select())
            ->setName($name)
            ->setLabel($label)
            ->setDialogDescription($description);
        $select->setConfig(['store' => $store]);

        return $select;
    }

    protected function inputField(string $name, string $label, ?string $description = null): Input
    {
        return (new Input())
            ->setName($name)
            ->setLabel($label)
            ->setDialogDescription($description);
    }

    protected function textareaField(string $name, string $label, ?string $description = null): Textarea
    {
        return (new Textarea())
            ->setName($name)
            ->setLabel($label)
            ->setDialogDescription($description);
    }

    protected function checkboxField(string $name, string $label, ?string $description = null): Checkbox
    {
        return (new Checkbox())
            ->setName($name)
            ->setLabel($label)
            ->setDialogDescription($description);
    }

    protected function numericField(string $name, string $label, ?string $description = null): Numeric
    {
        return (new Numeric())
            ->setName($name)
            ->setLabel($label)
            ->setDialogDescription($description);
    }

    /**
     * The section-band selects shared by every full-width content brick.
     *
     * @return array<int, Select>
     */
    protected function sectionFields(): array
    {
        return [
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
        ];
    }
}
