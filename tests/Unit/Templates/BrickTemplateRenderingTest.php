<?php

declare(strict_types=1);

namespace CombatUI\CombatUIOpenDxpBundle\Tests\Unit\Templates;

use Codeception\Test\Unit;
use CombatUI\CombatUIOpenDxpBundle\Tests\Support\Twig\BrickTwigEnvironment;

/**
 * Renders every brick view template through the real Combat UI core templates ({% cui %} tags and
 * all), with the opendxp_* editables stubbed. Frontend tests assert the published markup; editmode
 * tests assert the editing UI survives unescaped.
 */
final class BrickTemplateRenderingTest extends Unit
{
    public function testHeroFrontend(): void
    {
        $html = BrickTwigEnvironment::render('cui-hero', false, [
            'variant' => 'overlay',
            'width' => 'wide',
            'eyebrow' => 'Campaign',
            'title' => 'Launch day',
            'lead' => 'The lead paragraph.',
            'background' => '/media/bg.jpg',
            'content' => '<p>Body copy</p>',
            'link_primary' => ['href' => '/contact', 'text' => 'Contact us'],
        ]);

        $this->assertStringContainsString('class="cui-hero"', $html);
        $this->assertStringContainsString('data-variant="overlay"', $html);
        $this->assertStringContainsString('data-width="wide"', $html);
        $this->assertStringContainsString('<p class="cui-eyebrow">Campaign</p>', $html);
        $this->assertStringContainsString('<h1 class="cui-display">Launch day</h1>', $html);
        $this->assertStringContainsString('The lead paragraph.', $html);
        $this->assertStringContainsString('--cui-hero-background-image', $html);
        $this->assertStringContainsString('/media/bg.jpg', $html);
        $this->assertStringContainsString('<p>Body copy</p>', $html);
        $this->assertStringContainsString('<a class="cui-button" data-variant="primary" href="/contact">Contact us</a>', $html);
        $this->assertStringNotContainsString('cui-hero-media', $html, 'no media slot when the media image is empty');
    }

    public function testHeroEscapesScalarProps(): void
    {
        $html = BrickTwigEnvironment::render('cui-hero', false, [
            'title' => '<b>Bold</b>',
        ]);

        $this->assertStringContainsString('&lt;b&gt;Bold&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>Bold</b>', $html);
    }

    public function testHeroEditmodeRendersEditablesUnescaped(): void
    {
        $html = BrickTwigEnvironment::render('cui-hero', true);

        $this->assertStringContainsString('<x-editable data-type="wysiwyg" data-name="content">', $html);
        $this->assertStringContainsString('<x-editable data-type="link" data-name="link_primary">', $html);
        $this->assertStringContainsString('<x-editable data-type="link" data-name="link_secondary">', $html);
        $this->assertStringContainsString('<x-editable data-type="image" data-name="media">', $html);
        $this->assertStringNotContainsString('&lt;x-editable', $html, 'editmode UI must not be escaped');
        $this->assertStringContainsString('cui-hero-media', $html, 'editmode always offers the media drop area');
    }

    public function testPageIntroFrontendSplitsMetaLines(): void
    {
        $html = BrickTwigEnvironment::render('cui-page-intro', false, [
            'variant' => 'case',
            'title' => 'About us',
            'meta' => "By Jane\nMay 2026\n\n",
        ]);

        $this->assertStringContainsString('class="cui-page-intro"', $html);
        $this->assertStringContainsString('data-variant="case"', $html);
        $this->assertStringContainsString('<h1 class="cui-display">About us</h1>', $html);
        $this->assertStringContainsString('<ul class="cui-meta">', $html);
        $this->assertStringContainsString('<li>By Jane</li>', $html);
        $this->assertStringContainsString('<li>May 2026</li>', $html);
    }

    public function testCallToActionFrontendWrapsInContainedSection(): void
    {
        $html = BrickTwigEnvironment::render('cui-cta', false, [
            'variant' => 'split',
            'spacing' => 'compact',
            'title' => 'Ready?',
            'lead' => 'Get in touch today.',
            'link_primary' => ['href' => '/contact', 'text' => 'Contact', 'target' => '_blank'],
        ]);

        $this->assertStringContainsString('class="cui-section"', $html);
        $this->assertStringContainsString('data-spacing="compact"', $html);
        $this->assertStringContainsString('class="cui-container"', $html);
        $this->assertStringContainsString('data-variant="split"', $html);
        $this->assertStringContainsString('<h2 class="cui-cta-title">Ready?</h2>', $html);
        $this->assertStringContainsString('href="/contact" target="_blank"', $html);
    }

    public function testRichTextFrontend(): void
    {
        $html = BrickTwigEnvironment::render('cui-content', false, [
            'tone' => 'muted',
            'spacing' => 'spacious',
            'container' => 'narrow',
            'content' => '<h2>Hello</h2><p>World</p>',
        ]);

        $this->assertStringContainsString('cui-section-muted', $html);
        $this->assertStringContainsString('data-spacing="spacious"', $html);
        $this->assertStringContainsString('class="cui-container-narrow"', $html);
        $this->assertStringContainsString('<div class="cui-prose">', $html);
        $this->assertStringContainsString('<h2>Hello</h2><p>World</p>', $html);
    }

    public function testRichTextFullWidthDropsContainer(): void
    {
        $html = BrickTwigEnvironment::render('cui-content', false, [
            'container' => 'none',
            'content' => '<p>Wide</p>',
        ]);

        $this->assertStringNotContainsString('cui-container', $html);
    }

    public function testCardGridFrontend(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-card-grid',
            false,
            [
                'columns' => '3',
                'gap' => 'spacious',
                'category' => 'News',
                'title' => 'Card title',
                'text' => 'The excerpt.',
                'image' => '/media/card.jpg',
                'link' => ['href' => '/read', 'text' => 'Read more'],
            ],
            ['cards' => 2],
        );

        $this->assertStringContainsString('class="cui-grid"', $html);
        $this->assertStringContainsString('data-columns="3"', $html);
        $this->assertStringContainsString('data-gap="spacious"', $html);
        $this->assertStringContainsString('--cui-grid-min: 18rem', $html);
        $this->assertSame(2, substr_count($html, '<article'), 'one card per block item');
        $this->assertStringContainsString('data-category="news"', $html);
        $this->assertStringContainsString('<img src="/media/card.jpg" alt="">', $html);
        $this->assertStringContainsString('<a href="/read">Card title</a>', $html);
        $this->assertStringContainsString('Read more', $html);
    }

    public function testAccordionFrontendOpensFirstItemOnly(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-accordion',
            false,
            [
                'open_first' => true,
                'summary' => 'The question',
                'body' => '<p>The answer</p>',
            ],
            ['items' => 2],
        );

        $this->assertSame(2, substr_count($html, '<details'), 'one disclosure per block item');
        $this->assertSame(2, substr_count($html, 'The question'));
        $this->assertSame(2, substr_count($html, '<p>The answer</p>'));
        $this->assertSame(1, substr_count($html, '<cui-disclosure open>'), 'only the first item starts open');
    }

    public function testTabsFrontendBuildsTablistFromLabels(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-tabs',
            false,
            [
                'label' => 'My tab',
                'content' => '<p>Panel body</p>',
            ],
            ['tabs' => 2],
        );

        $this->assertStringContainsString('<cui-tabs>', $html);
        $this->assertSame(2, substr_count($html, 'class="cui-tab"'), 'one button per block item');
        $this->assertSame(1, substr_count($html, 'data-selected="true"'), 'first tab preselected');
        $this->assertSame(2, substr_count($html, '<p>Panel body</p>'));
    }

    public function testCarouselFrontendMapsBehaviourAttributes(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-carousel',
            false,
            [
                'autoplay' => true,
                'interval' => 3000,
                'transition' => 'fade',
                'no_loop' => true,
                'hide_pagination' => true,
                'image' => '/media/slide.jpg',
                'caption' => 'A caption',
            ],
            ['slides' => 2],
        );

        $this->assertStringContainsString('<cui-carousel', $html);
        $this->assertStringContainsString(' autoplay', $html);
        $this->assertStringContainsString('interval="3000"', $html);
        $this->assertStringContainsString('transition="fade"', $html);
        $this->assertStringContainsString('loop="false"', $html);
        $this->assertStringContainsString('pagination="false"', $html);
        $this->assertStringNotContainsString('controls=', $html, 'arrows stay on unless hidden');
        $this->assertSame(2, substr_count($html, 'slot="slide"'));
        $this->assertStringContainsString('<figcaption>A caption</figcaption>', $html);
    }

    public function testMediaFrontend(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'width' => 'narrow',
            'image' => '/media/photo.jpg',
            'caption' => 'Shot on location',
        ]);

        $this->assertStringContainsString('class="cui-container-narrow"', $html);
        $this->assertStringContainsString('<img src="/media/photo.jpg" alt="">', $html);
        $this->assertStringContainsString('<figcaption>Shot on location</figcaption>', $html);
    }

    public function testMediaFrontendWithoutCaptionOmitsFigcaption(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'image' => '/media/photo.jpg',
        ]);

        $this->assertStringNotContainsString('<figcaption>', $html);
    }

    public function testMapFrontendRendersPoints(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-map',
            false,
            [
                'center' => '50.85,4.35',
                'zoom' => 8,
                'cluster' => true,
                'label' => 'HQ',
                'lat' => '52.37',
                'lng' => '4.89',
            ],
            ['points' => 1],
        );

        $this->assertStringContainsString('<cui-map', $html);
        $this->assertStringContainsString('center="50.85,4.35"', $html);
        $this->assertStringContainsString('zoom="8"', $html);
        $this->assertStringContainsString(' cluster', $html);
        $this->assertStringContainsString('class="cui-map-point"', $html);
        $this->assertStringContainsString('data-lat="52.37"', $html);
        $this->assertStringContainsString('data-lng="4.89"', $html);
        $this->assertStringContainsString('HQ', $html);
    }

    public function testMapFrontendFallsBackToDefaultCenterAndZoom(): void
    {
        $html = BrickTwigEnvironment::render('cui-map', false);

        $this->assertStringContainsString('center="52.37,4.89"', $html);
        $this->assertStringContainsString('zoom="11"', $html);
    }

    public function testEditmodeStacksInteractiveBricks(): void
    {
        $tabs = BrickTwigEnvironment::render('cui-tabs', true, [], ['tabs' => 2]);
        $this->assertStringNotContainsString('<cui-tabs', $tabs, 'tabs stay flat in editmode so panels remain editable');
        $this->assertStringContainsString('<x-editable data-type="wysiwyg" data-name="content">', $tabs);

        $carousel = BrickTwigEnvironment::render('cui-carousel', true, [], ['slides' => 1]);
        $this->assertStringNotContainsString('<cui-carousel', $carousel, 'slides stay flat in editmode');
        $this->assertStringContainsString('<x-editable data-type="image" data-name="image">', $carousel);

        $map = BrickTwigEnvironment::render('cui-map', true, [], ['points' => 1]);
        $this->assertStringNotContainsString('<cui-map', $map, 'the Leaflet map is not booted in editmode');
        $this->assertStringContainsString('<x-editable data-type="input" data-name="lat">', $map);
    }
}
