<?php

require_once 'HTML/Template/Flexy.php';
require_once 'HTML/Template/Flexy/Element.php';

class Context {

  var $elements;
  var $opt;

  function Context($tpl, $cache = CACHE) {
     $this->elements = array();
     $this->opt = array(
      'templateDir'   => $tpl,
      'compileDir'    => $cache,
      'forceCompile'  => 0,
      'debug'         => 0,
      'locale'        => 'en',
      'compiler'      => 'Standard',
    );

  }

  function AddTemplatePath($path) {
     array_unshift($this->opt['templateDir'], $path);
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
       if ($tplfile == '') $tplfile = $this->template;
       if ($tplfile == '') $tplfile = 'default.html';

       $template = new FlexyEx($this->opt);
#       if (file_exists($this->opt['templateDir'])) {
#         $template->nocompile($tplfile);
#       } else {
         $template->compile($tplfile);
#       }
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
        mkdir("$dir", 0777);
      }
      $handle = fopen("$dir/$file", "w+");
      fwrite($handle, $out);
      fclose($handle);  
   }


   function setdata($array) {
      foreach ($array as $n => $v) {
         $this->$n = $v;
         $this->elements[$n] = new HTML_Template_Flexy_Element;
         $this->elements[$n]->setValue($v);
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

  function set($name, $value) {
      $this->$name = $value;
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


class FlexyEx extends HTML_Template_Flexy {

    function FlexyEx($opts) {
       HTML_Template_Flexy::HTML_Template_Flexy($opts);
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

        # return HTML_Template_Flexy::compile($file);

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
