<?php

declare(strict_types=1);

namespace CombatUI\CombatUIOpenDxpBundle\Tests\Support\Twig;

use CombatUI\Bundle\CoreBundle\Twig\ComponentRenderer;
use CombatUI\Bundle\CoreBundle\Twig\Extension\CombatUIExtension;
use Composer\InstalledVersions;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\TwigFunction;

/**
 * Builds a bare Twig environment that mirrors what the bricks see at runtime: the real Combat UI
 * core extension ({% cui %}, cui_attrs, …) and core templates, with the opendxp_* editable
 * functions replaced by FakeEditable stubs fed from a per-render data map.
 */
final class BrickTwigEnvironment
{
    private const EDITABLE_TYPES = ['input', 'textarea', 'wysiwyg', 'select', 'checkbox', 'numeric', 'image', 'link', 'video'];

    /**
     * @param array<string, mixed> $data   editable name => data
     * @param array<string, int>   $blocks block name => item count
     */
    public static function render(string $brickId, bool $editmode, array $data = [], array $blocks = []): string
    {
        return self::create($editmode, $data, $blocks)
            ->render(sprintf('@CombatUIOpenDxp/areas/%s/view.html.twig', $brickId), ['editmode' => $editmode]);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, int>   $blocks
     */
    public static function create(bool $editmode, array $data = [], array $blocks = []): Environment
    {
        $coreBundlePath = InstalledVersions::getInstallPath('combat-ui/core-bundle');

        // Prefer a sibling checkout of the core bundle when it is ahead of the released
        // package (identified by a template the release does not ship yet).
        $sibling = \dirname(__DIR__, 4) . '/combat-ui-bundle';
        if (is_file($sibling . '/templates/components/article-card.html.twig')) {
            $coreBundlePath = $sibling;
        }

        $loader = new FilesystemLoader();
        $loader->addPath($coreBundlePath . '/templates', 'CombatUICore');
        $loader->addPath(\dirname(__DIR__, 3) . '/templates', 'CombatUIOpenDxp');

        $twig = new Environment($loader);
        $twig->addExtension(new CombatUIExtension());
        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            ComponentRenderer::class => static fn (): ComponentRenderer => new ComponentRenderer(),
        ]));

        foreach (self::EDITABLE_TYPES as $type) {
            $twig->addFunction(new TwigFunction(
                'opendxp_' . $type,
                static fn (string $name, array $options = []): FakeEditable => new FakeEditable($type, $name, $data[$name] ?? null, $editmode),
                ['is_safe' => ['html']],
            ));
        }

        $twig->addFunction(new TwigFunction(
            'opendxp_block',
            static fn (string $name, array $options = []): string => $name,
        ));
        $twig->addFunction(new TwigFunction(
            'opendxp_iterate_block',
            static fn (string $name): array => ($blocks[$name] ?? 0) > 0 ? range(1, $blocks[$name]) : [],
        ));

        return $twig;
    }
}
