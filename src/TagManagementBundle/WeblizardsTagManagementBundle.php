<?php

namespace Weblizards\TagManagementBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class WeblizardsTagManagementBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
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

    public function getDescription()
    {
        return 'Tag and Snippet Management';
    }

    public function getVersion()
    {
        return '1.0';
    }
}
