<?php

class isDirectoryIterator {

  var $base;

  function __construct($dir) {
    $this->base = $dir;
  }

  function walk($recursive = false) {
    return $this->process_dir($this->base, $recursive);
  }


  function process_dir($dir,$recursive = FALSE) {
    if (is_dir($dir)) {
      for ($list = array(),$handle = opendir($dir); (FALSE !== ($file = readdir($handle)));) {
        if (($file != '.' && $file != '..') && (file_exists($path = $dir.'/'.$file))) {
          if (is_dir($path) && ($recursive)) {
            $list = array_merge($list, $this->process_dir($path, TRUE));            
          } 
          
          // Process the file
          $reldir = substr($dir, strlen($this->base)+1);                   

          if (is_dir($path)) {
            $entry = array('dirpath' => $dir . '/' . $file, 'reldir' => $reldir . '/' . $file);
            $entry = $this->handle_path($entry);  
            $this->handle_dir($entry);
          } else {
            $entry = array('filename' => $file, 'dirpath' => $dir, 'reldir' => $reldir);
            $entry = $this->handle_path($entry);
            $this->handle_file($entry);
          }

          $list[] = $entry;          
        }
      }
      closedir($handle);
      return $list;
    } else return FALSE;
  }

  function handle_path(&$entry) { 
    return $entry;
  }

  function handle_file($entry) {
    print_r($entry);
  }

  function handle_dir($entry) {
    print_r($entry);
  }

}


