<?php
/**
 * Sabrayla PHP Classes and Functions
 *
 * @category   sabrayla
 * @package    sabrayla
 * @author     Nick Temple <Nick.Temple@intellispire.com>
 * @copyright  2002-2006 Intellispire
 * @license    http://www.sabrayla.com/license/1_0.txt Sabrayla License 1.0
 * @version    SVN: $Id: sabrayla.class.php 21 2013-03-15 19:35:01Z ntemple $
 * @since      File available since Release 0.1
 */

/** ensure this file is being included by a parent file */
defined( '_SB_VALID_' ) or die( 'Direct Access to this location is not allowed.' );

## Setup logging here ##
if (defined('LOG')) {
  require_once('Log.php');
  $_log_trace = array('UNDERFLOW', 'SABRAYLA-U');
  $_log = &Log::singleton('file', INCLUDES_DIR . '/out.log', 'SABRAYLA');
  $GLOBALS['log'] = $_log;
}


/**
 * Generic Object. Does this need it's own file,
 * Or can it be in sabrayla.php?
 */

class object {
   function trace($level, $msg) { print_r($msg); }
   function classloader($class) {
          require_once("lib/$class.class.php");
          return $class;
   }
   function toString() { return serialize($this); }
}


class Sabrayla extends Object {

  var $_events = array();

  /**
   *  Standard event System
   *  List of Events
   *  event_name     group  object         description
   * ===================================================
   * init           system NULL           after framework is loaded and database connected,
   *                                      before application entered
   * sqlerror       system mysql_database triggered whenever a SQL error is encountered
   *
   *
   * @param event    name of the event to trigger
   * @param object   params
   * @param group    The group (or subdir) to load.  Defaults to system
   */

  function triggerEvent( $event, $object = NULL, $group = 'system') {
     $this->trace("triggerEvent( $event, $group) ");

     $events = $this->_events[$event];
     if (! $events) return;

     // TODO: keep track of the groups we've loaded, and
     // don't load new ones
     $this->loadEventHandlers($group);

     foreach( $events as $function) {
       if (function_exists($function)) {
           $this->trace("Handler: $event $group $function");
           $function($event, $object);
       }
     }
  }

  /**
   * Load event handlers from the specified subdirectory.
   *
   * @param group subdir to load
   */

  function loadEventHandlers($group = 'system') {
     global $sabrayla;

     $dir = opendir(EVENTDIR . $group);
     while (false !== ($file = readdir($dir)))
         if (strpos($file, '.eh.php') > 0) {
             $this->trace("Load EH: $group $file");
             require_once(EVENTDIR . $group . '/' . $file);
         }
     closedir($dir);
  }

  /**
   * Register an event function. Used in event handlers.
   */

  function registerEventHandler( $event, $function ) {
         $this->_events[$event][] = $function;
  }

  /* Logging System */
  function trace($message, $level=PEAR_LOG_INFO) {
  	$data = debug_backtrace();
    array_shift($data);
    $place = array_shift($data);
    if ($place) {
      $file     = $place['file'];
      $function = $place['function'];
      $line     = $place['line'];
      $args     = serialize($place['args']);
      $message = "$function [$line]: $message";
      if (DEBUG > 200) {
      	$message = "$file - $message";
      }
    }
    $this->writelog($message, $level);
  }

  function debug($message, $level=PEAR_LOG_DEBUG) {
    $this->writelog($message, $level);
  }

  function log($message, $level=PEAR_LOG_DEBUG) {
    $this->writelog($message, $level);
  }


  function backtrace($message, $level=PEAR_LOG_DEBUG) {
     $data = debug_backtrace();
     array_shift($data);
     $message .= "\n";
     $message .= sabrayla_sprint_r($data);
     if (!defined('LOG')) return;
     $messsage = sabrayla_sprint_r($message);
     $GLOBALS['log']->log($message, $level);
  }

  function writelog($message, $level) {
    if (!defined('LOG')) return;
    $messsage = sabrayla_sprint_r($message);
    $GLOBALS['log']->log($message, $level);
  }


  /* Authentication */
  /* This authentication system is as basic as you can get.
     Notes: We CANNOT use php SESSIONS. This is because session data
     is sometimes leaked, and, most importantly it is NOT scaleable
     (See: O'reilly's "Building Scalable Web Sites" by Cal Henderson)_

     Simply overide in the applications descendent class.

     This is for demo purposes and needs to be made much more robust

   */

   function _cookiename() {
     return md5(RPC_KEY . ADMIN_USER . ADMIN_PWD . BASEPATH);
   }

   function authenticate() {
     $cookie = $this->_cookiename;

     if ($_COOKIE[$cookie] == ADMIN_USER) return true;

     return $this->login($_REQUEST['admin_user'], $_REQUEST['admin_pwd']);

   }


   function login($admin_user, $admin_pwd) {
      $cookie = $this->_cookiename;

     if ( ($admin_user == ADMIN_USER) &&
          ( $admin_pwd  == ADMIN_PWD) ) {
       setcookie($cookie, ADMIN_USER);
       $_COOKIE[$cookie] = ADMIN_USER;
       return true;
     } else {
       return false;
     }
  }

  function logout() {
     $cookie = $this->_cookiename;
     $_COOKIE[$cookie] = '';
     setcookie($cookie, '', -1);
  }


}

?>
