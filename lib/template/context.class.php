<?php
/**
*
* @copyright  Copyright (C) 2012 Intellispire, LLC. All rights reserved.
* @license    GNU/GPL v2.0, see LICENSE.txt
*
* Marketing Website Generator is free software.
* This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class Context {

    var $_opt;

    function __construct($path = null, $cache = null) {
        $this->_opt['template_path'] = $path;
        $this->_opt['cache'] = $cache;
    }

    protected function _render($text, $loader) {
        $twig = new Twig_Environment($loader, $this->_opt);
        $twig->addGlobal('ctx', $this);
        return $twig->render($text, (array) $this);
    }

    function render($text, $loader = null) {
        if (!$loader) {
            $loader = new Twig_Loader_Filesystem($this->_opt['template_path']);
        }
        return $this->_render($text, $loader);
    }

    function render_string($string) {
        return $this->render($string, new Twig_Loader_String());
    }

    function setdata($array) {
        foreach ($array as $n => $v) {
            $this->$n = $v;
            $this->element($n, $v);
        }
    }

    function lipsum($size) {
        $out = '';
        for($i = 0; $i < $size; $i++) {
            $out .= 'lorum ipsum';
        }
        return $out;
    }

    function set($name, $value) {
        $this->$name = $value;
        $this->element($name, $value);
    }

    function debug($msg) {
        if ($this->_opt['debug']) {
            print ": $msg\n";
        }
    }

}


class Flexy_Context {

  var $_elements;
  var $_opt;

  function Context($tpl, $cache) {
     $this->_elements = array();
     $this->_opt = array(
      'templateDir'   => $tpl,
      'compileDir'    => $cache,
      'forceCompile'  => 1,
      'debug'         => 0,
      'locale'        => 'en',
      'compiler'      => 'Regex',
    );

  }

  function debug($msg) {
    if ($this->_opt['debug']) {
      print ": $msg\n";
    }
  }

  function AddTemplatePath($path) {
     array_unshift($this->_opt['templateDir'], $path);
  }

  function inc($var) {
    if (isSet($this->$var)) $this->$var++;
    else $this->$var = 1;
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
    if (! isSet($this->$var) ) $this->$varname = 0;
    else $this->$var ++;
    return $this->$var % 2;
  }

  function output($tplfile = '') {
       if ($tplfile == '') $tplfile = $this->_template;
       if ($tplfile == '') $tplfile = 'default.html';

       $this->debug($tplfile);
       $template = new FlexyEx($this->_opt);
       if (file_exists($this->_opt['templateDir'])) {
         $template->nocompile($tplfile);
       } else {
         $template->compile($tplfile);
       }
       $template->outputObject($this, $this->_elements);
   }

   function getOutput($tplfile = '') {
      # ob_start();
      $this->output($tplfile);
      $content = ob_get_contents();
      # ob_end_clean();
      return $content;
   }

   function writeOutput($dir, $file, $tplfile = '') {
      $out = $this->getOutput($tplfile);

      $this->debug($out);

      if (! file_exists($dir) ) {
        # PHP 5 mkdir("$dir", 0777, true);
        mkdir("$dir", 0777);
      }
      $handle = fopen("$dir/$file", "w+");
      fwrite($handle, $out);
      fclose($handle);
   }

   # TODO: ignore all values with a leading underscore
   function setdata($array) {
      foreach ($array as $n => $v) {
         $this->$n = $v;
         $this->element($n, $v);
       }
   }

   function persist($name) {
    if (is_array($name))  {
      foreach ($name as $n) $this->element($n, $this->$n);
    } else {
      $this->element($name, $this->$name);
    }
  }

  function set($name, $value) {
      $this->$name = $value;
      $this->element($name, $value);
  }

  function setSelect($name, $options) {
    $this->element($name);
    $this->_elements[$name]->setOptions($options);
  }

  function& element($name, $value = '') {
      if (!isset($this->_elements[$name]))
           $this->_elements[$name]  = new HTML_Template_Flexy_Element;
      if ($value != '') $this->_elements[$name]->setValue($value);
      return $this->_elements[$name];
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

     $context = clone($this); # should be deep copy, check in PHP5

     # add a pointer this context
     array_unshift($args, $context);
     return call_user_func_array(array(&$object, $method), $args);
  }

  function is($v1, $v2) {
    return ($v1 == $v2);
  }

}

