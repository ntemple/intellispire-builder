<?php
/**
* Amazon PHP Classes and Functions
*
* @category   Amazon
* @package    AWS
* @author     Nick Temple <Nick.Temple@intellispire.com>
* @copyright  2002-2008 Intellispire
* @license    MIT: http://www.opensource.org/licenses/mit-license.php
* @version    SVN: $Id: s3.class.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*
* Requires PHP 5+
*/

require_once 'aws.class.php';
// pear install  Crypt_HMAC

class S3 extends AWS {
    
    var $debug = 1;
    var $S3_URL = "http://s3.amazonaws.com/";
    var $contentType = 'text/plain';
    var $acl = 'private';
    // or 'public-read';
    var $bucket = '';
    
    public function __construct($a, $s, $bucket = '') {
        $this->bucket = $bucket;
        parent::__construct($a, $s);
    }
    
    public function putfile($key, $path, $contentType = -1, $acl=-1) {
        $data = file_get_contents($path);
        $this->put($key, $data, $contentType,$acl);
    }
    
    public function put($key, $data, $contentType = -1, $acl= -1) {
        $this->_call('PUT', $key, $data, $contentType, $acl);
    }
    
    public function get($key) {
        return $this->_call('GET', $key, '', '', '');
    }
    
    public function delete($key) {
        return $this->_call('DELETE', $key, '', '', '');
    }
    
    public function getfile($key, $path) {
        $out = fopen($path, "w+");
        $data = $this->get($key);
        fwrite($out, $data);
        fclose($out);
    }
    
    
    public function getRequestURL($key, $expires) {
        $bucket = $this->bucket;
        $key = urlencode($key);
        
        $signature = urlencode($this->signString("GET\n\n\n$expires\n/$bucket/$key"));
        return("http://s3.amazonaws.com/$bucket/$key?AWSAccessKeyId=".$this->access_key .
        "&Signature=$signature" .
        "&Expires=$expires"
        );
    }
    
    private function _call($verb, $key, $data, $contentType, $acl) {
        
        $bucket = $this->bucket;
        if ($acl == -1) {
            $acl = $this->acl;
        }
        if ($contentType == -1) {
            $contentType = $this->contentType;
        }
        
        
        // User has entered parameters so let's do the S3 request!
        
        // pull off request parameters.
        $resource = "$bucket/$key";
        if ($resource == "/") {
            // please explain?
            $resource = "";
        }
        
        $methods = array("GET"=>1, "DELETE"=>1, "PUT"=>1);
        if ($methods[$verb] == 0) {
            throw new Exception("S3: Unknown verb $verb");
        }
        
        $httpDate = gmdate(DATE_RFC822);
        $stringToSign = "$verb\n\n$contentType\n$httpDate\nx-amz-acl:$acl\n/$resource";
        $signature = $this->signString($stringToSign);
        
        
        $req =& new HTTP_Request($this->S3_URL . $resource);
        $req->setMethod($verb);
        $req->addHeader("content-type", $contentType);
        $req->addHeader("Date", $httpDate);
        $req->addHeader("x-amz-acl", $acl);
        $req->addHeader("Authorization", "AWS " . $this->access_key . ":" . $signature);
        
        if ($data != "") {
            $req->setBody($data);
        }
        $req->sendRequest();
        $req->setBody('truncated:');
        return $req->getResponseBody();
    }
    
}

