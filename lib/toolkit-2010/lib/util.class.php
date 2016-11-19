<?php

define ('SB_LOG_INFO', 6); // same as PEARL_LOG_INFO, but defined.
define ('SB_FULL_TRACE', -1); // full dump of the paramaters

class util {

  static $LOG = false;
  static $DEBUG = 0;


  static function pchop(&$string) 
  {
    if (is_array($string)) 
    {
      foreach($string as $i => $val)
      {
        $endchar = pchop($string[$i]);
      }
    } else {
      $endchar = substr("$string", strlen("$string") - 1, 1);
      $string = substr("$string", 0, -1);
    }
    return $endchar;
  }

  static function sprint_r($mixed) {
    ob_start();
    print_r($mixed);
    return ob_get_clean();
  }

  /* Logging System */
  static function trace($message = '', $level=SB_LOG_INFO) {

    $out = '';

    $data  = debug_backtrace();
    $here  = array_shift($data);
    $place = array_shift($data);
  
    # util::writelog($place);
    # util::writelog($here);

    $file     = $place['file'];
    $function = $place['function'];
    $line     = $here['line'];
    $class    = $place['class'];
    $args     = serialize($place['args']);

#    if (util::$DEBUG > 200) 
          $out .= $file . " - ";
    if (isset($class)) $out .= $class."::";
    $out .= "$function [$line]";
    if ($message) $out .= ": $message";

    util::writelog($out, '', $level);
    if ($level = SB_FULL_TRACE) {
      util::writelog($place);
    }
  }


  
  static function debug($message, $level=SB_LOG_INFO) {
    util::writelog($message, '', $level);
  }


  static function backtrace($message = '', $level=SB_LOG_INFO) {
     $data = debug_backtrace();
     array_shift($data);
     $message .= "\n";
     $message .= util::sprint_r($data);
     util::writelog($message, '', $level);
  }

  static function writelog($message, $label = '', $level = SB_LOG_INFO) {
    if (util::initLogging()) {
      $message = util::sprint_r($message);
      $GLOBALS['log']->log($label . $message, $level);
    }
  }

  /**
   * Setup logging
   */
  static function initLogging($filename = '/tmp/sabrayla_log') {
    if (!util::$LOG) return false;
    if (isset($GLOBALS['log'])) return true;
    ## Setup logging here ##
   
    require_once('Log.php');
    $_log_trace = array('UNDERFLOW', 'SABRAYLA-U');
    $_log = &Log::singleton('file', $filename, 'SABRAYLA');
    $GLOBALS['log'] = $_log;
    return true;

  }

}
