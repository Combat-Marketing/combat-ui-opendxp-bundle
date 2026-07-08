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

namespace CombatUI\CombatUIOpenDxpBundle;

use CombatUI\Bundle\CoreBundle\CombatUICoreBundle;
use OpenDxp\Extension\Bundle\AbstractOpenDxpBundle;
use OpenDxp\Extension\Bundle\Installer\InstallerInterface;
use OpenDxp\Extension\Bundle\OpenDxpBundleAdminClassicInterface;
use OpenDxp\Extension\Bundle\Traits\BundleAdminClassicTrait;
use OpenDxp\Extension\Bundle\Traits\PackageVersionTrait;
use OpenDxp\HttpKernel\Bundle\DependentBundleInterface;
use OpenDxp\HttpKernel\BundleCollection\BundleCollection;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;

class CombatUIOpenDxpBundle extends AbstractOpenDxpBundle implements DependentBundleInterface, OpenDxpBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;
    use PackageVersionTrait;

    public function getNiceName(): string
    {
        return 'Combat UI';
    }

    public function getComposerPackageName(): string
    {
        return 'combat-ui/core-opendxp-bundle';
    }

    public static function registerDependentBundles(BundleCollection $collection): void
    {
        $collection->addBundle(new WebpackEncoreBundle());
        $collection->addBundle(new CombatUICoreBundle());
    }

    public function getJsPaths(): array
    {
        return [
            '/bundles/combatuiopendxp/js/startup.js',
            '/bundles/combatuiopendxp/js/theme-editor.js',
        ];
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/combatuiopendxp/css/admin.css',
        ];
    }

    public function getInstaller(): ?InstallerInterface
    {
        return $this->container->get(Installer::class);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
