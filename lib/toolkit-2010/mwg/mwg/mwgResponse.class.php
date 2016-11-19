<?php
/**
* @version    $Id: mwgResponse.class.php 21 2013-03-15 19:35:01Z ntemple $
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


defined('_MWG') or die ('Restricted Access');

require_once('context.class.php');
require_once('mwgActions.class.php');

class mwgResponse extends Context {

  var $_head;
  var $_success = null;
  var $message = ''; 
  
  var $layout_body_class  = 'gt-fixed';
  var $layout_menu_class  = 'sf-navbar';
  var $layout_menu_size   = '75px';
  var $sf_navbar = 1;
  var $layout_gt_cols     = '';
  
 
  

  function __construct() {
    parent::__construct(MWG_ADMIN . '/themes/default', MWG_BASE . '/tmp/tcache');
    $this->_head = '';
    
    $this->dispatcher = new mwgDispatcher();
    
    // Necessary constants
    $this->MWG_BASEHREF = MWG_BASEHREF;
    
  }

  function activateSidebar($text) {
    $this->sidebar = $text;
    $this->layout_gt_cols = 'gt-cols';
  }
  
  function contentBox($title = '') {
    $this->contentbox = $title;
  }
  
  function initEditor() {
    $editor = MWG::getInstance()->get_setting('admin_editor', 'none');
    switch ($editor) {
      case 'none': break;
      case 'tinymce': $this->includeHeaderFile('editor_tinymce.js'); break;
      case 'editarea': break;
    }
  }
  
  function includeHeaderFile($file) {
    $this->_head .= $this->getOutput($file);
  }

  function addHeader($text) {
    $this->_head .= "\n$text\n";
  }

  function newContext() {
    return clone($this);
  }

  function setFlash($message, $type = 'info') {
    $_SESSION['flash'] = array( 
    'message' => $message,
    'level'   => $type
    );
  }

  function getFlash() {
    if (isset($_SESSION['flash'])) {
      $this->message = $_SESSION['flash']['message'];
      $this->level   = $_SESSION['flash']['level'];
      unset($_SESSION['flash']);
    }
  }

  function route($module, $action='default', $data= '') {
    $location = "servlet.php?m=$module&a=$action&$data";
    $this->redirect($location);
  }

  function redirect($path, $message = '', $type = info) {
    if ($message) {
      $this->setFlash($message, $type);
    }

    header("Location: $path");
    exit(0);
  }



  function marshall() {
    require_once('includes/IXR_Library.inc.php');

    $result = clone($this);
    unset($result->opt);
    unset($result->_head);
    unset($result->_template);
    unset($result->elements);
    // TODO: unset all _ (private) vars;
    $r = new IXR_Value($result);
    return $r->getXml();
  }
  
  function setting($var) {
    switch ($var) {
      case 'fixed': return false;
      case 'sidebar': return false;      
    }
    return false;
  }

}


class mwgDispatcher {
  
  /**
  * This probably belongs someplace else.  
  * A dispatcher class?
  * 
  * @param string $module
  * @param string $action
  */
  function partial($module, $action = 'default') {
    /** @var mwgResponse */
    $response = MWG::getInstance()->response;
    $request  = MWG::getInstance()->request;
    
    $module = strtolower($module);
    $action = strtolower($action);

    $instance = $this->classFactory($module);

    # default partial template
    $response->_partial = $module . "_" . $action . "_view.html";
    $response->partialObj = $instance; // allows template to access the view object

    $method = 'do' . ucwords($action) . 'View';
    if (! method_exists($instance, $method) ) $method = 'doView';
    
    try {
      call_user_func(array($instance, $method), $request, $response);      
    } catch (Exception $e) {
      throw $e;
    } 
    $response->output($response->_partial);
  }
  
  function dispatch($request, $response) {
  # @todo routing for SEF urls

  $module  = strtolower($request->safeGet('m'));
  $action  = strtolower($request->safeGet('a', 'default'));

  $instance =  $this->classFactory($module);

  # default template
  $response->_template = $module . "_" . $action . "_view.html";
  $response->viewObj = $instance; // allows template to access the view object

  $method = 'do' . ucwords($action) . 'View';
  if (! method_exists($instance, $method) ) $method = 'doView';

  try {
   call_user_func(array($instance, $method), $request, $response);
  } catch (Exception $e) {
    throw $e;
  } 
  return $instance;
}

function classFactory($module) {
  $class     = $module . 'Actions';
  if (!class_exists($class)) {
     $classfile = "$class.class.php";
     require_once("classes/$classfile");
  }
  $instance = new $class;
  return $instance;
}



function decorate($response) {
  $response->content = $response->getOutput(); // Parse the content
  $response->head    = $response->_head;
  

  $response->getFlash();

  if (! $response->message) {
  try {  
    if (needsUpdate($current, $latest)) {
      $response->setFlash("Update found! New Version $latest You are running $current.
                           <a href='controller.php?c=updates'>Please upgrade now!</a>", 'warn');
    }
  } catch(Exception $e) {
    $response->setFlash("Could not reach update server. Please try again.<br> $e", 'alert');
  }
  $response->getFlash();
  }
  
  
  // $submenu = $t->get_var('submenu');

  $gs = BMGenStaller::getInstance();
  if (isset($_GET['c']))
    $component = $_GET['c'];
  else
    $component = '';

  if (isset($_GET['menu'])) {
    $_SESSION['menu'] = $_GET['menu'];
  }

  $select_menu = $_SESSION['menu'];

  $response->component_menu = $gs->getComponentMenuItems($component);
  $response->menu = $gs->getStandardMenuItems($select_menu);
  $response->li_menu = $gs->getMainMenu($select_menu);

/*
  if (!$submenu) {
    $path = MWG_BASE . '/admin/templates/submenu/admin.main.'. $select_menu . ".html";
    if (file_exists($path)) $response->submenu = file_get_contents($path);
  }
*/

  $response->newmessages = MWG::getDb()->get_value('select count(*) from messages where member_id=1 and read_flag=0');
  $response->sitename = MWG::getInstance()->get_setting("site_name");
  $response->version = trim(file_get_contents(MWG_BASE .'/config/version'));


  // content, menu, component_menu, message, head
  $response->output('layout.html');
}


  
  
}


