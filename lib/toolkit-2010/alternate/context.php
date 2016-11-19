<?php

require_once 'HTML/Template/Flexy.php';
require_once 'HTML/Template/Flexy/Element.php';

class Context {

  var $_elements;
  var $_opt;
  var $_template;

  function Context($tpl, $cache = CACHE) {
     $this->_elements = array();
     $this->_opt = array(
      'templateDir'   => $tpl,
      'compileDir'    => $cache,
      'forceCompile'  => 0,
      'debug'         => 0,
      'locale'        => 'en',
      'compiler'      => 'Standard',
    );

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


  function compile($tplfile = '', $getelements = 1) {
       if ($tplfile == '') $tplfile = $this->_template;
       if ($tplfile == '') $tplfile = 'default.html';
# print($tplfile);
       $template = new FlexyEx($this->_opt);
       if (file_exists($this->_opt['templateDir'])) {
         $template->nocompile($tplfile);
       } else {
         $template->compile($tplfile);
       }

       if ($getelements) {
         $this->_elements = $tempalte->getElements();
       }
       $this->$_template = $template;

   }


  function output($tplfile = '') {
       $this->compile($tplfile, 0);
       $this->$_template->outputObject($this, $this->_elements);
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
 # print $out; 
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

  # We need a method to handle unkown methods
  function ucwords($s) {
     return ucwords($s);
  }

}

class FlexyEx extends HTML_Template_Flexy {

    function FlexyEx($_opts) {
       HTML_Template_Flexy::HTML_Template_Flexy($_opts);
    }

    /**
    *   compile the template
    *
    *   @access     public
    *   @version    01/12/03
    *   @author     Wolfram Kriesing <wolfram@kriesing.de>
    *   @param      string  $file   relative to the 'templateDir' which you set when calling the constructor
    *   @return     boolean true on success. (or string, if compileToString) PEAR_Error on failure..
    */
    function nocompile( $file )
    {

        return HTML_Template_Flexy::compile($file); // COMMENT WHEN DONE

        if (!$file) {
            return $this->raiseError('HTML_Template_Flexy::compile no file selected',
                HTML_TEMPLATE_FLEXY_ERROR_INVALIDARGS,HTML_TEMPLATE_FLEXY_ERROR_DIE);
        }

        $compileDest = @$this->options['compileDir'];


        $compileSuffix = ((count($this->options['templateDir']) > 1) && $this->options['multiSource']) ?
            DIRECTORY_SEPARATOR  .basename($tmplDirUsed) . '_' .md5($tmplDirUsed) : '';

        $this->compiledTemplate    = $compileDest . $compileSuffix . DIRECTORY_SEPARATOR .$file.'.'.$this->options['locale'].'.php';
        $this->getTextStringsFile  = $compileDest . $compileSuffix . DIRECTORY_SEPARATOR .$file.'.gettext.serial';
        $this->elementsFile        = $compileDest . $compileSuffix . DIRECTORY_SEPARATOR .$file.'.elements.serial';

        return true;
        
    }

}

?>
