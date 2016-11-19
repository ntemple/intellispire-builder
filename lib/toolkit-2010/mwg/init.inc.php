<?php
/**
* @version    $Id: init.inc.php 21 2013-03-15 19:35:01Z ntemple $
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
*
* @todo: rewrite to be overridable, or use namespace / singleton?
*/

if (!defined('_MWG')) die();
define('_SB_VALID_', true);                                
///magic error_reporting(0);
                    
// Find basehref for self-referencing URL's
$parts = explode('/', $_SERVER['SCRIPT_NAME']);

$self = array_pop($parts);
$admin = array_pop($parts);
if ($admin == 'admin') {
  define('MWG_BE', true);
  define('MWG_FE', false);  
} else {
  define('MWG_BE', false);
  define('MWG_FE', true);
  array_push($parts, $admin);     
}

$href = implode('/', $parts);

if (isset($_SERVER['HTTP_HOST'])) {
  define('MWGU_BASE', 'http://' . $_SERVER["HTTP_HOST"] . $href);
} else {
  define('MWGU_BASE', NULL);
}

function defineif($name, $value) {
  if (! defined($name)) define($name, $value);
  notice($name . ': ' . constant($name));
}

function notice($string) {
#  echo $string . "\n";
}

// You can move these out of the base, anyplace open_basedir allows
defineif('MWGD_BASE',   dirname(dirname(__FILE__)));
defineif('MWGD_WEB',    dirname(MWGD_BASE));
defineif('MWGD_CORE',   MWGD_BASE . '/sabrayla');
defineif('MWGD_LIB',    MWGD_BASE . '/sabrayla');
defineif('MWGD_APP',    MWGD_BASE . '/app');
defineif('MWGD_DATA',   MWGD_BASE . '/data');
defineif('MWGD_TMP',    MWGD_DATA . '/tmp');


/*
* Determine seperators
*/
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $incsep = ';'; // Windows
  $pathsep = '\\';
} else {
  $incsep  = ':'; // default for linux
  $pathsep = '/';
}
define('DS', $pathsep);
define('IS', $incsep);


/*
* Set includes to find libs
*/
$path  = ini_get('include_path');
$path  = $path . IS . MWGD_LIB . IS . MWGD_LIB . "/PEAR"; 
ini_set('include_path', $path);

/*
 require_once(MWGD_BASE . '/config/constants.php');
if (!defined('DB_HOST')) {
  header("Location: "  . MWG_BASEHREF . '/install/index.php');
  exit();
}
*/

/*
require_once('mwg/frontend.php');     // Provide MWG singleton
require_once('mail.class.php');       // Provide Mail
require_once('isnclient/spyc.php');   // Provide core YML parsing
require_once('upgrade/upgrade.php');

$notemplate = false; // define here until we can completely rebuild legacy template system
MWG::getInstance();
*/


spl_autoload_register('mwg_autoload');

function mwg_autoload($class)
    {
$class = str_replace('_', '/', $class);
echo($class);
        set_include_path(get_include_path(). IS. MWGD_LIB);
        spl_autoload_extensions('.class.php');
        spl_autoload($class);
    }


