<?php
/* SVN FILE: $Id: init.inc.php 21 2013-03-15 19:35:01Z ntemple $*/
/**
 * Sabrayla PHP Classes and Functions
 *
 * Sabrayla Default Initialization Routines - Simple version
 *
 * @category   sabrayla
 * @package    sabrayla
 * @author     Nick Temple <Nick.Temple@intellispire.com>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.sabrayla.com/license/1_0.txt Sabrayla License 1.0
 * @version    SVN: $Id: init.inc.php 21 2013-03-15 19:35:01Z ntemple $
 * @since      File available since Release 0.1
 *
 */

/**
 * defines and pathing
 */

defined( '_SB_VALID_' ) or die( 'Direct Access to this location is not allowed.' );

if (version_compare(phpversion(), '5.1') < 0) {
    die("You must use PHP 5.1 or greater!");
}

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
define('PATHSEP', $pathsep);
define('INCSEP', $incsep);

/*
 * Get Path Info
 */

# determinePath();
define('PHPLIBPATH', dirname(dirname(__FILE__))); // assume we are in config/ directory

/*
 * Set includes to find libs
 */

$path  = ini_get('include_path');
$path  = $path . INCSEP . PHPLIBPATH . INCSEP . PHPLIBPATH . PATHSEP . 'pear';
ini_set('include_path', $path);

/*
 * Dump magic_quotes
 */

set_magic_quotes_runtime(0);
if( get_magic_quotes_gpc() ) {
  stripslashes_deep($_GET);
  stripslashes_deep($_POST);
  stripslashes_deep($_COOKIE);
  stripslashes_deep($_REQUEST);
}

/*
 * NOTE: Assume register_globals off since
 * that can be controlled from .ini file
 * If it's on, we can do some hacks to unregister
 * here if required, but that's not necessary if we
 * can avoid it.
 */

function stripslashes_deep($value) {
        $value = is_array($value) ? array_map("stripslashes_deep", $value) : stripslashes($value);
        return $value;
}

 
