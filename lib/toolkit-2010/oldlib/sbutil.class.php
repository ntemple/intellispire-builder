<?php
/**
* Copyright (c)2008 Intellispire and original author(s)
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
* @category   sabrayla
* @package    sabrayla
* @author     Nick Temple <Nick.Temple@intellispire.com>
* @copyright  2002-2008 Intellispire
* @version    SVN: $Id: sbutil.class.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.2
*/

/** ensure this file is being included by a parent file */
defined('_SB_VALID_' ) or die('Direct Access to this location is not allowed.' );

define('SB_LOG_INFO', 6); 
// same as PEARL_LOG_INFO, but defined.
define('SB_FULL_TRACE', -1);
// full dump of the paramaters

// Some nice to have regexps
define('EMAIL_FORMAT', "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}$/i");
define('URL_FORMAT', "/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/i");
define('DATE_MYSQL', 'Y-m-d H:i:s');
define('EMPTY_DATETIME', '0000-00-00 00:00:00');

/**
* Generic helper class.
*/

class sbutil {
    
    static $LOG = false;
    static $DEBUG = 0;
    
    
    static function pchop(&$string)
    {
        if (is_array($string)) {
            foreach($string as $i => $val)
            {
                $endchar = self::pchop($string[$i]);
            }
        } else {
            $endchar = substr("$string", strlen("$string") - 1, 1);
            $string = substr("$string", 0, -1);
        }
        return $endchar;
    }
    
    static function sprint_r($mixed) {
        ob_start();
        print_r($mixed);
        return ob_get_clean();
    }
    
    
    public static function validate_email($email)
    {
        
        // Create the syntactical validation regular expression
        $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";
        
        // Presume that the email is invalid
        $valid = 0;
        
        // Validate the syntax
        if (eregi($regexp, $email)) {
            list($username,$domaintld) = split("@",$email);
            // Validate the domain
            if (getmxrr($domaintld,$mxrecords)) {
                $valid = 1;
            }
        } else {
            $valid = 0;
        }
        
        return $valid;
    }
    
    public static function curl_post($url, $data) {
        $params = array();
        
        foreach($data as $name => $value) {
            $params .= $name .'='. urlencode($value);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        
        ob_start();
        $result = curl_exec($ch);
        curl_close($ch);
        $page = ob_get_clean();
        
        return $page;
    }
    
    public static function bp_formcapture($url, $firstname, $lastname, $email, $ar, $custom= array() ) {
        
        $params = array();
        $params['first_name']     = $firstname;
        $params['last_name']      = $lastname;
        $params['CUSTOM_IP']      = $_SERVER['REMOTE_ADDR'];
        $params['email']          = $email;
        $params['responder_id']   = $ar;
        $params['form_version']   = 3.47;
        $params['desired_action'] = 'SUB';
        
        foreach($custom as $name => $value) {
            $params["CUSTOM_" . $name]  = $value;
        }
        
        $page = self::curl_post($url, $params);
        return $page;
    }
    
    
    /* Logging System */
    static function trace($message = '', $level=SB_LOG_INFO) {
        
        $out = '';
        
        $data  = debug_backtrace();
        $here  = array_shift($data);
        $place = array_shift($data);
        
        $file     = $place['file'];
        $function = $place['function'];
        $line     = $here['line'];
        $class    = $place['class'];
        $args     = serialize($place['args']);
        
        $out .= $file . " - ";
        if (isset($class)) {
            $out .= $class."::";
        }
        $out .= "$function [$line]";
        if ($message) {
            $out .= ": $message";
        }
        
        self::writelog($out, '', $level);
        if ($level = SB_FULL_TRACE) {
            self::writelog($place);
        }
    }
    
    static function debug($message, $level=SB_LOG_INFO) {
        self::writelog($message, '', $level);
    }
    
    
    static function backtrace($message = '', $level=SB_LOG_INFO) {
        $data = debug_backtrace();
        array_shift($data);
        $message .= "\n";
        $message .= self::sprint_r($data);
        self::writelog($message, '', $level);
    }
    
    static function writelog($message, $label = '', $level = SB_LOG_INFO) {
        if (self::initLogging()) {
            $message = self::sprint_r($message);
            $GLOBALS['log']->log($label . $message, $level);
        }
    }
    
    /**
* Setup logging
*/
    static function initLogging($filename = '/tmp/sabrayla_log') {
        if (!self::$LOG) {
            return false;
        }
        if (isset($GLOBALS['log'])) {
            return true;
        }
        ## Setup logging here ##
        
        require_once('Log.php');
        $_log_trace = array('UNDERFLOW', 'SABRAYLA-U');
        $_log = &Log::singleton('file', $filename, 'SABRAYLA');
        $GLOBALS['log'] = $_log;
        return true;
        
    }
    
    ## File Functions
    function chmod_r($path, $filemode, $dirmode = 0777)
    {
        if (!is_dir($path)) {
            return chmod($path, $filemode);
        }
        
        $dh = opendir($path);
        while ($file = readdir($dh)) {
            if ($file != '.' && $file != '..') {
                $fullpath = $path.'/'.$file;
                if (!is_dir($fullpath)) {
                    if (!chmod($fullpath, $filemode)) {
                        return FALSE;
                    }
                } else {
                    if (!chmod_r($fullpath, $filemode)) {
                        return FALSE;
                    }
                }
            }
        }
        
        closedir($dh);
        
        if (chmod($path, $dirmode)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    function chmod_RX($path, $filemode)
    {
        if (!is_dir($path)) {
            return chmod($path, $filemode);
        }
        
        $dh = opendir($path);
        while ($file = readdir($dh)) {
            if ($file != '.' && $file != '..') {
                $fullpath = $path.'/'.$file;
                if (!is_dir($fullpath)) {
                    if (!chmod($fullpath, $filemode)) {
                        return FALSE;
                    }
                } else {
                    if (!chmod_R($fullpath, $filemode)) {
                        return FALSE;
                    }
                }
            }
        }
        
        closedir($dh);
        
        if (chmod($path, $filemode)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    function recurse_chown_chgrp($mypath, $uid, $gid)
    {
        $d = opendir($mypath) ;
        while (($file = readdir($d)) !== false) {
            if ($file != "." && $file != "..") {
                
                $typepath = $mypath . "/" . $file ;
                
                //print $typepath. " : " . filetype ($typepath). "<BR>" ;
                if (filetype($typepath) == 'dir') {
                    recurse_chown_chgrp($typepath, $uid, $gid);
                }
                
                chown($typepath, $uid);
                chgrp($typepath, $gid);
                
            }
        }
        
    }

/**
 * Cut string to n symbols and add delim but do not break words.
 *
 * Example:
 * <code>
 *  $string = 'this sentence is way too long';
 *  echo neat_trim($string, 16);
 * </code>
 *
 * Output: 'this sentence is'
 *
 * @access public
 * @param string string we are operating with
 * @param integer character count to cut to
 * @param string|NULL delimiter. Default: 
 * @return string processed string
 **/
function neat_trim($str, $n, $delim='...') {
   $len = strlen($str);
   if ($len > $n) {
       preg_match('/(.{' . $n . '}.*?)\b/', $str, $matches);
       return rtrim($matches[1]) . $delim;
   }
   else {
       return $str;
   }
}


    
    function keypos($text, $kw, $len)
    {
        
        $pos = strpos($text, $kw);
        $start_pos = $pos - $len / 4;
        if ($start_pos < 1) {
            $start_pos = 0;
        } else {
            $start_pos = strpos($text, ' ', $start_pos);
        }
        
        $end_pos = strpos($text, ' ', $start_pos + $len);
        $chars = $end_pos - $start_pos;
        
        if ($chars < $len ) {
            $chars = $len;
        }
        
        return(substr($text, $start_pos, $chars) );
        
    }
    
    function normalize_word($kw)
    {
        $kw = strtolower($kw);
        $kw = preg_replace('/\W/', '', $kw);
        return $kw;
    }
    /*
function paypal_notify($data)
{

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
*/
    
    // do a chop off the _first_ character
    static function pchopfirst(&$string)
    {
        if (is_array($string)) {
            foreach($string as $i => $val)
            {
                $endchar = pchopr($string[$i]);
            }
        } else {
            $endchar = substr("$string", 0, 1);
            $string = substr("$string", 1, strlen("$string") -1);
        }
        return $endchar;
    }
    
    
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
    
    static function uuid() {
        
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
    
    static function uuid_raw() {
        return str_replace('-', '', self::uuid());
    }
    
    
    
}
