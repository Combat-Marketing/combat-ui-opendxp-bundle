<?php

declare(strict_types=1);

namespace CombatUI\CombatUIOpenDxpBundle\Tests\Unit\Theme;

use Codeception\Test\Unit;
use CombatUI\CombatUIOpenDxpBundle\Theme\TokenSchemaProvider;

final class TokenSchemaProviderTest extends Unit
{
    private TokenSchemaProvider $provider;

    protected function _before(): void
    {
        $this->provider = new TokenSchemaProvider(\dirname(__DIR__, 2) . '/Support/fixtures/blocks.json');
    }

    public function testGroupsKeepSourceOrderWithColorsAtColorBasePosition(): void
    {
        $names = array_column($this->provider->getGroups(), 'name');

        $this->assertSame(['typography', 'colors', 'spacing'], $names);
    }

    public function testColorGroupsAreMergedIntoASingleColorGroup(): void
    {
        $groups = $this->provider->getGroups();
        $colors = $groups[1];

        $this->assertSame('color', $colors['kind']);
        $this->assertSame('Colors', $colors['label']);
        $this->assertSame(
            ['--cui-color', '--cui-color-accent', '--cui-color-inverse'],
            array_column($colors['tokens'], 'name'),
        );
    }

    public function testColorTokensCarryLightAndDarkDefaults(): void
    {
        $tokens = [];
        foreach ($this->provider->getGroups()[1]['tokens'] as $token) {
            $tokens[$token['name']] = $token;
        }

        // Light default from color-base, dark default from color-dark.
        $this->assertSame('oklch(24% 0.04 264deg)', $tokens['--cui-color']['light']);
        $this->assertSame('oklch(97.6% 0.009 259deg)', $tokens['--cui-color']['dark']);

        // A base-only token has no dark default.
        $this->assertSame('oklch(57% 0.18 246deg)', $tokens['--cui-color-accent']['light']);
        $this->assertSame('', $tokens['--cui-color-accent']['dark']);

        // Inverse tokens exist only in the theme override groups: light default comes
        // from color-light, dark default from color-dark.
        $this->assertSame('oklch(98.5% 0.005 265deg)', $tokens['--cui-color-inverse']['light']);
        $this->assertSame('oklch(13.2% 0.02 265deg)', $tokens['--cui-color-inverse']['dark']);
    }

    public function testValueGroupsKeepTheirSingleDefault(): void
    {
        $typography = $this->provider->getGroups()[0];

        $this->assertSame('value', $typography['kind']);
        $this->assertSame('Typography', $typography['label']);
        $this->assertSame('clamp(0.9375rem, 0.9rem + 0.16vi, 1rem)', $typography['tokens'][1]['value']);
    }

    public function testTokenIndexMapsEveryTokenToItsKind(): void
    {
        $tokens = $this->provider->getTokens();

        $this->assertSame(['kind' => 'color'], $tokens['--cui-color']);
        $this->assertSame(['kind' => 'color'], $tokens['--cui-color-inverse']);
        $this->assertSame(['kind' => 'value'], $tokens['--cui-space-1']);
        $this->assertSame(['kind' => 'value'], $tokens['--cui-font-family']);
    }

    public function testMissingManifestThrows(): void
    {
        $provider = new TokenSchemaProvider('/nonexistent/blocks.json');

        $this->expectException(\RuntimeException::class);
        $provider->getGroups();
    }
}
