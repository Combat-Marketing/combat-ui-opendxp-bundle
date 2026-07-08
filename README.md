# Combat UI — OpenDXP Bundle

OpenDXP wrapper bundle for [Combat UI](https://www.combat.nl). It ships a curated set of configurable
**areabricks** built on top of the `combat-ui/core-bundle` Symfony bundle, so editors can compose pages
from Combat UI sections and components directly in the OpenDXP document editor.

## Requirements

- PHP 8.4 or 8.5
- OpenDXP ^1.0
- `combat-ui/core-bundle` (installed automatically as a dependency)

## Installation

```bash
composer require combat-ui/core-opendxp-bundle
```

The bundle is discovered through its composer type (`opendxp-bundle`) and registers its dependencies
(`CombatUICoreBundle`, `WebpackEncoreBundle`) itself. Enable and install it from the OpenDXP bundle
manager, or via CLI:

```bash
bin/console opendxp:bundle:enable CombatUIOpenDxpBundle
bin/console opendxp:bundle:install CombatUIOpenDxpBundle
bin/console assets:install
```

`assets:install` publishes the prebuilt Combat UI Encore build to `public/bundles/combatuicore/build`;
the core bundle wires the `combat_ui` Encore build there automatically.

## Layout integration

Load the Combat UI assets once in your document layout and expose an areablock for the bricks:

```twig
<!DOCTYPE html>
<html lang="{{ document.getProperty('language') }}">
    <head>
        {{ cui_assets() }}
    </head>
    <body>
        <main>
            {{ opendxp_areablock('content', {
                allowed: [
                    'cui-hero', 'cui-page-intro', 'cui-cta', 'cui-content', 'cui-card-grid',
                    'cui-accordion', 'cui-tabs', 'cui-carousel', 'cui-media', 'cui-map',
                ]
            }) }}
        </main>
    </body>
</html>
```

The bricks are full-width bands that bring their own `.cui-container` wrappers, so place the areablock
in a full-bleed region of the layout rather than inside another container.

## Bricks

All bricks live in the `Combat UI` namespace (`src/Document/Areabrick`) with templates under
`templates/areas/<brick-id>/view.html.twig`. Structural options (variant, tone, spacing, width, …)
are configured through the brick dialog (pencil icon); content is edited inline where possible.

| Brick | ID | Dialog options | Inline editables |
| --- | --- | --- | --- |
| Hero | `cui-hero` | variant (split/background/overlay/text), title tag, alignment, width, media position, eyebrow, title, lead, background image | WYSIWYG body, media image, primary/secondary links |
| Page intro | `cui-page-intro` | variant (landing/case/vacancy/campaign/full-bleed), tone, alignment, title tag, eyebrow, title, lead, meta lines | WYSIWYG body, primary/secondary links |
| Call to action | `cui-cta` | variant (simple/split/full-bleed/sticky), tone, title tag, section spacing, eyebrow, title, lead | WYSIWYG body, primary/secondary links |
| Rich text | `cui-content` | section spacing, tone, container width | WYSIWYG prose |
| Card grid | `cui-card-grid` | columns, gap, card style (featured/compact/flat/borderless), section spacing, tone, container width | per card: image, category, title, excerpt, link |
| Accordion | `cui-accordion` | variant (ghost), open first item | per item: summary, WYSIWYG body |
| Tabs | `cui-tabs` | — | per tab: label, WYSIWYG content |
| Carousel | `cui-carousel` | autoplay, interval, transition, looping, arrows/pagination, width | per slide: image, caption |
| Media | `cui-media` | style (figure / full-width / banner / media card / overlay card), aspect ratio, optional video, media link (banner: whole image, card: media region), eyebrow, title, container width, alignment, row orientation, reversed order (content before media), card chrome (flat/borderless), scrim, corner radius | image; caption (figure); WYSIWYG body + links (card/overlay) |
| Map | `cui-map` | center, zoom, clustering, fit bounds, scroll-wheel zoom | per marker: label, latitude, longitude |

### Notes

- Brick auto-registration relies on OpenDXP's default `documents.areas.autoload: true`; if you disabled
  it, tag the brick classes with `opendxp.area.brick` yourself.
- Image editables render the original asset by default. Override a brick template in your app
  (`templates/bundles/CombatUIOpenDxpBundle/areas/<brick-id>/view.html.twig`) to apply thumbnail
  configurations.
- Brand-wide component defaults (e.g. default button variant) can be set via the core bundle's
  `combat_ui_core.component_defaults` configuration.

## Development

Run the Codeception unit suite:

```bash
composer install
composer test          # codecept run Unit
```

The suite covers the bundle contract (nice name, dependent bundles, container extension), every
brick's `AsAreabrick` id, template location and dialog-box editables, and renders each brick view
template through the real Combat UI core templates — with the `opendxp_*` editables stubbed — in
both frontend and editmode.

## License

GPL-3.0-or-later — see [LICENSE](LICENSE).
