<?php

declare(strict_types=1);

namespace CombatUI\CombatUIOpenDxpBundle\Tests\Unit;

use Codeception\Test\Unit;
use CombatUI\Bundle\CoreBundle\CombatUICoreBundle;
use CombatUI\CombatUIOpenDxpBundle\CombatUIOpenDxpBundle;
use CombatUI\CombatUIOpenDxpBundle\DependencyInjection\CombatUIOpenDxpExtension;
use OpenDxp\Extension\Bundle\OpenDxpBundleInterface;
use OpenDxp\HttpKernel\BundleCollection\BundleCollection;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;

final class CombatUIOpenDxpBundleTest extends Unit
{
    private CombatUIOpenDxpBundle $bundle;

    protected function _before(): void
    {
        $this->bundle = new CombatUIOpenDxpBundle();
    }

    public function testImplementsOpenDxpBundleInterface(): void
    {
        $this->assertInstanceOf(OpenDxpBundleInterface::class, $this->bundle);
    }

    public function testNiceName(): void
    {
        $this->assertSame('Combat UI', $this->bundle->getNiceName());
    }

    public function testComposerPackageNameMatchesComposerJson(): void
    {
        $composer = json_decode(
            (string) file_get_contents($this->bundle->getPath() . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame($composer['name'], $this->bundle->getComposerPackageName());
    }

    public function testPathIsBundleRootAboveSrc(): void
    {
        $this->assertFileExists($this->bundle->getPath() . '/composer.json');
        $this->assertDirectoryExists($this->bundle->getPath() . '/templates/areas');
        $this->assertDirectoryExists($this->bundle->getPath() . '/src');
    }

    public function testRegistersCoreAndEncoreBundlesAsDependencies(): void
    {
        $collection = new BundleCollection();

        CombatUIOpenDxpBundle::registerDependentBundles($collection);

        $this->assertTrue($collection->hasItem(CombatUICoreBundle::class));
        $this->assertTrue($collection->hasItem(WebpackEncoreBundle::class));
    }

    public function testContainerExtensionResolvesByConvention(): void
    {
        $this->assertInstanceOf(CombatUIOpenDxpExtension::class, $this->bundle->getContainerExtension());
    }
}
