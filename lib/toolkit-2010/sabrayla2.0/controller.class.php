<?php
/* SVN FILE: $Id: controller.class.php 21 2013-03-15 19:35:01Z ntemple $*/
/**
 * Short description for file. 
 *
 * Long description for file
 *
 * PHP versions 5
 * 
 * Copyright 2007, Nick Temple, Intellispire
 *                 1355 Bardstown Rd. #230
 *                 Louisville, KY 40204
 *                 http://www.intellispire.com
 *                 Nick.Temple@intellispire.com
 *
 * @copyright           Copyright 2006-2007, Nick Temple, Intellispire
 * @link                http://www.intellispire.com
 * @package             
 * @subpackage          
 * @since               
 * @version             $Revision: 21 $
 * @modifiedby          $LastChangedBy: ntemple $
 * @lastmodified        $Date: 2013-03-15 15:35:01 -0400 (Fri, 15 Mar 2013) $
 * @license             
 */

# ===
/**
 * Throw a "true" 404 error, and put out cool content
 * TODO: do a search for keywords, etc and see if we
 * can't do a redirect to an appropriate page instead.
 * 
 * TODO: We can add any routing redirects (moved pages)
 * here.
 */

function throw404($tpl = 'lib/pages/error404.html') {
  global $ctx;
  header("http/1.0 404 Not Found");  
  $body = $ctx->getOutput($tpl);
  if ($body) {
  	print $body;
  	exit;
  }
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<HTML><HEAD>
<TITLE>404 Not Found</TITLE>
</HEAD><BODY>
<H1>Not Found</H1>
The requested URL was not found on this server.<P>
</BODY></HTML>
<?php    
exit;	
}

/**
 * Connect to the database
 */

function connect() {
    static $db;
    if ($db) return $db;
	
    $db = new mysql_database;
    $db->prefix = ''; // DB_PREFIX;
    $db->connect(DB_RW_HOST, DB_RW_DATABASE, DB_RW_LOGIN, DB_RW_PASSWORD);    
    return $db;
}

function setdisplaytitle($s) {
	if (strlen($s) < 25) return $s;
	$s = substr($s, 0, 20);
	$s .= " ...";
	return $s;
}

// Used by legal template
function getConfig($name, $default = "") {
	return "GETCONFIG";
}

function tpl_getcontactinfo() {
    global $ctx;
    $ctx->address   = '1355 Bardstown Rd. #230';
    $ctx->city      = 'Louisville';
    $ctx->state     = 'KY';
    $ctx->zip       = '40204';
    $ctx->phone     = '877-341-1796';
    $ctx->longstate = 'Kentucky';
    $ctx->county    = 'Jefferson';	
}

function normalize() {
	$db = connect();
	$results = $db->get_results('select id,title,filename from menus');
	foreach ($results as $result) {
		$fname =  normalize_phrase($result['title']);
		$db->query('update menus set filename=? where id=?', $fname, $result['id']);	
	}	
}

function normalize_phrase($kw) {
      $kw = trim(strtolower($kw));
      $kw = preg_replace('/\W+/', '-', $kw);           
      return $kw;
}

function out($page) {
  global $start_time;
  $end_time = microtime_float();
  $total = $end_time - $start_time;
  print $page;
  print "\n<!-- $total -->\n";
  exit();
}

# default controller
class controller extends sabrayla {

  function dispatch($page) {
    $this->trace($page);

    $req = $_SERVER['REQUEST_METHOD'];
    $method = strtolower($req . '_' . $page);
    if (method_exists($this, $method)) {
      $this->log("calling: $method");
      return $this->$method();
    }
   
    $method = strtolower('do_' . $page);
    if (method_exists($this, $method)) {
           $this->log("calling: $method");
          return $this->$method();
     }
    return false;
  }
}


