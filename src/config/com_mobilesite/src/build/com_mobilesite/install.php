<?php
defined('_JEXEC') or die('No Access');

$installer = JInstaller::getInstance();
$path = $installer->getPath('source');

$man = $installer->getManifest();

if(version_compare(JVERSION,'1.6.0','ge')) {
    install_16($path, $man);
} else {
    install_15($path, $man);
}

function install_16($path, $man){
    
    $subs = $man->subinstall->folder;
    
    foreach($subs as $sub){
        $subinstaller = new JInstaller();
        $subinstaller->install($path .'/'. $sub);
    }
}

function install_15($path, $man){
    $mf = $man->document;
    foreach ($mf->children() as $node)
    {
      if ($node->name() == 'subinstall') {
        $pkgs = $node->children();
        foreach ($pkgs as $pkg) {
          if ($pkg->name() == 'folder') {
            $subinstaller = new JInstaller();
            $subinstaller->install($path .'/'. $pkg->data());
          }
        }
      }
    }
}

