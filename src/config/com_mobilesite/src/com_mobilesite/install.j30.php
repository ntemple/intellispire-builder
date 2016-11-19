<?php

// http://docs.joomla.org/Manifest_files
// For version 3.0+, only.


class com_mobilesiteInstallerScript  {

    public function postflight($route, $adapter) {

        // Fixup manifest name after install
        if ($route == 'install' || $route == 'update') {
            $this->fixManifest($adapter);

            $installer = $adapter->get('parent');

            $path = $installer->getPath('source');

            $man = $installer->getManifest();
            $subs = $man->subinstall->folder;

            foreach($subs as $sub){
              $subinstaller = new JInstaller();
              $subinstaller->install($path .'/'. $sub);
            }
        }
    }

    private function fixManifest($adapter)
    {
        $filesource = $adapter->get('parent')->getPath('source').'/manifest.xml';
        $filedest = $adapter->get('parent')->getPath('extension_root').'/mobilesite.xml';

        if (!(JFile::copy($filesource, $filedest)))
        {
            JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_FAIL_COPY_FILE', $filesource, $filedest), JLog::WARNING, 'jerror');

            if (class_exists('JError'))
            {
                JError::raiseWarning(1, 'JInstaller::install: '.JText::sprintf('Failed to copy file to', $filesource, $filedest));
            }
            else
            {
                throw new Exception('JInstaller::install: '.JText::sprintf('Failed to copy file to', $filesource, $filedest));
            }
            return false;
        }

        return true;
    }
}
