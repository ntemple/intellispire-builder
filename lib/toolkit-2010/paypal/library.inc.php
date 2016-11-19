<?php
// Generic libray routines go here.

ini_set('include_path', ini_get('include_path') . ":/usr/local/lib/php/core:/usr/local/lib/php/core/pear");
require_once('mysql_db.php');
require_once('sabrayla.php');

/* Handle templates */

function tpl_persist($template, &$array) {
    
  // Handle array 
  foreach($array as $name=>$value) {
    $template = str_replace('{'.$name.'}', $value, $template);
  }		
  return $template;
}

// abstract this so we can turn it off / on in 
// a centralized place
function clean_html($string) {
	return strip_tags($string);
}

// Some people are not puting in http:// on their sales pages and other URL's
// best to fix durring input into the database, but this works if applied on all 
// output

function fix_location ($location, $add_sep = false) {
  if (stristr($location, 'http') != $location) { $location = "http://" . $location; }

  $sep = '';

  if ($add_sep) {
     if (strstr($location,"?") === false) { 
        $sep = '?';
     } else {
        $sep = '&';
     }
     // Take care of case where we don't want to duplicate

     $endchar = substr($location, strlen($location) - 1, 1);
     if ($endchar == '?' or $endchar == '&') $sep = '';

  }

  

  return $location . $sep;
}


?>
