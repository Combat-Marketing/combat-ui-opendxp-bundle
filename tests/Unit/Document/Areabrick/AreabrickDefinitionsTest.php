<?php

declare(strict_types=1);

namespace CombatUI\CombatUIOpenDxpBundle\Tests\Unit\Document\Areabrick;

use Codeception\Stub;
use Codeception\Test\Unit;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\AbstractCuiAreabrick;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\Accordion;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\CallToAction;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\CardGrid;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\Carousel;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\Hero;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\Map;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\Media;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\PageIntro;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\RichText;
use CombatUI\CombatUIOpenDxpBundle\Document\Areabrick\Tabs;
use OpenDxp\Extension\Document\Areabrick\Attribute\AsAreabrick;
use OpenDxp\Extension\Document\Areabrick\EditableDialogBoxInterface;
use OpenDxp\Extension\Document\Areabrick\TemplateAreabrickInterface;
use OpenDxp\Model\Document\Editable;

final class AreabrickDefinitionsTest extends Unit
{
    /**
     * @return array<string, array{class-string<AbstractCuiAreabrick>, string, list<string>}>
     */
    public static function brickProvider(): array
    {
        return [
            'hero' => [Hero::class, 'cui-hero', ['eyebrow', 'title', 'lead', 'variant', 'title_tag', 'align', 'width', 'media_position', 'background']],
            'page intro' => [PageIntro::class, 'cui-page-intro', ['eyebrow', 'title', 'lead', 'meta', 'variant', 'title_tag', 'tone', 'align']],
            'call to action' => [CallToAction::class, 'cui-cta', ['eyebrow', 'title', 'lead', 'variant', 'title_tag', 'tone', 'spacing']],
            'rich text' => [RichText::class, 'cui-content', ['spacing', 'tone', 'container']],
            'card grid' => [CardGrid::class, 'cui-card-grid', ['columns', 'gap', 'card_variant', 'spacing', 'tone', 'container']],
            'accordion' => [Accordion::class, 'cui-accordion', ['variant', 'open_first']],
            'tabs' => [Tabs::class, 'cui-tabs', []],
            'carousel' => [Carousel::class, 'cui-carousel', ['autoplay', 'interval', 'transition', 'no_loop', 'hide_controls', 'hide_pagination', 'width']],
            'image' => [Media::class, 'cui-media', ['width']],
            'map' => [Map::class, 'cui-map', ['center', 'zoom', 'cluster', 'fit_bounds', 'scroll_wheel_zoom']],
        ];
    }

    /**
     * @dataProvider brickProvider
     *
     * @param class-string<AbstractCuiAreabrick> $class
     * @param list<string>                       $dialogEditables
     */
    public function testBrickDefinition(string $class, string $expectedId, array $dialogEditables): void
    {
        $brick = new $class();

        $attributes = (new \ReflectionClass($class))->getAttributes(AsAreabrick::class);
        $this->assertCount(1, $attributes, $class . ' must carry the AsAreabrick attribute');
        $this->assertSame($expectedId, $attributes[0]->newInstance()->id);

        $this->assertNotSame('', $brick->getName());
        $this->assertNotSame('', $brick->getDescription());
        $this->assertTrue($brick->hasTemplate());
        $this->assertSame(TemplateAreabrickInterface::TEMPLATE_LOCATION_BUNDLE, $brick->getTemplateLocation());
        $this->assertSame(TemplateAreabrickInterface::TEMPLATE_SUFFIX_TWIG, $brick->getTemplateSuffix());

        $this->assertFileExists(
            \dirname(__DIR__, 4) . sprintf('/templates/areas/%s/view.html.twig', $expectedId),
            'every brick id needs a matching view template in the bundle',
        );

        if ($dialogEditables === []) {
            $this->assertNotInstanceOf(EditableDialogBoxInterface::class, $brick);

            return;
        }

        $this->assertInstanceOf(EditableDialogBoxInterface::class, $brick);

        $config = $brick->getEditableDialogBoxConfiguration(Stub::makeEmpty(Editable::class), null);

        $this->assertTrue($config->getReloadOnClose(), 'dialog changes affect structure, so the brick must re-render on close');
        $this->assertSame($dialogEditables, $this->collectEditableNames($config->getItems()));
    }

    /**
     * @param array<int|string, mixed> $items
     *
     * @return list<string>
     */
    private function collectEditableNames(array $items): array
    {
        $names = [];
        foreach ($items as $item) {
            if ($item instanceof Editable) {
                $names[] = $item->getName();
            } elseif (is_array($item)) {
                $names = [...$names, ...$this->collectEditableNames($item['items'] ?? $item)];
            }
        }

        return $names;
    }
}
