<?php
/**
* @version $Id: servlet.php 21 2013-03-15 19:35:01Z ntemple $
* @package Saybrayla_1_0
* @copyright (C) 2004-2005 ByPass Networks
* @license http://www.bypassnetworks.com/euala.html
*/

/** ensure this file is being included by a parent file */
defined( '_SB_VALID_' ) or die( 'Direct Access to this location is not allowed.' );

function ClassFactory($class, &$response) {
  if ($class == '') $class = 'default';

  # Find file for class. Right now, we search only in modules subdirectory
  $class = strtolower($class);
  $classList = split("_", $class);
  $path = "modules/" . $classList[0] . "/" . $class . ".class.php";

  # HERE IS WHERE WE CHANGE THE UI
  # TODAY, it grabs from he templates directory
  # Probably should add an /xml directory, too.
  $TEMPLATEDIR = "templates";

  $response->AddTemplatePath(BASEPATH . "/modules/" . $classList[0] . "/$TEMPLATEDIR");
  if ($class != 'default') require_once($path);

  $class = $class . 'Class';
  return new $class; 
}

class defaultClass {

  var $_template = 'default';

  function doView(&$request, &$response) {
     # print "defaultClass doView\n";
     # $template = $_REQUEST['p'];
     # if ($template == '') $template = 'default';
     # $this->template = $template . ".html";
     # $response->template = $this->template;
  }

  # Return an array containing top-level submenu items
  function getMenu() {
    return NULL;
  }

  function getHead() {
     return '';
  }

  function doStore(&$request, &$response) {
    # print "defaultClass doStore\n";   

    if ($_POST['error'] == '1') {
      $response->errors['error'] = 'An error occured.';
      $response->_template= 'test.html';
      return false;
    }  

    return true;  # success
  }

  function libMethod() {
    return "inside libMethod\n";
  }
}

# These should proably be factories
class Request {
  var $_profile;
  
  function username()  {
     return $_SERVER['REMOTE_USER'];
  }

  function profile() {
    if ($this->_profile != '') return $this->_profile;
//    $rs = sql_query_ro('select * from auto_settings LIMIT 1');
//    $this->_profile = sql_fetch_assoc($rs);
    return $this->_profile;
  }

  function persist(&$response) {
     $response->setdata($_POST);
  }

}


class Response extends Context {

  var $_head;

  function includeHeaderFile($file) {
   $this->_head .= $this->getOutput($file);
  }

  function addHeader($text) {
    $this->_head .= "\n$text\n";
  }
}

?>
