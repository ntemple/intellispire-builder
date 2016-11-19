<?php
/* SVN FILE: $Id: sb_init.inc.php 21 2013-03-15 19:35:01Z ntemple $*/
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
 * @version    SVN: $Id: sb_init.inc.php 21 2013-03-15 19:35:01Z ntemple $
 * @since      File available since Release 0.1
 *
 */

/**
 * defines and pathing
 */

defined( '_SB_VALID_' ) or die( 'Direct Access to this location is not allowed.' );

/*
* Make sure we have relatively recent technology
*/
if (version_compare(phpversion(), '5.1') < 0) {
    die("You must use PHP 5.1 or greater!");
}

/**
 * P3P Compact privacy policy - defined in cofig
 */
if (defined('CP-P3P')) { header(CP-P3P); }


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

determinePath();

/*
 * Set includes to find libs
 */

$path  = ini_get('include_path');
$path  = INCLUDES_DIR . INCSEP . SABRAYLADIR . INCSEP . PEARDIR . INCSEP . $path;
ini_set('include_path', $path);
# print $path;
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

include_once('config.php'); # DO NOT CHANGE
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


# ===========================================================
/**
 * Based on where we are called from,
 * determine the various path elements and set
 * the appropriate consants for the rest of the application
 *
 */

function determinePath() {
  $BASEPATH = dirname(__FILE__);

  define ('INCLUDES_DIR',   "$BASEPATH/");
  define ('THEME_DIR',      INCLUDES_DIR . 'themes');
  define ('CACHE',          INCLUDES_DIR . 'tmp/');
  define ('TEMPLATEDIR',    INCLUDES_DIR . 'templates/');
  define ('COMPILEDIR',     INCLUDES_DIR . 'tmp/');
  define ('EVENTDIR',       INCLUDES_DIR . 'handlers/');
  define ('SABRAYLADIR',    INCLUDES_DIR . 'sabrayla/');
  define ('PEARDIR',        INCLUDES_DIR . 'pear/usr/local/lib/php/');

  // Strip off the lib portion and normalize
  $parts = explode(PATHSEP, $BASEPATH);
  array_pop($parts);
  $BASEPATH = implode('/', $parts); // normalized to UNIX
  define('BASEPATH', $BASEPATH);

  // Find basehref for self-referencing URL's
  $parts = split('/', $_SERVER['SCRIPT_NAME']);
  $self = array_pop($parts);
  $href = implode('/', $parts);
#  $BASEHREF =  'http://' . $_SERVER["SERVER_NAME"] . $href;
   $BASEHREF =  'http://' . $_SERVER["HTTP_HOST"] . $href;

  define('BASESUBDIR', "$href/");
  define('BASEHREF', $BASEHREF);

#  $page   = $_SERVER['PATH_INFO'];  
  $page = '';
  if ($page == '') $page = $_SERVER['REQUEST_URI'];
  $page   = preg_replace('@' . preg_quote(BASESUBDIR) . '@', '', $page,1);
  $page   = explode('?', $page);
  $page   = $page[0];
  if ($page == '') $page = "index.html";
  /*
   * handle /subdir/index.html
   * 
   */
   $endchar = substr($page, strlen($page) - 1, 1);
   if ($endchar == '/') {
   	$page .= "index.html";
   }
   
  define('BASEPAGE', $page);

  $parts = explode('/', $page);
  $pagename = array_pop($parts);
  $pagedir  = implode($parts, '/');
  $pageobj  = str_replace('.html', '', $page);
  $pagemethod  = str_replace('/', '_', $pageobj);

  define('BASEPAGENAME',   $pagename); // the name of the html page
  define('BASEPAGEMETHOD', $pagemethod);  // the normalized name of the page
  define('BASEPAGEDIR',    $pagedir);  // The directory the page lives in

 /* TODO - implement siteid 

    Here we determine the siteid. This is done by taking the hostname from
    the request, and then looking up the sited from the tuyu_hosts tables.

    For now, we know we are on Tuyu.

 */
 define('SITEID', 1); 
}

/**
 * Debug dump the path info
 */

function dump_path() {
# phpinfo();

print ("<pre>\n");
print ('INCLUDES_DIR: '   . INCLUDES_DIR . "\n");
print ('THEME_DIR: '      . THEME_DIR . "\n");
print ('CACHE: '          . CACHE . "\n");
print ('TEMPLATEDIR: '    . TEMPLATEDIR . "\n");
print ('COMPILEDIR: '     . COMPILEDIR . "\n");
print ('EVENTDIR: '       . EVENTDIR . "\n");
print ('SABRAYLADIR: '    . SABRAYLADIR . "\n");
print ('PEARDIR: '        . PEARDIR . "\n");
print ('BASEPATH: '       . BASEPATH . "\n");
print ('BASESUBDIR: '     . BASESUBDIR . "\n");
print ('BASEHREF: '       . BASEHREF . "\n");
print ('BASEPAGE: '       . BASEPAGE . "\n");
print ('BASEPAGENAME: '   . BASEPAGENAME . "\n");
print ('BASEPAGEMETHOD: ' . BASEPAGEMETHOD . "\n");
print ('BASEPAGEDIR: '    . BASEPAGEDIR . "\n");
print ('SITEID:'          . SITEID      . "\n");

if (! empty($_SERVER['HTTP_REFERER'])) print ('REFERER:'         . $_SERVER['HTTP_REFERER']);
//global $db;
//print_r($db);
print ("</pre>\n");
# phpinfo();
}



function stripslashes_deep($value) {
        $value = is_array($value) ? array_map("stripslashes_deep", $value) : stripslashes($value);
        return $value;
}

 
