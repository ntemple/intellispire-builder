<?php
/**
* Amazon PHP Classes and Functions
*
* Copyright (c)2008 Intellispire
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
* @category   Amazon
* @package    AWS
* @author     Nick Temple <Nick.Temple@intellispire.com>
* @copyright  2002-2008 Intellispire
* @license    MIT: http://www.opensource.org/licenses/mit-license.php
* @version    SVN: $Id: aws.class.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*
* Requires PHP 5+
*
* portions from: http://code.google.com/p/php-aws/
*
*/


if (!defined('DATE_RFC822'))  define('DATE_RFC822',  'D, d M Y H:i:s T');
require_once 'HTTP/Request.php'; // grab this with "pear install --onlyreqdeps HTTP_Request"


class AWSException extends Exception
{
    public function __construct($errors)
    {
        echo $errors['Error']['Code'] . ': ' . $errors['Error']['Message'];
    }
}

class AWS
{
    var $access_key;
    var $secret;
    var $endpoint;
    var $multi = array();

    var $_status;
    var $_requestid;
    var $_errors;
    
    var $remoteCallFunc = 'http_request';
    //  var $remoteCallFunc = 'curl_request';
    //         var $remoteCallFunc = 'multi_curl_request';
    
    public function __construct($a, $s) {
        $this->access_key = $a;
        $this->secret = $s;
    }
    
    function setRemotCallFunc($func)
    {
        $tmp = $this->remoteCallFunc;
        $this->remoteCallFunc = $func;
        return $tmp;
    }
    
    function http_request($endpoint, $request)
    {

        $req = & new HTTP_Request($endpoint);
        $req->setMethod('GET');
        $req->addRawQueryString($request);
        $req->sendRequest();
        
        // TODO: throw error on failure
        return $req->getResponseBody();
        
    }
    
    function curl_request($endpoint, $request)
    {
        $url = $endpoint . '?' . $request;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        return $response;
        
    }
    
    function multi_curl_request($endpoint, $request)
    {
        $req['endpoint'] = $endpoint;
        $req['request']  = $request;
        $this->multi[]  = $req;
        return "<ok />";
    }
    
    function multi_exec()
    {
        $curly = array();
        // array of curl handles
        $result = array();
        // data to be returned
        $multiHandle = curl_multi_init();
        // multi handle
        foreach($this->multi as $req) {
            $url = $req['endpoint'] . '?'. $req['request'];
            $key = $this->uuid();
            $curly[$key] = curl_init($url);
            curl_setopt($curly[$key], CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($multiHandle, $curly[$key]);
        }
        
        $mh = $multiHandle;
        do {
            $execReturnValue = curl_multi_exec($mh, $stillRunning);
        } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM); 

        // Loop and continue processing the request
        while ($stillRunning and $execReturnValue == CURLM_OK) {
            //       $this->timeslice();
            // wait for network
            if (curl_multi_select($mh) != -1) {
                // Pull in any new data, or at least handle timeouts
                do {
                    $execReturnValue = curl_multi_exec($mh, $stillRunning);
                } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM) ;
            }
        }
        
        
        foreach($curly as $key => $content) {
            $result[$key] = curl_multi_getcontent($content);
            curl_multi_remove_handle($multiHandle, $content);
            curl_close($content);
        }
        
        curl_multi_close($multiHandle);
        return($result);
    }
    
    function commit()
    {
        if (count($this->multi) > 0) {
            $this->multi_exec();
        }
    }
    
    function timeslice()
    {
        usleep(50);
        // adjust as necessary
    }
    
    // TODO: http://us2.php.net/curl_multi_init
    
    function hasher($data)
    {
        // Algorithm adapted (stolen) from http://pear.php.net/package/Crypt_HMAC/)
        $key = $this->secret;
        if (strlen($key) > 64) {
            $key = pack("H40", sha1($key));
        }
        if (strlen($key) < 64) {
            $key = str_pad($key, 64, chr(0));
        }
        $ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
        $opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));
        return sha1($opad . pack("H40", sha1($ipad . $data)));
    }
    
    protected function signString($stringToSign) {
        $signature = $this->hex2b64($this->hasher($stringToSign));
        return $signature;
    }
    
    
    function implode_with_keys($array, $glue = '', $keyGlue = '')
    {
        $elements = array();
        
        foreach($array as $key => $value)
        {
            $elements[] = $key . $keyGlue . $value;
        }
        
        return implode($glue, $elements);
    }
    
    
    function sign(&$params)
    {
        
        $params['AWSAccessKeyId'] = $this->access_key;
        $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
        $params['SignatureVersion'] = '1';
        // '2008-01-01';
        
        uksort($params, 'strcasecmp');
        $string_to_sign = '';
        foreach($params as $name => $value) {
            $string_to_sign .= "$name$value";
        }
        
        $params['Signature'] = $this->signString($string_to_sign);
    }
    
    
    protected function xmlrequest($params, $return_xml = true) {
        
        $this->sign($params);
        
        foreach($params as &$param)
        {
            $param = rawurlencode($param);
        }
        
        $request =  $this->implode_with_keys($params, '&', '=');
        
        // make sure we do NOT have a ? on the endpoint
        $endpoint = $this->endpoint;
        $c = $this->pchop($endpoint);
        if ($c != '?') {
            $endpoint .= $c;
        }
        // put it back if it's not a ?
        
        $response =  call_user_func(array($this, $this->remoteCallFunc), $endpoint, $request);
        $this->_xml = new SimpleXMLElement($response);

        $results = $this-> simplexml2array($this->_xml);
        if(isset($results['Status']))    $this->_status    = $results['Status'];
        if(isset($results['RequestId'])) $this->_requestid = $results['RequestId'];
      
        if (isset($results['Errors'])) {
            throw new AWSException($results['Errors']);
        }
        $this->_results = $results;
       
        if ($return_xml) {
          return $this->_xml;
        } else {
          return $results;
        }
        
    }


    
    
    
    protected function hex2b64($str) {
        $raw = '';
        for ($i=0; $i < strlen($str); $i+=2) {
            $raw .= chr(hexdec(substr($str, $i, 2)));
        }
        return base64_encode($raw);
    }
    
    
    
/**
* implementation of Perl's "chop" function
*/
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
    
    
    function uuid()
    {
        
        // The field names refer to RFC 4122 section 4.1.2
        
        return sprintf('%04x%04x%04x%03x4%04x%04x%04x%04x',
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
    
function simplexml2array($xml) {
   if (get_class($xml) == 'SimpleXMLElement') {
       $attributes = $xml->attributes();
       foreach($attributes as $k=>$v) {
           if ($v) $a[$k] = (string) $v;
       }
       $x = $xml;
       $xml = get_object_vars($xml);
   }
   if (is_array($xml)) {
       if (count($xml) == 0) return (string) $x; // for CDATA
       foreach($xml as $key=>$value) {
           $r[$key] = $this->simplexml2array($value);
       }
       if (isset($a)) $r['@'] = $a;    // Attributes
       return $r;
   }
   return (string) $xml;
}

    
}
