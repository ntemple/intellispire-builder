<?php
/**
* @version    $Id: MWG.class.php 21 2013-03-15 19:35:01Z ntemple $
* @package    MWG
* @copyright  Copyright (C) 2010 Intellispire, LLC. All rights reserved.
* @license    GNU/GPL v2.0, see LICENSE.txt
*
* Marketing Website Generator is free software.
* This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class MWG {

  /** @var mwgDocument */
  var $document;

  /** @var Template */
  var $template;  // BFM's template system

  /** @var modelTheme */
  var $theme;

  /** @var WMGDataRegistry */
  var $registry;

  /** @var mwgRequest */
  var $request;

  /** @var mwgResponse */
  var $response;

  /** @var mwgSetting */
  var $settings;

  /** @var mwgSession */
  var $session;

  // $this->BASEHREF =  MWG_BASEHREF;

  #  var $theme      = 'computer1';  // The name of the theme
  #  var $theme_type = 'joomla';     // the type of the theme
  var $site_name  = 'Marketing Website Generator';

  private function __construct() {
    $this->request  = new mwgRequest();
    $this->response = new mwgResponse();
    $this->session  = new mwgSession(); // Could be a factory

    $this->registry = mwgDataRegistry::getInstance();    

    $default_theme = $this->registry->get('theme.default', 'bfm', true);
    if (self::is_logged_in()) {
      $default_theme = $this->registry->get('theme.defaultbe', $default_theme);
    } 

    $this->theme = new modelThemes($default_theme);
    $this->document = new mwgDocument();

    /* Switcher needs to be in a plugin */
    if (isset($_GET['theme'])) {
      $this->theme->switchThemes($_GET['theme']);      
    }  else  {
      if (isset($_COOKIE['theme'])) $this->theme->switchThemes($_COOKIE['theme']);
    }

    $this->loadPlugins();
  }
  
  /**
  * @returns MWG $mwg
  */

  static function getInstance() {
    static $instance;
    
    if (!$instance) {
      $instance = new MWG();
    }

    return $instance;
  }

  /**
  * @returns mysqldb $db
  */
  static function getDb() {
    static $db;

    if (!$db) {
      $db = new mysqldb();
      $db->connect(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
      $db->connect();
    }

    return $db;
  }

  /**
  * @returns mwgSession $session 
  */
  static function getSession() {
    return MWG::getInstance()->session;
  }

  /**
  * @returns mwgRequest 
  */
  static function getRequest() {
    return MWG::GetInstance()->request;    
  }
  /**
  * @returns mwgResponse
  */

  static function getResponse() {
    return MWG::GetInstance()->response;
  }
  
  /**
  * @returns modelThemes
  */
  static function getTheme() {
    return MWG::getInstance()->theme;
  }


  function loadPlugins() {
    // Load ALL the plugins.  
    // @todo make this more efficient
    $plugin_dirs = $this->listDir(MWG_BASE . '/plugins/');
    foreach($plugin_dirs as $ptypes) {
      $this->loadPluginGroup($ptypes);
    }          
  }

  function loadPluginGroup($ptype) {
    $plugins = $this->listDir(MWG_BASE . '/plugins/' . $ptype);
    foreach ($plugins as $plugin) {
      $files = $this->listFiles(MWG_BASE . "/plugins/$ptype/$plugin");
      foreach ($files as $file) {
        $ext = explode('.', $file);
        $ext = array_pop($ext);
        if ($ext == 'php') include(MWG_BASE . "/plugins/$ptype/$plugin/$file");
      }
    }
  }

  function listFiles($base) {
    $dirs = array();

    if ($handle = opendir($base)) {
      while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && $file != '.svn' && !is_dir("$base/$file") ) {                      
          $dirs[] = $file;
        }
      }
      closedir($handle);
    }
    return $dirs;
  }


  function listDir($base) {
    $dirs = array();

    if ($handle = opendir($base)) {
      while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && $file != '.svn' && is_dir("$base/$file") ) {                      
          $dirs[] = $file;
        }
      }
      closedir($handle);
    }
    return $dirs;
  }

  function runEvent($method, $args) {
    if (!class_exists('modelGizmo')) require_once(MWG_BASE . '/components/gizmo/modelgizmo.class.php');
    $model = new modelGizmo();
    $gizmos = $model->getActiveGizmos();

    foreach ($gizmos as $gizmo) {
      $func = array($gizmo, $method);
      if (is_callable($func)) {
        call_user_func_array($func, $args);
      }
    }    
  }

  /**
  * @todo: return the search path for the active theme
  */
  function getTemplatePath() {
    //    return array('/var/www/html/mwg/script/themes/mwg-default');
    return array();
  }

  function start() {
    $this->runEvent('afterStart', array($this->request, $this->response));
  }
  
  /**
  * Render a completed page.
  * Necessary because OTO's aren't currently using the theme section
  * 
  * @param mixed $content
  */
  
  function render($content) {
     /* @todo: If  this is a full page, we should parse out the meta fields and 
     * Maybe this is only done if the document is empty?
     * Specifically necessary for the front end OTO's
     */
     
    $this->runEvent('beforeDoShortcode', array($this->document, &$content));
    // Apply shortcode filter
    $content = do_shortcode($content); 

    $this->runEvent('beforeDocumentRender', array($this->document, &$content));

    // Set the code in the document, and render
    $this->document->setContent($content);
    $page = $this->document->renderDocument();
    $this->runEvent('afterDocumentRender', array($page));
    return $page;    
  }
  
  function end(Template $tpl) {
    if (defined('SITENAME')) $this->site_name = SITENAME;

    $this->template = $tpl;
    $this->document->setDescription($this->tplGet('description'), true);
    $this->document->setKeywords($this->tplGet('keywords'), true);
    $this->document->setTitle(trim($this->tplGet('keywords_title')), false, true);
    $this->document->setTitle($this->site_name, false, true);

    $content = $this->theme->process($tpl);
    $page = $this->render($content);
    print $page;
  }


  function getContent($section = 'content') {
    #    $this->template->set_var($section, '{content}');
    $this->template->set_file($section, "$section.html");    

    ob_start();
    $this->template->pparse('out', $section);
    return ob_get_clean();
  }

  /**
  * Return a list of menu items as raw links 
  * or unordered list
  * 
  * @param mixed $type
  */

  function getMenu($type = 'list') {
    global $sess_id, $membership_id;

    $db = $this->getDb();
    $array = array();

    if (isset($sess_id)) {
      $items = generate_main_menu_list('members', $membership_id);      
    } else {
      $items = generate_main_menu_list('main');      
    }

    if ($type == 'items') {
      return $items;      
    }


    foreach ($items as $item) {
      $array[] = _render_link($item);
    }

    if ($type == 'list') {
      $menu = '';
      foreach ($array as $item) {
        $menu .= "<li>$item</li>\n";
      }  
      $menu ="<ul>\n$menu</li>\n";
    } else {
      $menu = $array;
    }

    return $menu;
  }

  function tplGet($var) {
    return $this->template->varvals[$var];
  }


  /**
  This should be changed to be page specific. Right now, BFM
  has one title / description / keywords for all pages
  */ 
  /**
  * Get the extra head strings from the document
  * @deprecated 1.1
  * 
  */

  function getHead() {
    return '';
    //    return $this->document->getHead();
  }
  /**
  * Get the document title
  * @deprecated 1.1
  * 
  */

  function getTitle() {
    return $this->document->getTitle();
  }         

  /**
  * @todo Optimize by loading all settings, once
  * 
  * @param mixed $setting_name
  * @param mixed $default
  */
  function get_setting($setting_name, $default = null) {
    static $settings = array();
    static $nsettings = array();

    if (isset($settings[$setting_name])) return $settings[$setting_name];
    if (isset($nsettings[$setting_name])) return $default;

    // Can't find it!
    $value = $this->_get_setting($setting_name);
    if ($value == null) {
      $nsettings[$setting_name] = true;
      $value = $default;
    } else {
      $settings[$setting_name] = $value;
    }    

    return $value;
  }

  function _get_setting($setting_name, $default = null)
  {
    /* check MWG settings, first */
    $value = $this->getDb()->get_value('select value from mwg_setting where name=?', $setting_name);  
    if ($value) return $value;

    $value = $this->getDb()->get_value('select value from settings where name=?', $setting_name);  
    if ($value) {
      return stripslashes($value);      
    }    
    if ($default) {
      return $default;
    }    
    return $value;
  }

  static function is_logged_in() {
    /*
    $admin_logged_in = false;
    if (isset($_SESSION['admin_sess_id']) && $_SESSION['admin_sess_id'] == md5($mwg->get_setting("secret_string")."-".ADMIN_PASSWORD))
    {
    $admin_logged_in = true;
    } else {
    session_destroy();
    header("location:login.php");
    die();
    }
    return $admin_logged_in();
    */    

    if (isset($_SESSION['sess_id'])) {
      return true;
    } else {
      return false;
    }
  }

}

function mwg_shortcode_gizmo($atts) {
  $id = $atts['id'];
  if (!$id) return;

  if (!class_exists('modelGizmo')) require_once(MWG_BASE . '/components/gizmo/modelgizmo.class.php');
  $model = new modelGizmo();
  $gizmos = $model->getActiveGizmos();
  if (!isset($gizmos[$id])) return;

  $gizmo = $gizmos[$id];

  if ($gizmo) {
    return $gizmo->render($atts);
  }
}

/**
* Displays a group of modules
* 
* @todo Refactor to themeing engine
* 
* @param mixed $atts
*/
function mwg_shortcode_gposition($atts) {
   $position = $atts['name'];
   if (!$position) return;
   return MWG::getTheme()->renderGizmos($position, $atts);
}

add_shortcode('gizmo', 'mwg_shortcode_gizmo');
add_shortcode('gposition', 'mwg_shortcode_gposition');


