<?php
/**
* @version    $Id: mwgadmin.class.php 21 2013-03-15 19:35:01Z ntemple $
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

defined('_MWG') or die( 'Restricted access' );

require_once('isnclient/spyc.php');
require_once('admin/mwghelper.class.php');
require_once('mwg/mwgDataRegistry.class.php');

class BMGenStaller {

  /** @var mwgDataRegistry */
  var $registry = null;

  static function getInstance() {
    static $self = null;

    if (! $self) {
      $self = new self;
    }
    return $self;
  }

  private function __construct() {
    $this->registry = mwgDataRegistry::getInstance();
  }

  function getComponentMenuItems($selected) {
    $fullmenu = $this->registry->getMenu();
    $menu = '';

    foreach ($fullmenu as $k => $v) {
      if (is_scalar($v)) {
        //$items[$k] = $v;
        if ($selected == $k) {
          $class = 'a_selected';
        } else {
          $class = 'a';
        }
        if ($menu) $menu .= ' | ';
        $menu .= "<a href='controller.php?c=$k' class='$class'>$v</a>";
      }
    }
    //return $items;
    return $menu;
  }


  function getMainMenu($selected) {
    $items = array(
    'settings'   => '<a href="index.php?menu=settings">Settings</a>',
    'members'    => '<a href="members.php?menu=members">Members</a>',
    'design'     => '<a href="servlet.php?m=editor&menu=design">Site Design</a>',
    'membership' => '<a href="membership.php?menu=membership">Membership Levels</a>',
    'reports'    => '<a href="siteoverview.php?menu=reports">Reports</a>',
    );

    $menu = '';
    foreach ($items as $item => $link) {
      if ($item == $selected) {
        $link = str_replace("$selected\"", "$selected\" class='gt-active'", $link);
      }
      $menu .= "<li>$link</li>";     
    }
    return $menu;    
  }


  function getStandardMenuItems($selected) {
    $items = array(
    'settings'   => '<a href="index.php?menu=settings" class="a">Settings</a>',
    'members'    => '<a href="members.php?menu=members" class="a">Members</a>',
    'design'     => '<a href="promo.tools.php?menu=design" class="a">Site Design</a>',
    'membership' => '<a href="membership.php?menu=membership" class="a">Membership Configuration</a>',
    'help'       => '<a href="helpdesk.php?menu=help" class="a">Help Desk({newmessages})</a>',
    'reports'    => '<a href="siteoverview.php?menu=reports" class="a">Reports</a>',
    );

    $menu = '';
    foreach ($items as $item => $link) {
      if ($item == $selected) {
        $link = str_replace('class="a"', 'class="a_selected"', $link);
      }
      $menu .= "$link | ";     
    }
    trim($menu);
    trim($menu, '|');

    #   $menu .= '<a href="siteoverview.php?menu=settings" class="a">Reports</a>';
    return $menu;

  }


  function getVersion() {
    static $version;
    if (!$version)       
      return $version;

  }    


  function check_version() {
    $version = $this->getVersion();
    require_once(GENSTALL_BFPATH . '/components/genstaller/genstaller.class.php');

  }

}

function genstall_admin_start() {
  $gs = BMGenStaller::getInstance();
  ob_start();
}

function genstall_admin_end($t, $ocontent, $notemplate) {

  if ($notemplate) {
    echo $ocontent;
  } else {
    $t->set_var("content", $ocontent);
    $t->pparse("out", "main");
  }
  $content = ob_get_clean();

  $msg = '';
  try {
    if (needsUpdate($current, $latest)) {
      $msg = "<p class='warn'>Update found! New Version $latest You are running $current.<br /> <a href='controller.php?c=updates'>Please upgrade now!</a></p>";
    }
  } catch(Exception $e) {
    MWGHelper::setFlash('alert', "Could not reach update server. Please try again.<br> $e");      
  }
  $head = ''; 
  $submenu = $t->get_var('submenu');

  $page = mwg_admin_decorate($content, $submenu, $msg, $head);
  print $page;
}


function Xmwg_admin_decorate($content, $submenu, $message, $head) {
  $gs = BMGenStaller::getInstance();
  if (isset($_GET['c']))  
    $component = $_GET['c'];
  else 
    $component = '';  

  if (isset($_GET['menu'])) {
    $_SESSION['menu'] = $_GET['menu'];
  }

  $select_menu = $_SESSION['menu'];

  $component_menu = $gs->getComponentMenuItems($component);
  $menu = $gs->getStandardMenuItems($select_menu);


  if (!$submenu) {
    $path = MWG_BASE . '/admin/templates/submenu/admin.main.'. $select_menu . ".html";
    if (file_exists($path)) $submenu = file_get_contents($path);
  }

  $newmessages = MWG::getDb()->get_value('select count(*) from messages where member_id=1 and read_flag=0');  
  $sitename = SITENAME;     
  $version = trim(file_get_contents(MWG_BASE .'/config/version'));


  // content, menu, component_menu, message, head
  ob_start();
  include('templates/admin.main.php');
  $page = ob_get_clean();
  $page = str_replace('{newmessages}', $newmessages, $page);
  return $page;  
}

function mwg_admin_decorate($content, $submenu, $message, $head) {
  $response = MWG::getInstance()->response;
  
  $response->content = $content;
  if ($head)  
    $response->head    = $head;
  $response->head .= $response->_head;
  
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

  if (!$submenu) {
    $path = MWG_BASE . '/admin/templates/submenu/admin.main.'. $select_menu . ".html";
    if (file_exists($path)) $response->submenu = file_get_contents($path);
  }

  $response->newmessages = MWG::getDb()->get_value('select count(*) from messages where member_id=1 and read_flag=0');
  $response->sitename = MWG::getInstance()->get_setting("site_name");
  $response->version = trim(file_get_contents(MWG_BASE .'/config/version'));

  // content, menu, component_menu, message, head
  $response->output('layout.html');
}


function needsUpdate(&$current, &$latest) {
  require_once (GENSTALL_BASEPATH . '/components/genstaller/modelgenstaller.class.php');
  $model = new modelGenstaller();
  $manifest = $model->getManifest();
  $latest = trim($manifest->manifest['mwglatest']);
  $current = trim(file_get_contents(GENSTALL_BFPATH . '/config/version'));
  return (version_compare($current, $latest, '<'));
}

