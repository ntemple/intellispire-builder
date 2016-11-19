<?php
/* SVN FILE: $Id: init.inc.php 21 2013-03-15 19:35:01Z ntemple $*/
/**
 * Sabrayla PHP Classes and Functions
 *
 * Sabrayla Default Initialization Routines
 *
 * <p>
 * This file handles the core initilization functions.  While it may be overridden
 * on a per-project basis, the preferred method to make changes is by modifying
 * local.init.inc.php with local application functions.  This allows us to update
 * this file (for example, adding new database types) without changing the applications
 * core logic
 *
 * Possible Defines form parent file:
 * _SB_VALID_: must be defined to allow this file to be loaded. Security check.
 * _SB_SKIP_INIT_: Skip the external initialization and datbase creation phase
 *
 * Other additional granularity can be defined here if needed.
 *
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

# defined( '_SB_VALID_' ) or die( 'Direct Access to this location is not allowed.' );

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
define('PHPLIBPATH', dirname(__FILE__));


/*
 * Set includes to find libs
 */

$path  = ini_get('include_path');
$path  = $path . INCSEP . PHPLIBPATH;
# $path  = INCLUDES_DIR . INCSEP . SABRAYLADIR . INCSEP . PEARDIR . INCSEP . $path;
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

/*
 * Load our local config and librairies
 *
 * DO NOT CHANGE.  
 * You MUST create your own LOCAL config.php when installing.
 * This is the only way seperate installations can co-exist 
 * peacefully
 */

# include_once('config.php'); # DO NOT CHANGE
# require_once("Cache/Lite.php");
# require_once('PEAR.php');
# require_once('functions.inc.php');
# require_once('includes/context.class.php');
# require_once('mysql.class.php');
# require_once('sabrayla.class.php');
# require_once('class.phpmailer.php');

/**
 * P3P Compact privacy policy - defined in cofig
 */
if (defined('CP-P3P')) { header(CP-P3P); }

// continue processing

## ===

function stripslashes_deep($value) {
        $value = is_array($value) ? array_map("stripslashes_deep", $value) : stripslashes($value);
        return $value;
}

 
 
?>
