<?php
/**
 * Sabrayla PHP Classes and Functions
 *
 * Generic, useful functions. Todo: refactor to increase granularity, create
 * some type of class and class loader to reduce startup time.
 *
 * @category   sabrayla
 * @package    sabrayla
 * @author     Nick Temple <Nick.Temple@intellispire.com>
 * @copyright  2002-2006 Intellispire
 * @license    http://www.sabrayla.com/license/1_0.txt Sabrayla License 1.0
 * @version    SVN: $Id: functions.inc.php 21 2013-03-15 19:35:01Z ntemple $
 * @since      File available since Release 0.1
 */


/** ensure this file is being included by a parent file */
defined( '_SB_VALID_' ) or die( 'Direct Access to this location is not allowed.' );

/*
 * Make sure we have relatively recent technology
 */

if (version_compare(phpversion(), '4.1') < 0) {
  die("You must use PHP 4.1 or greater! (5.1+ preferred)");
}

// Compatibility functions

// Compatibility constants (available since PHP 5.1.1). This constants are taken from
// PHP_Compat PEAR package
if (!defined('DATE_ATOM'))    define('DATE_ATOM',    'Y-m-d\TH:i:sO');
if (!defined('DATE_COOKIE'))  define('DATE_COOKIE',  'D, d M Y H:i:s T');
if (!defined('DATE_ISO8601')) define('DATE_ISO8601', 'Y-m-d\TH:i:sO');
if (!defined('DATE_RFC822'))  define('DATE_RFC822',  'D, d M Y H:i:s T');
if (!defined('DATE_RFC850'))  define('DATE_RFC850',  'l, d-M-y H:i:s T');
if (!defined('DATE_RFC1036')) define('DATE_RFC1036', 'l, d-M-y H:i:s T');
if (!defined('DATE_RFC1123')) define('DATE_RFC1123', 'D, d M Y H:i:s T');
if (!defined('DATE_RFC2822')) define('DATE_RFC2822', 'D, d M Y H:i:s O');
if (!defined('DATE_RSS'))     define('DATE_RSS',     'D, d M Y H:i:s T');
if (!defined('DATE_W3C'))     define('DATE_W3C',     'Y-m-d\TH:i:sO');

// Some nice to have regexps
define('EMAIL_FORMAT', "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i");
define('URL_FORMAT', "/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/i");

define('DATE_MYSQL', 'Y-m-d H:i:s');
define('EMPTY_DATETIME', '0000-00-00 00:00:00');


// Declare clone for php4
if (version_compare(phpversion(), '5.0') < 0) {
    eval('
    function clone($object) {
      return $object;
    }
    ');
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

function safeconstant($const, $default = NULL) {
  if (!defined($const)) return $default;
  else return constant($const);
}

function saferequest($var, $default = NULL) {
  if (! empty($_REQUEST[$var])) return $_REQUEST[$var];
  return $default;
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
   if (! defined('LOG')) return;
   // TODO: if (mixed)
   $messsage = sabrayla_sprint_r($message);
   $GLOBALS['log']->log($message, $level);
}

function sabrayla_trace($message) {
  sabrayla_log($message);
}

function sabrayla_debug($message) {
 sabrayla_log($message);
}


function sabrayla_backtrace($message) {
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

function sabrayla_getProperty($name, $default = '') {
  global $_properties;
  global $db;

  if (isset($_properties['name'])) {
    return $_properties[$name];
  }


  $value = $db->get_value('select value from ' . T_PROPERTIES . ' where name=?', $name);
  if ($value == '') $value = $default;
  $_properties[$name] = $value;
  return $_properties[$name];

}

function sabrayla_getProperties($settings) {
   global $_properties;
   global $db;

   $data = $db->get_select('select name,value from ' . T_PROPERTIES . " where name in ($settings)");
   foreach ($data as $name=>$value) {
     $_properties[$name] = $value;
   }

   return $_properties;
}

function redirect($location) {
  sabrayla_trace("+redirect $location");
  header("Location: $location");

}

function hex2b64($str) {
    $raw = '';
    for ($i=0; $i < strlen($str); $i+=2) {
        $raw .= chr(hexdec(substr($str, $i, 2)));
    }
    return base64_encode($raw);
}

function GetRandomString($length, $ints = false) {

       // you could repeat the alphabet to get more randomness
       $template = "23456789abcdefghjkmnpqrstuvwxyz";

       if ($ints) {
         $template = "23456789";
       }
       settype($template, "string");

       for ($a = 0; $a < $length; $a++) {
           $b = rand(0, strlen($template) - 1);
           $rndstring .= $template[$b];
       }

       return "$rndstring";
}

function CreatePassword() {

    $words = array ('coffee', 'cake', 'money', 'juice', 'apple',
                         'java', 'pat', 'cat', 'car', 'doll', 'fan', 'hat');

    $random = rand (0, (sizeof($words)-1));
    $password = GetRandomString(3, true) . $words[$random] . GetRandomString(3, true);


    return $password;
}

function validate_email($email)
{

   // Create the syntactical validation regular expression
   $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";

   // Presume that the email is invalid
   $valid = 0;

   // Validate the syntax
   if (eregi($regexp, $email))
   {
      list($username,$domaintld) = split("@",$email);
      // Validate the domain
#      if (getmxrr($domaintld,$mxrecords))
         $valid = 1;
   } else {
      $valid = 0;
   }

   return $valid;

}

function xmlentities($string) {

  $string = htmlentities2unicodeentities($string);
  $string = str_replace ( array ( '&', '"', "'", '<', '>', '?'), array
                                 ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '' ),
             $string );
  $string = str_replace('E##E', '&#', $string);
  # Strip all non-unicode chars
  # TODO: s/([^\x20-\x7f])/sprintf("&#%d;", ord($1)/eg;
  $string = preg_replace('/[^\x01-\x7f]/e', '', $string);

  return $string;
}

function htmlentities2unicodeentities ($input) {
  static $htmlEntities = NULL;
  static $utf8Entities = NULL;

  if (empty($htmlEntities)) {
    $htmlEntities = array_values (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
    $entitiesDecoded = array_keys  (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
    $num = count ($entitiesDecoded);
    for ($u = 0; $u < $num; $u++) {
      $utf8Entities[$u] = 'E##E'.ord($entitiesDecoded[$u]).';';
    }
  }

  return str_replace ($htmlEntities, $utf8Entities, $input);
}


/*
function xmlentities($string, $quote_style=ENT_QUOTES)
{
   static $trans;
   if (!isset($trans)) {
       $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);
       foreach ($trans as $key => $value)
           $trans[$key] = '&#'.ord($key).';';
       // dont translate the '&' in case it is part of &xxx;
       $trans[chr(38)] = '&';
   }
   // after the initial translation, _do_ map standalone '&' into '&#38;'
   return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,3};)/","&#38;" , strtr($string, $trans));
}

# replace all high order bytes with Unicode equivalents
$text = preg_replace('/([\xc0-\xdf].)/se', "'&#' . ((ord(substr('$1', 0, 1)) - 192) * 64 + (ord(substr('$1', 1, 1)) - 128)) . ';'", $text);
$text = preg_replace('/([\xe0-\xef]..)/se', "'&#' . ((ord(substr('$1', 0, 1)) - 224) * 4096 + (ord(substr('$1', 1, 1)) - 128) * 64 + (ord(substr('$1', 2, 1)) - 128)) . ';'", $text);


*/


/**
 * Generates a Universally Unique IDentifier, version 4.
 *
 * RFC 4122 (http://www.ietf.org/rfc/rfc4122.txt) defines a special type of Globally
 * Unique IDentifiers (GUID), as well as several methods for producing them. One
 * such method, described in section 4.4, is based on truly random or pseudo-random
 * number generators, and is therefore implementable in a language like PHP.
 *
 * We choose to produce pseudo-random numbers with the Mersenne Twister, and to always
 * limit single generated numbers to 16 bits (ie. the decimal value 65535). That is
 * because, even on 32-bit systems, PHP's RAND_MAX will often be the maximum *signed*
 * value, with only the equivalent of 31 significant bits. Producing two 16-bit random
 * numbers to make up a 32-bit one is less efficient, but guarantees that all 32 bits
 * are random.
 *
 * The algorithm for version 4 UUIDs (ie. those based on random number generators)
 * states that all 128 bits separated into the various fields (32 bits, 16 bits, 16 bits,
 * 8 bits and 8 bits, 48 bits) should be random, except : (a) the version number should
 * be the last 4 bits in the 3rd field, and (b) bits 6 and 7 of the 4th field should
 * be 01. We try to conform to that definition as efficiently as possible, generating
 * smaller values where possible, and minimizing the number of base conversions.
 *
 * @copyright  Copyright (c) CFD Labs, 2006. This function may be used freely for
 *              any purpose ; it is distributed without any form of warranty whatsoever.
 * @author      David Holmes <dholmes@cfdsoftware.net>
 *
 * @return  string  A UUID, made up of 32 hex digits and 4 hyphens.
 */

function uuid() {
  
   // The field names refer to RFC 4122 section 4.1.2

   return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
       mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
       mt_rand(0, 65535), // 16 bits for "time_mid"
       mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
       bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
           // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
           // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
           // 8 bits for "clk_seq_low"
       mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node" 
   ); 
}

function uuid_raw() {
   return str_replace('-', '', uuid());
}



?>
