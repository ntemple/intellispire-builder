<?php

require_once('include/Smarty2/Smarty.class.php');
require_once('lib/mysql.class.php');
require_once('lib/isDirectoryIterator.class.php');

/**
* Runs through every file in the template directory per call.
* One model per group of data.
* 
* Templates and directory filename should use UPPERCASE variables:
* example:
*    templates/views/_MODEL_/tmpl
* 
* otherwise, all variables should be lowercase.
* 
* Variables in the models are directly copied to the context.
* 
* Context is (currently) a smarty implementation.
*/

class templateRunner extends isDirectoryIterator {

  var $out;
  var $ctx;

  function __construct($ctx, $dir = 'templates', $out = 'out') {
    $this->out = $out;
    $this->ctx = $ctx;
    parent::__construct($dir);
  }  
  
  function interpolate_path($out) {
    
    foreach ((array) $this->ctx as $n => $v) {
      if (is_string($n) && is_string('v') &&($n[0] != '_')) {
        $name = strtoupper('_' . $n . '_');
        $out = str_replace($name, $v, $out);
      }
    }
    $out = str_replace('//', '/', $out);
    return $out;    
  }

  function handle_path(&$entry) {
    $entry['outdir'] = $this->interpolate_path($this->out . '/' . $entry['reldir']);
    return $entry;
  }                                

  function handle_dir($entry) {
    return $entry;                           
  }  

  function handle_file($entry) {
    print "=FILE=\n";
    print_r($entry);

    $content = $this->ctx->getOutput($entry['reldir'] . '/' . $entry['filename']);
    
    @mkdir($entry['outdir'], 0755, true);    
    $filename = $this->interpolate_path($entry['filename']);
    file_put_contents($entry['outdir'] . '/' . $filename, $content);
    print "$filename\n";

    return $entry;
  }
}

class SmartyContext extends Smarty {
  
  function __construct($tpldir) {

    parent::__construct();

    $this->template_dir = $tpldir;
    $this->compile_dir  = $tpldir . "_c";
    $this->config_dir   = $tpldir . "_cfg";
    $this->cache_dir    = $tpldir . "_cache";

    @mkdir($this->compile_dir, 0755, true);
    @mkdir($this->config_dir, 0755, true);
    @mkdir($this->cache_dir, 0755, true);


    $this->caching = false;
    $this->assign('app_name', 'Builder');
  }

  function getOutput($template) {
    return $this->fetch($template);
  }

  function __set($n, $v)  {
    $this->$n = $v;
    $this->assign($n, $v);
  }
  
  function reflect($db, $table) {
    $fields = $db->columns($table);
  
    // Normalize ID
    array_shift($fields);
    array_unshift($fields, 'id');
    
    $this->fields = $fields;
    
    return $fields;
  }

}


