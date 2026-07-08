<?php

declare(strict_types=1);

namespace CombatUI\CombatUIOpenDxpBundle\Tests\Unit\Theme;

use Codeception\Test\Unit;
use CombatUI\CombatUIOpenDxpBundle\Theme\TokenOverrideValidator;
use CombatUI\CombatUIOpenDxpBundle\Theme\TokenSchemaProvider;

final class TokenOverrideValidatorTest extends Unit
{
    private TokenOverrideValidator $validator;

    protected function _before(): void
    {
        $this->validator = new TokenOverrideValidator(
            new TokenSchemaProvider(\dirname(__DIR__, 2) . '/Support/fixtures/blocks.json'),
        );
    }

    public function testNormalizesTrimsAndDropsEmptyValues(): void
    {
        $result = $this->validator->normalize([
            '--cui-space-1' => ['value' => '  0.5rem  '],
            '--cui-color' => ['light' => "oklch(30%\n 0.05 264deg)", 'dark' => ''],
            '--cui-color-accent' => ['light' => '   '],
        ]);

        $this->assertSame([
            '--cui-space-1' => ['value' => '0.5rem'],
            '--cui-color' => ['light' => 'oklch(30% 0.05 264deg)'],
        ], $result);
    }

    public function testUnknownTokenIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('--cui-evil');

        $this->validator->normalize(['--cui-evil' => ['value' => 'red']]);
    }

    public function testColorSlotOnValueTokenIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('slot');

        $this->validator->normalize(['--cui-space-1' => ['dark' => '1rem']]);
    }

    public function testValueSlotOnColorTokenIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->validator->normalize(['--cui-color' => ['value' => 'red']]);
    }

    public function testValuesThatCouldEscapeTheDeclarationAreRejected(): void
    {
        $dangerousValues = [
            'semicolon' => '1rem; background: red',
            'closing brace' => '1rem } body { color: red',
            'opening brace' => '{',
            'markup' => '<style>',
            'comment open' => '1rem /* x',
            'at rule' => '@import "x"',
            'url' => 'url(https://evil.example)',
            'url mixed case' => 'URL(https://evil.example)',
            'backslash escape' => '\\75rl(evil)',
        ];

        foreach ($dangerousValues as $label => $value) {
            try {
                $this->validator->normalize(['--cui-space-1' => ['value' => $value]]);
                $this->fail(sprintf('Expected the "%s" value to be rejected.', $label));
            } catch (\InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }

    public function testOverlongValueIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('too long');

        $this->validator->normalize(['--cui-space-1' => ['value' => str_repeat('a', 401)]]);
    }
}
