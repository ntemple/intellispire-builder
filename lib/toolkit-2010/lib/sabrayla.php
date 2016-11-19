<?php
/**
* @version $Id: sabrayla.php 21 2013-03-15 19:35:01Z ntemple $
* @package Saybrayla_1_0
* @copyright (C) 2004-2005 ByPass Networks
* @license http://www.bypassnetworks.com/euala.html
*/

/** ensure this file is being included by a parent file */
# defined( '_SB_VALID_' ) or die( 'Direct Access to this location is not allowed.' );
require_once('mysql_db.php');

## Setup logging here ##
if (defined('LOG')) {
  require_once('Log.php');
  $_log_trace = array('UNDERFLOW', 'SABRAYLA-U');
  $_log = &Log::singleton('file', 'out.log', 'SABRAYLA');
  $GLOBALS['log'] = $_log;
}

## DB Functions

function sabrayla_insert($table, $data, $pkey = 'id') {
    
    $fields = array();
    $values = array();
    $replace = array();

    foreach ($data as $name => $value) {
      if ($name != $pkey && $name != 'ts' && strpos($name, '_') !== 0) { 
        array_push($fields, "`$name`");
        array_push($values, $value);
        array_push($replace, '?');
      }
    }

    $fields = implode(',', $fields);
    $replace = implode(',', $replace);
  
    $sql = "insert into `$table` ($fields) values ($replace)"; 
    sabrayla_log($sql, PEAR_LOG_DEBUG);
    mysql_qw($sql, $values);
#    return sql_get_value('select last_insert_id()');

    return mysql_insert_id();
}


function sabrayla_update($table, $data, $pkey = 'id') {
    # id is the auto_inc field in the table, and must be present
    # ts is the timestamp in the table.  If present, and additional
    # check is performed. If the ts is out of date, nothing happens
    # the pattern protects from stale data 


    $id = $data[$pkey];
    $id = $id+0; // convert to numeric
    if ($id == 0) return sabrayla_insert($table, $data, $pkey);

    $values = array();

    $sql = "update `$table` set ";
    foreach ($data as $name => $value) {
      if ($name != $pkey && name != 'ts') {
        $sql .= "`$name`=?,";
        array_push($values, $value);
      }
    }
    pchop($sql);  # remove last comma

    $sql .= " where $pkey=?";
    array_push($values, $id);

    if (! empty($data['ts']) ) {
      $sql .= " and ts=?";
      array_push($values, $data['ts']);
    }

    sabrayla_log($sql, PEAR_LOG_DEBUG);
    mysql_qw($sql, $values);
    return $id;
}

# Convenience function to store data in a table,
# usually from post data

function sabrayla_store($table, $data, $pkey = 'id') {
  # We assume the data is for this table, only
  # we strip out all "hidden" params

  $params = array();
  foreach ($data as $name=>$value) {
   if (is_array($value) ) 
      $value = implode(",", $value);

    if( (strpos($name, '_') !== 0) && ($name != 'ts') )
        $params[$name] = $value;

  }

  return sabrayla_update($table, $params, $pkey);
}

function sabrayla_execute_sql($sql) {
  
  $sql_array = explode(';', $sql);
  foreach ($sql_array as $sql) {
    @mysql_query($sql);
  }

  return true;
}


## File Functions
function chmod_r($path, $filemode, $dirmode = 0777) {
   if (!is_dir($path))
       return chmod($path, $filemode);

   $dh = opendir($path);
   while ($file = readdir($dh)) {
       if($file != '.' && $file != '..') {
           $fullpath = $path.'/'.$file;
           if(!is_dir($fullpath)) {
             if (!chmod($fullpath, $filemode))
                 return FALSE;
           } else {
             if (!chmod_r($fullpath, $filemode))
                 return FALSE;
           }
       }
   }

   closedir($dh);

   if(chmod($path, $dirmode))
     return TRUE;
   else
     return FALSE;
}

function chmod_RX($path, $filemode) {
   if (!is_dir($path))
       return chmod($path, $filemode);

   $dh = opendir($path);
   while ($file = readdir($dh)) {
       if($file != '.' && $file != '..') {
           $fullpath = $path.'/'.$file;
           if(!is_dir($fullpath)) {
             if (!chmod($fullpath, $filemode))
                 return FALSE;
           } else {
             if (!chmod_R($fullpath, $filemode))
                 return FALSE;
           }
       }
   }
 
   closedir($dh);
  
   if(chmod($path, $filemode))
     return TRUE;
   else
     return FALSE;
}

function recurse_chown_chgrp($mypath, $uid, $gid)
{
   $d = opendir ($mypath) ;
   while(($file = readdir($d)) !== false) {
       if ($file != "." && $file != "..") {

           $typepath = $mypath . "/" . $file ;

           //print $typepath. " : " . filetype ($typepath). "<BR>" ;
           if (filetype ($typepath) == 'dir') {
               recurse_chown_chgrp ($typepath, $uid, $gid);
           }

           chown($typepath, $uid);
           chgrp($typepath, $gid);

       }
   }

}

function _sabrayla_getXML($url) {
  sleep(1);
  $data = @implode("",file($url));

  $parser = xml_parser_create();
  xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
  xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
  xml_parse_into_struct($parser,$data,$d_ar,$i_ar);
  xml_parser_free($parser);

#  $v['url']   = $url;
#  $v['xml']   = $data;
  $v['index'] = $i_ar;
  $v['data']  = $d_ar;

  return $v;

}

function sabrayla_getXML($url, $cache_time=600, $cache_dir = 'temp') {
            $cache_file = $cache_dir . '/sabraylaXML_' . md5($url);
            $timedif = @(time() - filemtime($cache_file));
            if ($timedif < $cache_time) {
                $result = unserialize(join('', file($cache_file)));
                if ($result) $result['cached'] = 1;
            } else {
                $result = _sabrayla_getXML($url);
                if ($result) {
                  $serialized = serialize($result);
                  if ($f = @fopen($cache_file, 'w')) {
                    fwrite ($f, $serialized, strlen($serialized));
                    fclose($f);
                  }
                  $result['cached'] = 0;
                }
            }
        
        // return result
        return $result;

}



# recurse_chown_chgrp ("uploads", "unsider", "unsider") ; 
 
## Logging functions

function sabrayla_log_setup($ident) {
  if (defined ('LOG')) {
    global $_log_trace;

    array_push($_log_trace, $GLOBALS['log']->getIdent());
    $GLOBALS['log']->setIdent($ident);
  }
}

function sabrayla_log_teardown() {
  if (defined('LOG')) {
    global $_log_trace; 
    $GLOBALS['log']->setIdent(array_pop($_log_trace));
  }
}

function sabrayla_log($message, $level=PEAR_LOG_DEBUG) {
   # error_log($message);
  if (defined('LOG')) { $GLOBALS['log']->log($message, $level); }
}

function sabrayla_trace($message) {
	$data = debug_backtrace();
	array_shift($data);
	print "<pre>\n";
	print_r($data);
	print "</pre>\n";
}

function sabrayla_die($message) {
	$data = debug_backtrace();
	array_shift($data);
	print "<pre>\n";
	print_r($data);	
	print "</pre>\n";
	die($message);
}



function sabrayla_errorHandler($error)
{
    global $logger;

    $message = $error->getMessage();

    if (!empty($error->backtrace[1]['file'])) {
        $message .= ' (' . $error->backtrace[1]['file'];
        if (!empty($error->backtrace[1]['line'])) {
            $message .= ' at line ' . $error->backtrace[1]['line'];
        }
        $message .= ')';
    }

    $GLOBALS['log']->log($message, $error->code);
}

function sabrayla_keypos($text, $kw, $len) {

         $pos = strpos($text, $kw);
         $start_pos = $pos - $len / 4;
         if ($start_pos < 1) { 
             $start_pos = 0;
         } else {
           $start_pos = strpos($text, ' ', $start_pos);
         }

         $end_pos = strpos($text, ' ', $start_pos + $len);
         $chars = $end_pos - $start_pos;

         if ($chars < $len ) $chars = $len;

         return ( substr($text, $start_pos, $chars) );

}

function sabrayla_normalize_word($kw) {
      $kw = strtolower($kw);
      $kw = preg_replace('/\W/', '', $kw);
      return $kw;
}


# TODO
# Much more robust user authentication mechanism

function sabrayla_checklogin() {


  $auth = $_COOKIE['sabrayla'];
  $username = $_REQUEST['_username'];
  $password = $_REQUEST['_password'];

  if ($auth) {
     return true;
  }

  if (strlen($username) < 3) return false;
  if ($password == '')  return false;

  $rs = mysql_qw('select a_name from auto_users where a_name=? and password=?', $username, $password);
  $name = mysql_fetch_value($rs);

  if ($name == $username) {
    setcookie('sabrayla', md5($username . $password . 'sabrayla'));
    return true;
  }

  return false;

}

function sabrayla_paypal_notify($data)  {

  foreach($data as $i=>$v) {
    $postdata.= $i . "=" . urlencode($v) . "&";
  }
  $postdata.="cmd=_notify-validate";

  $ch=curl_init();

  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_URL, IPN_PPURL);
  curl_setopt($ch,CURLOPT_POST,1);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);

  //Start ob to prevent curl_exec from displaying stuff.
  ob_start();
  curl_exec($ch);
  $info=ob_get_contents();
  ob_end_clean();

  curl_close($ch);

  return $info;
}

function sabrayla_sprint_r($mixed) {
  ob_start();
  print_r($mixed);
  $r = ob_get_contents();
  ob_end_clean();
  return $r;
}


?>
