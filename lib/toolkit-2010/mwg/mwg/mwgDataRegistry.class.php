<?php
defined('_MWG') or die( 'Restricted access' );
require_once('isnclient/spyc.php');

class mwgDataRegistry {

  var $data = null;
  var $path = null;
  var $dirty = false;

  static function getInstance() {
    static $self = null;

    if (! $self) {
      $self = new self();
    }
    return $self;
  }

  private function __construct() {
    $this->path = MWG_BASE . '/config/registry.yml.php';
    if (file_exists($this->path)) {      
      $file = Spyc::YAMLLoad($this->path);
      $this->data = $file['mwg'];
    } else {
      $this->data = array();
      $this->write(true);
    }    
  }

  function get($key, $default = '', $set = false) {
    if (isset($this->data[$key])) return $this->data[$key];
    
    if ($set) {
      $this->set($key, $default);
    }    
    return $default;    
  }
  
  function set($key, $value) {
    $this->data[$key] = $value;
    $this->write(true); // @todo optimize
  }

  // Can we do some sort of locking?
  function write($force = false) {
    if (!$this->dirty && !$force) return;
    $data = array();
    $data['mwg'] = $this->data;
    $this->writeYML($this->path, $data);
  }

  function writeYML($file, $data) {
    $yml = Spyc::YAMLDump($data);
    $f = fopen($file, 'w+');
    fwrite($f, "#\n#<?php die(); ?>\n$yml");
    fclose($f);
  }

  function getMenu($selected = null) {
    $menu = array();

    $ext = $this->findExtensions();
    foreach ($ext as $ident => $e) {
      if (isset($e['menu'])) {
        if (isset($e['updatecheck'])) {
          $folder = $e['updatecheck'];  // @deprecated 1.1
        } else if (isset($e['folder'])) {
          $folder = $e['folder'];
        } else {
          $folder = ''; 
        }
        if ($folder)  
          $menu[$folder] = $e['menu'];
      }
    }
    return $menu;
  }

  function findExtensions() {
     $extdir = GENSTALL_BASEPATH .DS . 'components';
     $components = array();

     $dir = opendir($extdir);
     while (false !== ($file = readdir($dir))) {
       if ($file[0] != '.') {
         $configFile = $extdir . DS . $file . DS . $file . '.yml.php';
         if (file_exists($configFile)) {
           $manifest = Spyc::YAMLLoad($configFile);
           $identity  = $manifest['identity'];
           $components[$identity] = $manifest;
         }
       }
     }
     closedir($dir);
     return $components;
  }

}
