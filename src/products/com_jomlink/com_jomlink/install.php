<?php
defined('_JEXEC') or die('No Access');

class /*com_{jomlink}*/InstallerScript  {

    public function postflight($route, $adapter) {

        if ($route == 'install' || $route == 'update') {

            $installer = JInstaller::getInstance();
            $path = $installer->getPath('source');

            $man = $installer->getManifest();

            $subs = $man->subinstall->folder;

            foreach($subs as $sub){
                $subinstaller = new JInstaller();
                $subinstaller->install($path .'/'. $sub);
            }
        }
    }

}

