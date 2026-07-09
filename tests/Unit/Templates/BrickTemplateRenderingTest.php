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

    public function testMediaFigureFrontend(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'width' => 'narrow',
            'ratio' => 'wide',
            'align' => 'center',
            'image' => '/media/photo.jpg',
            'caption' => 'Shot on location',
        ]);

        $this->assertStringContainsString('class="cui-container-narrow"', $html);
        $this->assertStringContainsString('class="cui-figure"', $html);
        $this->assertStringContainsString('data-ratio="wide"', $html);
        $this->assertStringContainsString('data-align="center"', $html);
        $this->assertStringContainsString('<div class="cui-figure-media"><img src="/media/photo.jpg" alt=""></div>', $html);
        $this->assertStringContainsString('<figcaption>Shot on location</figcaption>', $html);
    }

    public function testMediaFigureWithoutCaptionOmitsFigcaption(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'image' => '/media/photo.jpg',
        ]);

        $this->assertStringContainsString('class="cui-figure"', $html);
        $this->assertStringNotContainsString('<figcaption>', $html);
    }

    public function testMediaWithoutAnyMediaRendersNoBlock(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'caption' => 'Orphan caption',
        ]);

        $this->assertStringNotContainsString('cui-figure', $html);
        $this->assertStringNotContainsString('Orphan caption', $html);
    }

    public function testMediaFullBleedFrontendUsesVideoWhenSelected(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'style' => 'full',
            'width' => 'none',
            'no_radius' => true,
            'media_type' => 'video',
            'image' => '/media/poster.jpg',
            'video' => '/media/clip.mp4',
        ]);

        $this->assertStringContainsString('class="cui-media-full"', $html);
        $this->assertStringContainsString('data-bleed="full"', $html);
        $this->assertStringContainsString('data-radius="none"', $html);
        $this->assertStringContainsString('<video src="/media/clip.mp4"></video>', $html);
        $this->assertStringNotContainsString('<img', $html, 'the video media type replaces the image');
        $this->assertStringNotContainsString('cui-container', $html, 'full width drops the container');
    }

    public function testMediaKeepsImageWhenVideoSetButTypeIsImage(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'style' => 'full',
            'image' => '/media/poster.jpg',
            'video' => '/media/clip.mp4',
        ]);

        $this->assertStringContainsString('<img src="/media/poster.jpg" alt="">', $html);
        $this->assertStringNotContainsString('<video', $html, 'a set video is ignored unless the media type is video');
    }

    public function testMediaBannerFrontendIsAMediaCardWithLinkedMedia(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'style' => 'banner',
            'ratio' => 'wide',
            'image' => '/media/promo.jpg',
            'eyebrow' => 'Now on',
            'title' => 'Summer promo',
            'banner_link' => ['href' => '/promo', 'text' => 'Summer promo'],
        ]);

        $this->assertStringContainsString('class="cui-surface cui-media-card"', $html);
        $this->assertStringContainsString('data-ratio="wide"', $html);
        // The link surrounds the media element (native media-card linked media).
        $this->assertStringContainsString(
            '<a class="cui-media-card-media" href="/promo" aria-label="Summer promo"><img src="/media/promo.jpg" alt=""></a>',
            $html,
        );
        // Optional eyebrow and heading render, and the heading also links.
        $this->assertStringContainsString('<p class="cui-eyebrow">Now on</p>', $html);
        $this->assertStringContainsString('<h3><a href="/promo">Summer promo</a></h3>', $html);
    }

    public function testMediaBannerVideoWrapsMediaSlotInLink(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'style' => 'banner',
            'media_type' => 'video',
            'image' => '/media/poster.jpg',
            'video' => '/media/clip.mp4',
            'banner_link' => ['href' => '/promo', 'text' => 'Summer promo', 'target' => '_blank'],
        ]);

        $this->assertStringContainsString('class="cui-surface cui-media-card"', $html);
        $this->assertMatchesRegularExpression(
            '~<div class="cui-media-card-media"><a href="/promo" target="_blank" aria-label="Summer promo"><video src="/media/clip.mp4"></video></a></div>~',
            $html,
        );
    }

    public function testMediaBannerWithoutLinkRendersUnlinkedMedia(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'style' => 'banner',
            'image' => '/media/promo.jpg',
        ]);

        $this->assertStringContainsString('class="cui-surface cui-media-card"', $html);
        $this->assertStringContainsString('<div class="cui-media-card-media"><img src="/media/promo.jpg" alt=""></div>', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }

    public function testMediaCardFrontend(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'style' => 'card',
            'orient_row' => true,
            'card_style' => 'flat',
            'image' => '/media/cover.jpg',
            'eyebrow' => 'Spotlight',
            'title' => 'Card title',
            'body' => '<p>Card copy</p>',
            'link_primary' => ['href' => '/post', 'text' => 'Read more'],
        ]);

        $this->assertStringContainsString('<article class="cui-surface cui-media-card" data-orient="row" data-elevation="flat">', $html);
        $this->assertStringContainsString('<div class="cui-media-card-media"><img src="/media/cover.jpg" alt=""></div>', $html);
        $this->assertStringContainsString('<p class="cui-eyebrow">Spotlight</p>', $html);
        $this->assertStringContainsString('<h3>Card title</h3>', $html);
        $this->assertStringContainsString('<p>Card copy</p>', $html);
        $this->assertStringContainsString('<a class="cui-button" data-variant="primary" href="/post">Read more</a>', $html);
        $this->assertLessThan(
            strpos($html, '<div class="cui-stack">'),
            strpos($html, 'cui-media-card-media'),
            'media leads the card by default',
        );
    }

    public function testMediaCardReverseRendersContentBeforeMedia(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'style' => 'card',
            'reverse' => true,
            'image' => '/media/cover.jpg',
            'title' => 'Card title',
        ]);

        $this->assertStringContainsString('<div class="cui-media-card-media">', $html);
        $this->assertGreaterThan(
            strpos($html, '<div class="cui-stack">'),
            strpos($html, 'cui-media-card-media'),
            'reversed card puts the body before the media',
        );
    }

    public function testMediaCardWithMediaLinkWrapsMediaRegionInAnchor(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'style' => 'card',
            'image' => '/media/cover.jpg',
            'title' => 'Card title',
            'banner_link' => ['href' => '/case-study', 'text' => 'Read the case study'],
        ]);

        $this->assertStringContainsString(
            '<a class="cui-media-card-media" href="/case-study" aria-label="Read the case study"><img src="/media/cover.jpg" alt=""></a>',
            $html,
        );
        $this->assertStringNotContainsString('<div class="cui-media-card-media">', $html);
    }

    public function testMediaCardBorderlessMapsToDataVariant(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'style' => 'card',
            'card_style' => 'borderless',
            'image' => '/media/cover.jpg',
        ]);

        $this->assertStringContainsString('data-variant="borderless"', $html);
        $this->assertStringNotContainsString('data-elevation=', $html);
    }

    public function testMediaOverlayFrontend(): void
    {
        $html = BrickTwigEnvironment::render('cui-media', false, [
            'style' => 'overlay',
            'align' => 'center',
            'scrim' => 'solid',
            'ratio' => 'wide',
            'image' => '/media/campaign.jpg',
            'eyebrow' => 'Campaign',
            'title' => 'Above the fold',
            'body' => '<p>Overlay copy</p>',
        ]);

        $this->assertStringContainsString('class="cui-media-overlay"', $html);
        $this->assertStringContainsString('data-align="center"', $html);
        $this->assertStringContainsString('data-scrim="solid"', $html);
        $this->assertStringContainsString('data-ratio="wide"', $html);
        $this->assertStringContainsString('<div class="cui-media-overlay-media"><img src="/media/campaign.jpg" alt=""></div>', $html);
        $this->assertStringContainsString('<h3 class="cui-display">Above the fold</h3>', $html);
        $this->assertStringContainsString('<p>Overlay copy</p>', $html);
    }

    public function testMediaEditmodeShowsStyleSpecificEditables(): void
    {
        $figure = BrickTwigEnvironment::render('cui-media', true);
        $this->assertStringContainsString('<x-editable data-type="image" data-name="image">', $figure);
        $this->assertStringContainsString('<x-editable data-type="input" data-name="caption">', $figure);
        $this->assertStringNotContainsString('data-name="body"', $figure, 'figure style has no body copy');
        $this->assertStringNotContainsString('<x-editable data-type="video" data-name="video">', $figure, 'the image media type edits the image inline');

        $video = BrickTwigEnvironment::render('cui-media', true, ['media_type' => 'video']);
        $this->assertStringContainsString('<x-editable data-type="video" data-name="video">', $video);
        $this->assertStringNotContainsString('<x-editable data-type="image" data-name="image">', $video, 'the video media type replaces the image editable inline');

        $card = BrickTwigEnvironment::render('cui-media', true, ['style' => 'card']);
        $this->assertStringContainsString('<x-editable data-type="input" data-name="eyebrow">', $card, 'card content is edited inline');
        $this->assertStringContainsString('<x-editable data-type="input" data-name="title">', $card, 'card content is edited inline');
        $this->assertStringContainsString('<x-editable data-type="wysiwyg" data-name="body">', $card);
        $this->assertStringContainsString('<x-editable data-type="link" data-name="link_primary">', $card);
        $this->assertStringContainsString('<x-editable data-type="link" data-name="banner_link">', $card, 'card style offers the media link');
        $this->assertStringNotContainsString('data-name="caption"', $card, 'card style has no figure caption');

        $banner = BrickTwigEnvironment::render('cui-media', true, ['style' => 'banner']);
        $this->assertStringContainsString('<x-editable data-type="link" data-name="banner_link">', $banner);
        $this->assertStringContainsString('<x-editable data-type="input" data-name="eyebrow">', $banner, 'banner offers an optional eyebrow');
        $this->assertStringContainsString('<x-editable data-type="input" data-name="title">', $banner, 'banner offers an optional heading');
        $this->assertStringNotContainsString('data-name="body"', $banner, 'banner style has no body copy');
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

    public function testFeatureGridFrontend(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-feature-grid',
            false,
            ['columns' => '3', 'align' => 'center', 'icon' => '01', 'title' => 'Reusable structure', 'text' => 'Feature copy.'],
            ['features' => 2],
        );

        $this->assertStringContainsString('data-columns="3"', $html);
        $this->assertSame(2, substr_count($html, 'cui-feature-card'));
        $this->assertStringContainsString('<article class="cui-surface cui-feature-card" data-align="center">', $html);
        $this->assertStringContainsString('<span class="cui-feature-icon" aria-hidden="true">01</span>', $html);
        $this->assertStringContainsString('<h3>Reusable structure</h3>', $html);
        $this->assertStringContainsString('<p>Feature copy.</p>', $html);
    }

    public function testStatsFrontend(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-stats',
            false,
            ['align' => 'center', 'value' => '42%', 'label' => 'Higher page reuse'],
            ['stats' => 3],
        );

        $this->assertSame(3, substr_count($html, '<div class="cui-surface cui-stat" data-align="center">'));
        $this->assertStringContainsString('<span class="cui-stat-value">42%</span>', $html);
        $this->assertStringContainsString('<span class="cui-stat-label">Higher page reuse</span>', $html);
    }

    public function testLogoStripFrontend(): void
    {
        $withImage = BrickTwigEnvironment::render('cui-logo-strip', false, ['logo' => '/logo.svg'], ['logos' => 1]);
        $this->assertStringContainsString('<div class="cui-surface cui-logo-item"><img src="/logo.svg" alt=""></div>', $withImage);

        $labelOnly = BrickTwigEnvironment::render('cui-logo-strip', false, ['label' => 'Northstar'], ['logos' => 1]);
        $this->assertStringContainsString('<div class="cui-surface cui-logo-item"><strong>Northstar</strong></div>', $labelOnly);
    }

    public function testTeamFrontend(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-team',
            false,
            [
                'variant' => 'flat',
                'align' => 'center',
                'photo_shape' => 'square',
                'photo' => '/people/jane.jpg',
                'name' => 'Jane Smith',
                'role' => 'Staff engineer',
                'meta' => 'Engineering',
                'email' => 'jane@example.com',
            ],
            ['members' => 2],
        );

        $this->assertStringContainsString('<article class="cui-surface cui-person" data-align="center" data-photo="square" data-variant="flat">', $html);
        $this->assertStringContainsString('<img class="cui-person-photo" src="/people/jane.jpg" alt="">', $html);
        $this->assertStringContainsString('<p class="cui-person-meta">Engineering</p>', $html);
        $this->assertStringContainsString('<h3 class="cui-person-name">Jane Smith</h3>', $html);
        $this->assertStringContainsString('<a href="mailto:jane@example.com">jane@example.com</a>', $html);
    }

    public function testTeamLeadershipDefaults(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-team',
            false,
            ['variant' => 'leadership', 'name' => 'Chief Person', 'bio' => 'Founder story.'],
            ['members' => 1],
        );

        $this->assertStringContainsString('data-columns="2"', $html);
        $this->assertStringContainsString('data-gap="spacious"', $html);
        $this->assertStringContainsString('--cui-grid-min: 20rem', $html);
        $this->assertStringContainsString('data-orient="row" data-variant="leadership"', $html);
        $this->assertStringContainsString('<p class="cui-person-bio">Founder story.</p>', $html);
    }

    public function testEventsFrontend(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-events',
            false,
            [
                'variant' => 'featured',
                'date' => '2026-06-15 10:00',
                'title' => 'CSS workshop',
                'category' => 'Workshop',
                'time' => '10:00–12:00',
                'location' => 'Amsterdam',
                'status' => 'upcoming',
                'excerpt' => 'Two hours of layout.',
                'link' => ['href' => '/events/css', 'text' => 'Register'],
            ],
            ['events' => 1],
        );

        $this->assertStringContainsString('data-status="upcoming" data-variant="featured"', $html);
        $this->assertStringContainsString('<time class="cui-event-card-date" datetime="2026-06-15T10:00">', $html);
        $this->assertStringContainsString('<span class="cui-event-card-date-month">Jun</span>', $html);
        $this->assertStringContainsString('<span class="cui-event-card-date-day">15</span>', $html);
        $this->assertStringContainsString('<span class="cui-event-card-date-year">2026</span>', $html);
        $this->assertStringContainsString('data-cui-event-title><a href="/events/css">CSS workshop</a>', $html);
        $this->assertStringContainsString('<li>Amsterdam</li>', $html);
        $this->assertStringContainsString('<span>Upcoming</span>', $html);
        $this->assertStringContainsString('<a class="cui-button" data-variant="primary" href="/events/css">Register</a>', $html);
    }

    public function testEventsInvalidDateSkipsDateBlock(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-events',
            false,
            ['date' => 'next friday', 'title' => 'Event'],
            ['events' => 1],
        );

        $this->assertStringNotContainsString('cui-event-card-date', $html, 'an invalid date must be skipped, not crash the render');
    }

    public function testContactCardsFrontend(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-contact',
            false,
            [
                'variant' => 'inverse',
                'eyebrow' => 'Support',
                'title' => 'Customer success',
                'text' => 'We reply within one business day.',
                'email' => 'hello@example.com',
                'phone' => '+31 20 123 4567',
                'link' => ['href' => '/contact', 'text' => 'Send a message'],
            ],
            ['cards' => 1],
        );

        $this->assertStringContainsString('<article class="cui-surface cui-stack cui-contact-card" data-variant="inverse">', $html);
        $this->assertStringContainsString('<dd><a href="mailto:hello@example.com">hello@example.com</a></dd>', $html);
        $this->assertStringContainsString('<dd><a href="tel:+31201234567">+31 20 123 4567</a></dd>', $html);
        $this->assertStringContainsString('<p>We reply within one business day.</p>', $html);
        $this->assertStringContainsString('<a class="cui-button" data-variant="primary" href="/contact">Send a message</a>', $html);
    }

    public function testCardGridCategoryFilter(): void
    {
        $html = BrickTwigEnvironment::render(
            'cui-card-grid',
            false,
            ['show_filter' => true, 'category' => 'News', 'title' => 'Card title'],
            ['cards' => 2],
        );

        $this->assertStringContainsString('<cui-article-filter target="#cui-card-grid-0">', $html);
        $this->assertStringContainsString('<p class="cui-article-filter-label">Filter</p>', $html);
        $this->assertStringContainsString('<input type="radio" name="category" value="news">', $html);
        $this->assertStringContainsString('<span>News</span>', $html);
        $this->assertStringContainsString('id="cui-card-grid-0"', $html);
        $this->assertStringContainsString('data-category="news"', $html);
    }

    public function testNewBricksEditmodeEditables(): void
    {
        $features = BrickTwigEnvironment::render('cui-feature-grid', true, [], ['features' => 1]);
        $this->assertStringContainsString('<x-editable data-type="input" data-name="icon">', $features);

        $team = BrickTwigEnvironment::render('cui-team', true, [], ['members' => 1]);
        $this->assertStringContainsString('<x-editable data-type="image" data-name="photo">', $team);
        $this->assertStringContainsString('<x-editable data-type="textarea" data-name="bio">', $team);

        $events = BrickTwigEnvironment::render('cui-events', true, [], ['events' => 1]);
        $this->assertStringContainsString('<x-editable data-type="select" data-name="status">', $events);
        $this->assertStringContainsString('<x-editable data-type="link" data-name="link">', $events);

        $contact = BrickTwigEnvironment::render('cui-contact', true, [], ['cards' => 1]);
        $this->assertStringContainsString('<x-editable data-type="input" data-name="phone">', $contact);
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
