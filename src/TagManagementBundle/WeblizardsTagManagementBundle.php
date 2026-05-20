<?php

namespace Weblizards\TagManagementBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;
use Pimcore\Extension\Bundle\Traits\BundleAdminClassicTrait;

class WeblizardsTagManagementBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;

    /**
     * Register classic admin assets via the Pimcore 11-compatible admin-classic interface.
     */
    public function getJsPaths(): array
    {
        return [
            '/bundles/weblizardstagmanagement/js/pimcore/startup.js',
            '/bundles/weblizardstagmanagement/js/pimcore/settings/tagmanagement/panel.js',
            '/bundles/weblizardstagmanagement/js/pimcore/settings/tagmanagement/item.js',
        ];
    }

//    public function getInstaller()
//    {
//        return $this->container->get(Installer::class);
//    }

    public function getDescription(): string
    {
        return 'Tag and Snippet Management';
    }

    public function getVersion(): string
    {
        return '1.0';
    }
}
