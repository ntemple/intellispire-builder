<?php

function _sql_connect($host, $login, $password, $db, $pconnect=false) {
  if ($pconnect) {
    $c = @mysql_pconnect($host,$login,$password);
  } else {
    $c = @mysql_connect($host,$login,$password);
  }
  if ($c) {
     $res = @mysql_select_db($db, $c);
  } else {
    return false;
  }
  
  if ($res) {
     return $c;
  } else {
     return false;
  }
}

function sql_connect()
{
  if (! $GLOBALS['rw_connection']) {
    $GLOBALS['rw_connection'] = _sql_connect(DB_RW_HOST, DB_RW_LOGIN, DB_RW_PASSWORD, DB_RW_DATABASE, DB_RW_PCONNECT);
    if (! $GLOBALS['rw_connection'] ) { sabrayla_die("Couldn't connect : " . mysql_error());  }
  }
  $GLOBALS['ro_connection'] = $GLOBALS['rw_connection'];
  return $GLOBALS['rw_connection'];
}

// result-set mysql_qw($connection_id, $query, $arg1, $arg2, ...)
//  - or -
// result-set mysql_qw($query, $arg1, $arg2, ...)
//  mysql_qw("SELECT * FROM t WHERE name=?", $name)

function sql_prepare() {
  $args = func_get_args();

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
    $query = call_user_func_array("_sql_make_qw", $args);
  }
  
  return $query;
}

// deprecated?
// ##########
function mysql_qw() {
# print "mysql_qw is deprecated! Please use: sql_execute() and related functions";	
  $args  = func_get_args();
  $query = call_user_func_array("sql_prepare", $args);
  return sql_query_rw($query);   
}
// ##########

function sql_execute() {
  $args  = func_get_args();
  $query = call_user_func_array("sql_prepare", $args);
  return sql_query_rw($query);   
}

function sql_get_value() {
  $args  = func_get_args();
  $query = call_user_func_array("sql_prepare", $args);
  $rs = sql_query_ro($query);   
  $row = mysql_fetch_row($rs);
  return $row[0];
}

function sql_get_select() {
  $args  = func_get_args();
  $query = call_user_func_array("sql_prepare", $args);
  $rs = sql_query_ro($query);

  $data = array();
  while($row = mysql_fetch_row($rs) ) {
    $id   = $row[0];
    $name = $row[1];
    $data[$id] = $name;
  }
  return $data;
}

function sql_get_results() {
  $args  = func_get_args();
  $query = call_user_func_array("sql_prepare", $args);
  $rs = sql_query_ro($query);   

  $data = array();
  while($row = mysql_fetch_assoc($rs) ) {
    $data[] = $row;
  }
  return $data;
}

function sql_get_row() {
  $args  = func_get_args();
  $query = call_user_func_array("sql_prepare", $args);

  if (defined('SQL_DEBUG')) print "=\n$query\n=\n";

  $rs = sql_query_ro($query);   
  $row = mysql_fetch_assoc($rs);
  return $row;
}

function sql_get_column() {
  $args  = func_get_args();
  $query = call_user_func_array("sql_prepare", $args);

  if (defined('SQL_DEBUG')) print "=\n$query\n=\n";

  $rs = sql_query_ro($query);
  return sql_fetch_column($rs);
}


function sql_query($conn, $query) {
  return _sql_query($conn, $query);
}

function sql_query_ro($query) {
  return _sql_query($query);
}


function sql_query_rw($query) {
  return _sql_query($query);
}

function _sql_query() {
  $args = func_get_args();
  $conn = null;

  if (is_resource($args[0])) $conn = array_shift($args);
  $query = array_shift($args);

  $rs =  $conn!==null? mysql_query($query, $conn) : mysql_query($query);
  if(!$rs) {
        error_log("ERROR!:" . mysql_error() . "\n$query\n");
        /* sabrayla_die("Fatal Error: " . mysql_error() . "\n" . $query . "\n"); */
  }
#  error_log($query);
  return $rs;
}

function sql_fetch_row($rs) {
  return mysql_fetch_row($rs);
}

function sql_fetch_assoc($rs) {
  return mysql_fetch_assoc($rs);
}


function sql_fetch_results($rs) {
  $data = array();
  while($row = mysql_fetch_assoc($rs) ) {
    $data[] = $row;
  }
  return $data;
}

function sql_fetch_rows($rs, $ignore="") {
  $data = array();
  while($row = mysql_fetch_row($rs) ) {
   $id = $row[0];
   if ($id != $ignore) $data[$id] = $row[1];
  }
  return $data;
}

function sql_fetch_value($rs) {
  $row = mysql_fetch_row($rs);
  return $row[0];
}

function sql_fetch_column($rs) {
  $data = array();
  while($row = mysql_fetch_row($rs) ) {
    $data[] = $row[0];
  }
  return $data;
}

// string mysql_make_qw($query, $arg1, $arg2, ...)
function _sql_make_qw() {
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

function sql_datetime_to_timestamp($dt) {
    $yr=strval(substr($dt,0,4));
    $mo=strval(substr($dt,5,2));
    $da=strval(substr($dt,8,2));
    $hr=strval(substr($dt,11,2));
    $mi=strval(substr($dt,14,2));
    $se=strval(substr($dt,17,2));
    return mktime($hr,$mi,$se,$mo,$da,$yr);
}

?>
