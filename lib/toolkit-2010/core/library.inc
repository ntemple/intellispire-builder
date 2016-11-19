<?php

function  openconn()
{
  static $_connection;
  global $connection;

  if (defined('DB_SKIP_CONNECT')) return NULL;
  if (! isSet($_connection) ) {
    if (PCONNECT) {
      $_connection = @mysql_pconnect($GLOBALS["db_host"],$GLOBALS["db_login"],$GLOBALS["db_password"]) 
                   or die("Couldn't connect to Database user.");
    } else {
      $_connection = @mysql_connect($GLOBALS["db_host"],$GLOBALS["db_login"],$GLOBALS["db_password"])
                   or die("Couldn't connect to Database user.");
    }
    $db = @mysql_select_db($GLOBALS["db_name"], $_connection) or die("Couldn't select database due to : " . mysql_error());
    $connection = $_connection;
  }
  return $_connection;
}

// result-set mysql_qw($connection_id, $query, $arg1, $arg2, ...)
//  - or -
// result-set mysql_qw($query, $arg1, $arg2, ...)
//  mysql_qw("SELECT * FROM t WHERE name=?", $name)

function mysql_qw() {
  $args = func_get_args();
  $conn = null;
 
  if (is_resource($args[0])) $conn = array_shift($args);

  if (count($args) == 1) {
    // only a query, no substition expected nor required
    $query = $args[0];
  } else {
    // We need to substitute
    if (is_array($args[1])) {
      // The last argument is an array of replacement values
      $template = array_shift($args);  // get the template
      $args = array_shift($args);      // get the actual replacement values
      array_unshift($args, $template); // put the template on top
    }
    $query = call_user_func_array("mysql_make_qw", $args);
  }
  
# error_log($query);

  $rs =  $conn!==null? mysql_query($query, $conn) : mysql_query($query);
  if(!$rs) {
    if (defined('DB_SKIP_ERR')) return NULL;
    else { 
        error_log(mysql_error() . "\n$query\n");
        die("Fatal Error: " . mysql_error() . "\n" . $query . "\n");
    }
  }
  return $rs;
}

function mysql_fetch_results($rs) {

  $data = array();
  while($row = mysql_fetch_assoc($rs) ) {
    $data[] = $row;
  }
  return $data;
}

function mysql_fetch_value($rs) {
  if (mysql_num_rows($rs) != 1) return NULL;
  $row = mysql_fetch_row($rs);
  return $row[0];
}

function mysql_fetch_column($rs) {
  $data = array();
  while($row = mysql_fetch_row($rs) ) {
    $data[] = $row[0];
  }
  return $data;
}


// string mysql_make_qw($query, $arg1, $arg2, ...)
function mysql_make_qw() {
  $args = func_get_args();
  $tmpl =& $args[0];
  $tmpl = str_replace("%", "%%", $tmpl);
  $tmpl = str_replace("?", "%s", $tmpl);
  foreach ($args as $i=>$v) {
   if (!$i) continue;
   if (is_int($v)) continue;
   $args[$i] = "'".mysql_escape_string($v)."'";
  }
  for ($i=$c=count($args)-1; $i<$c+20; $i++)
   $args[$i+1] = "UNKNOWN_PLACEHOLDER_$i";
  return call_user_func_array("sprintf", $args);
}

/* Now a useful function for converting MySQL datetime to a UNIX timestamp
   which can then be used with the
   XMLRPC_convert_timestamp_to_iso8601($timestamp) function.
   This is not a method!
   It comes from: http://www.zend.com/codex.php?id=176&single=1 */

function mysql_datetime_to_timestamp($dt) {
    $yr=strval(substr($dt,0,4));
    $mo=strval(substr($dt,5,2));
    $da=strval(substr($dt,8,2));
    $hr=strval(substr($dt,11,2));
    $mi=strval(substr($dt,14,2));
    $se=strval(substr($dt,17,2));
    return mktime($hr,$mi,$se,$mo,$da,$yr);
}

function pchop(&$string) 
{ 
if (is_array($string)) 
{ 
foreach($string as $i => $val) 
{ 
$endchar = chomp($string[$i]); 
} 
} else { 
$endchar = substr("$string", strlen("$string") - 1, 1); 
$string = substr("$string", 0, -1); 
} 
return $endchar; 
} 

function normalize_address($address) {
        if (strpos($address, 'bp:') === false) return $address; 

        $bpbox = explode(':', $address);
        array_pop($bpbox);
        $bpbox = implode(':', $bpbox);
        return $bpbox . ':%';
}

function dprint($var, $name="") {
   if (DEBUG === false) return;
   if ($name) print $name . ":";
   print $var . "<br>\n";

}

function dprint_r($var, $name="") {
   if (DEBUG === false) return;
   if ($name) print "\n<br><b>$name</b><br>";
   print "\n<br>\n<hr><pre>\n";
   print_r($var);
   print ("\n</pre><hr>\n");
}


?>
