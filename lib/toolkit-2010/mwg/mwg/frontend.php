<?php
/**
* @version    $Id: frontend.php 21 2013-03-15 19:35:01Z ntemple $
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

// MWG frontend library hooks
/**
The main MWG class.  Here to protect ourself from
global variables pervasive throughout the script.
requires PHP 5.
*/
require_once('includes/sbutil.class.php');
require_once('includes/mysqldb.class.php');
require_once('mwgDataRegistry.class.php');
require_once('mwgDocument.class.php');
require_once('mwgRequest.class.php');
require_once('mwgResponse.class.php');
require_once('mwgActions.class.php');
require_once('mwgBaseGizmo.class.php');
require_once('mwgMember.class.php');
require_once(MWG_BASE . '/components/themes/modelthemes.class.php');

// Plugins - we need a plugin manager
require_once('shortcodes.php');

sbutil::$LOG = false;
sbutil::initLogging(MWG_BASE . '/tmp/app.log');
sbutil::debug('START');
sbutil::$print_trace = false;

require_once('MWG.class.php');


function plugin_basename($file) {

  $file = str_replace('\\','/',$file); // sanitize for Win32 installs
  $file = preg_replace('|/+|','/', $file); // remove any duplicate slash
  $plugin_dir = str_replace('\\','/',WP_PLUGIN_DIR); // sanitize for Win32 installs
  $plugin_dir = preg_replace('|/+|','/', $plugin_dir); // remove any duplicate slash
  $mu_plugin_dir = str_replace('\\','/',WPMU_PLUGIN_DIR); // sanitize for Win32 installs
  $mu_plugin_dir = preg_replace('|/+|','/', $mu_plugin_dir); // remove any duplicate slash
  $file = preg_replace('#^' . preg_quote($plugin_dir, '#') . '/|^' . preg_quote($mu_plugin_dir, '#') . '/#','',$file); // get relative path from plugins dir
  $file = trim($file, '/');
  return $file;
}


function mwg_check_admin_login($redirect = false) {
mwg_session_start();
if (! $_SESSION['admin_sess_id']) {
  if ($redirect) 
  {
     header("Location: " . MWG_BASEHREF . '/admin/');
  } 
  die ("Restricted Access");
}
return true;
}

