<?php
/**
* @version $Id: servlet.inc.php 21 2013-03-15 19:35:01Z ntemple $
* @package Saybrayla_1_0
* @copyright (C) 2004-2005 ByPass Networks
* @license http://www.bypassnetworks.com/euala.html
*/



/** ensure this file is being included by a parent file */
defined( '_SB_VALID_' ) or die( 'Direct Access to this location is not allowed.' );
require_once('default.class.php');

function ClassFactory($class, &$response, $defaultClass = DEFAULTCLASS) {
  sabrayla_trace("+ClassFactory: $class $response $defaultClass");
  // Save class access for later
  $response->m = $class;

  if ($class == '') $class = 'default';
  if ($class == 'default') {
    sabrayla_log('-ClassFactory: ' . $defaultClass);
    return new $defaultClass;
  }

  # Find file for class. Right now, we search only in modules subdirectory
  $class = strtolower($class);
  $classList = split("_", $class);
  $path = "modules/" . $classList[0] . "/" . $class . ".class.php";

  sabrayla_log("Path: $path");

  # HERE IS WHERE WE CHANGE THE UI
  # TODAY, it grabs from he templates directory
  # Probably should add an /xml directory, too.
  $TEMPLATEDIR = "templates";

  $response->AddTemplatePath(INCLUDES_DIR . "/modules/" . $classList[0]);
  $response->AddTemplatePath(INCLUDES_DIR . "/modules/" . $classList[0] . "/$TEMPLATEDIR");

  $className = $class . 'Class';

  sabrayla_log("className: $class");
  sabrayla_log(INCLUDES_DIR . $path);

  if (file_exists(INCLUDES_DIR . $path)) {
    sabrayla_log("Loading Class: $class");

    if (! class_exists($className)) require_once($path);
    $instance = new $className;
  } else {
    if (! AUTOBUILD === true) return NULL;  // Don't autobuild unless requested

    sabrayla_log("Autogenerating $className extends $defaultClass ...");
    $text = <<<EOD
/*
EVOLUTONCOPYRIGHT
\$Id \$
*/
class $className extends $defaultClass {
  var \$_class    = '$class';
  var \$_template = '$class.html';
  var \$_table    = '$class';
  var \$_pkey     = 'id';
}
EOD;

    sabrayla_debug($text);
    sabrayla_log("Creating: " . INCLUDES_DIR . $path);
    $out = @fopen(INCLUDES_DIR . $path, "a+w");
    if ($out) {
      sabrayla_log("Writing: " . INCLUDES_DIR . $path);
      fwrite($out, "<?php\n$text\n?>");
      fclose($out);
      require_once($path);
    }

    if (! class_exists($className)) eval ($text);
    $instance = new $className;
    sabrayla_log($instance);
  }
  $instance->_class = $class;

  sabrayla_trace("-ClassFactory: $instance");
  return $instance;
}

# These should probably be factories


class Request {

  var $req;

  function request() {
    $this->req = $_REQUEST;
  }

  function persist(&$response) {
     $response->setdata($_POST);
  }

  function classForPage($page) {
    return DEFAULTCLASS;
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

  function newContext() {
        return clone($this);
  }

  function marshal() {
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
}

?>
