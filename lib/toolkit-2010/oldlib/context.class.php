<?php
/**
 * Sabrayla PHP Classes and Functions
 *
 * @category   sabrayla
 * @package    sabrayla
 * @author     Nick Temple <Nick.Temple@intellispire.com>
 * @copyright  2002-2006 Intellispire
 * @license    http://www.sabrayla.com/license/1_0.txt Sabrayla License 1.0
 * @version    SVN: $Id: context.class.php 21 2013-03-15 19:35:01Z ntemple $
 * @since      File available since Release 0.1
 */

require_once 'HTML/Template/Flexy.php';
require_once 'HTML/Template/Flexy/Element.php';

class Context {

  var $elements;
  var $opt;

  // Default tags for escaping
  var $_php = "<?php";
  var $php_ = "?>";
  var $_b   = "{";
  var $b_   = "}";

  function Context($tpl = TEMPLATEDIR, $cache = COMPILEDIR, $plugins=NULL) {
     $this->elements = array();
     $this->opt = array(
      'templateDir'   => array ($tpl),
      'compileDir'    => $cache,
      'forceCompile'  => 0,
      'debug'         => 0,
      'locale'        => 'en',
      'compiler'      => 'Flexy',
      'multiSource'   => true,
      'plugins'       => $plugins,
# This opens up the system, and allows attacks. Careful!
      'globals'       => false,
      'globalfunctions' => true,
      'allowPHP'      => false,
      'privates'      => true,
    );

  }

  function AddTemplatePath($path) {
     array_unshift($this->opt['templateDir'], $path);
  }

  function inc($var) {
    if (isSet($this->$var)) $this->$var++;
    else $this->$var = 1;
  }


  # New method - does an include based on "theme"
  # This needs a lot of work to appropriately "find"
  # the template
  function flexy_include($tpl) {
    return $this->getOutput($tpl);
  }

  function formatRatio($a, $b) {
    if ($b == 0) $r = "0.0";
    else $r = $a / $b * 100;

    return "$r%";
  }

  function lookup($idx, $list) {
    if (isSet($list[$idx])) return $list[$idx];
    else return $list[''];
  }


  function choose($var) {
    if (! isSet($this->$var) ) $this->$var = 0;
    else $this->$var ++;
    return $this->$var % 2;
  }

  /**
   * Smarty compatible
   */
  function display($tplfile = '') {
     $this->output($tplfile);
  }

  function assign($name, $value) {
    $this->set($name, $value, false);
  }

  function output($tplfile = '') {
       if ($tplfile == '') $tplfile = $this->_template;
       if ($tplfile == '') $tplfile = 'default.html';

       $template = new HTML_Template_Flexy($this->opt);
       $template->compile($tplfile);
       $template->outputObject($this, $this->elements);
   }


   function getOutput($tplfile = '') {
      ob_start();
      $this->output($tplfile);
      $content = ob_get_contents();
      ob_end_clean();
      return $content;
   }

   function writeOutput($dir, $file, $tplfile = '') {
      $out = $this->getOutput($tplfile);

      if (! file_exists($dir) ) {
        # PHP 5 mkdir("$dir", 0777, true);
        mkdir($dir, 0777);
      }
      $handle = fopen("$dir/$file", "w+");
      fwrite($handle, $out);
      fclose($handle);
   }

   # Sets an array of data
   function setdata(&$array, $persist = true) {
      if (is_array($array)) foreach ($array as $n => $v) {
         $this->$n = $v;
         if ($persist) {
           $this->elements[$n] = new HTML_Template_Flexy_Element;
           $this->elements[$n]->setValue($v);
         }
       } else {
         print_r($array);
       }
   }

   function persist($name) {
    if (is_array($name))  {
      foreach ($name as $n) {
         $this->elements[$n] = new HTML_Template_Flexy_Element;
         $this->elements[$n]->setValue($this->$n);
       }
    } else {
      $this->elements[$name] = new HTML_Template_Flexy_Element;
      $this->elements[$name]->setValue($this->$name);
    }
  }

  # set a single element
  function set($name, $value, $persist = false) {
      $this->$name = $value;
      if ($persist) $this->persist($name);
  }

  function element($name, $value) {
      $this->elements[$name] = new HTML_Template_Flexy_Element;
      $this->elements[$name]->setValue($this->$name);
  }

  function setSelect($name, $options, $values = "") {
    $this->elements[$name] = new HTML_Template_Flexy_Element;
    $this->elements[$name]->setOptions($options);
    $this->elements[$name]->setValue($values);
    # TODO: persist
  }


  # module name from modules();
  # object
  # method
  # params
  function mod() {
     $args = func_get_args();
     $module = array_shift($args);

     $object = $this->modules[$module];
     if (count($args) > 0) {
       $method = array_shift($args);
     } else {
       $method = 'getcontent'; # default;
     }

     $context = $this; # should be deep copy, check in PHP5

     # add a pointer this context
     array_unshift($args, $context);
     return call_user_func_array(array(&$object, $method), $args);
  }

}

?>
