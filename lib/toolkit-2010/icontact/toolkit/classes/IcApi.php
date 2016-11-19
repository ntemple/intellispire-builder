<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  
 * 
 * @version $Id: IcApi.php 21 2013-03-15 19:35:01Z ntemple $
 * @package IcApi
 */

/**
 * This is the class that handles most of the heavy communication logic between your
 * code and the IntelliContact API.  It knows how to form the signature, authorizes you, etc.
 * 
 * The main thing that it doesn't have any idea about is what the URL is for the resource you're
 * trying to get.  That's why there are classes that extend IcResource.  The job of those children
 * is to do things such as specifying that you're trying to get contact #123 (contact/123) or
 * message #456 (message/456).
 *
 * @author acox@broadwick.com
 */
class IcApi {
	
	/**
	 * Holds whether or not we are currently authorized
	 *
	 * @var boolean
	 */
	private $authorized;
	
	/**
	 * The base url of the api.  Might look something like
	 * http://app.intellicontact.com/icp
	 *
	 * @var string
	 */
	private $base;

	/**
	 * A flag that controls whether not debug info gets printed
	 *
	 * @var boolean
	 */
	private $debug;

	/**
	 * The API's public key
	 *
	 * @var string
	 */
	private $key;
	
	/**
	 * The login that we want to use when communicating with IntelliContact
	 *
	 * @var string
	 */
	private $login;
	
	/**
	 * The password associated with the login
	 *
	 * @var string
	 */
	private $password;
	
	/**
	 * This gets appended onto the end of the request URL in order to suppor
	 * remote debugging
	 *
	 * @var string
	 */
	private $remote_debug;
	
	/**
	 * The secret associated with the API key
	 *
	 * @var string
	 */
	private $secret;
	
	/**
	 * Our authorization token
	 *
	 * @var string
	 */
	private $token;
	
	/**
	 * Our current client folder
	 *
	 * @var string
	 */
	private $clientfolder;
	
	/**
	 * Our current sequence number
	 *
	 * @var string
	 */
	private $seq;
	
	/**
	 * The API version 
	 *
	 * @var string
	 */
	private $version;
	
	public function __construct($base) {
		$this->base = $base . "/core/api";
		$this->authorized = false;
		$this->debug = false;
		$this->remote_debug = null;
	}

	/**
	 * Returns the current sequence number
	 *
	 * @return string
	 */
	public function getSeq() {
		return $this->seq;
	}

	/**
	 * Sets sequence number
	 *
	 * @param string
	 */
	public function setSeq($sSeq) {
		$this->seq = $sSeq;
	}

	/**
	 * Sets sequence number += 1
	 *
	 * @param string
	 */
	public function iterateSeq() {
		$this->seq = (string)((int)$this->seq +1);
	}

	/**
	 * Returns whether or not debugging is turned on
	 *
	 * @return boolean
	 */
	public function getDebug() {
		return $this->debug;
	}

	/**
	 * Gets the API public key
	 *
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}
	
	/**
	 * Gets the login that the API will be using
	 *
	 * @return string
	 */
	public function getLogin() {
		return $this->login;
	}
	
	/**
	 * Gets the password that is associated with the login
	 *
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}
	
	/**
	 * Returns the remote debugging string
	 *
	 * @return string
	 */
	public function getRemoteDebug() {
		return $this->remote_debug;
	}
	
	/**
	 * Gets the secret for the API key
	 *
	 * @return string
	 */
	public function getSecret() {
		return $this->secret;
	}
	
	/**
	 * Returns the API token.
	 *
	 * @return string
	 */
	public function getToken() {
		return $this->token;
	}
	
	/**
	 * Returns the API client folder. 
	 *
	 * @return string $clientfolder
	 */
	public function getClientFolder() {
		return $this->clientfolder;
	}
	
	/**
	 * Returns the version of the API
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}
	
	/**
	 * Sets whether or not debugging is turned on 
	 *
	 * @param boolean $debug 
	 */
	public function setDebug($debug) {
		$this->debug = $debug;
	}
	
	/**
	 * Sets the API public key
	 *
	 * @param string $key
	 */
	public function setKey($key) {
		$this->key = $key;
		$this->authorized = false;
	}
	
	/**
	 * Sets the login that the API will be using
	 *
	 * @param string $login
	 */
	public function setLogin($login) {
		$this->login = $login;
		$this->authorized = false;
	}
	
	/**
	 * Sets the password that is associated with the login
	 *
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
		$this->authorized = false;
	}
	
	/**
	 * Sets a string that can be used for remote debugging.  It gets
	 * appended onto the end of the URL
	 *
	 * @param string $remote_debug
	 */
	public function setRemoteDebug($remote_debug) {
		$this->remote_debug = $remote_debug;
	}
	
	/**
	 * Sets the secret for the API key
	 *
	 * @param string $secret
	 */
	public function setSecret($secret) {
		$this->secret = $secret;
		$this->authorized = false;
	}
	
	/**
	 * Sets the API token.  Normally, the programmer won't have to call this function,
	 * but they could if they wanted to.
	 *
	 * @param string $token
	 */
	public function setToken($token) {
		$this->token = $token;
	}
	
	/**
	 * Sets the API client folder. 
	 *
	 * @param string $clientfolder
	 */
	public function setClientFolder($clientfolder) {
		if ($this->clientfolder !== $clientfolder){
			$this->authorized = false;
			$this->clientfolder = $clientfolder;
		}
	}
	
	/**
	 * Sets the version of the API
	 *
	 * @param string $version
	 */
	public function setVersion($version) {
		$this->version = $version;
		$this->authorized = false;
	}
	
	/**
	 * Forms the authorization url and requests the token from the IntelliContact
	 * server.
	 *
	 */
	public function authorize() {
		$hash = md5($this->getPassword());
		if ($this->getClientFolder()) {
			$path = "auth/login/{$this->getClientFolder()}/{$this->getLogin()}/$hash";
		} else {
			$path = "auth/login/{$this->getLogin()}/$hash";
		}
		try {
			$xml = $this->doGet($path);
			$this->setToken($xml->getElementsByTagName("token")->item(0)->nodeValue);
			$this->setSeq($xml->getElementsByTagName("seq")->item(0)->nodeValue);
		} catch (Exception $e) {
			$newe = new Exception("Unable to receive token. " . $e->getMessage(), $e->getCode());
			throw $newe;
		}
		$this->authorized = true;
	}
	
	/**
	 * Converts the result from a string into xml.
	 *
	 * @param string $result
	 * @return DOMDocument
	 * @throws Exception
	 */
	private function toXml($result) {
		$xml = @DOMDocument::loadXML($result);
		if( ! $xml ) {
			throw new Exception("Non-XML response received from server: $result");
		}
		$xml->preserveWhitespace = false;
		return $xml;
	}
	
	/**
	 * This goes off and communicates with the server doing a GET request
	 *
	 * @param string $url
	 * @return string
	 */
	private function getCurl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			$result = curl_error($ch);
		}
		curl_close($ch);
		if($this->debug) {
			echo "GET '$url'\n";
			echo "RESPONSE\n'$result' \n";
		}
		return $result;
	}
	
	/**
	 * This goes off and communicates with the server doing a PUT request.  It takes
	 * as a second argument the name of the file that will be sent to the URL specified.
	 *
	 * @param string $url The url to PUT to
	 * @param string $file The name of the file being put
	 * @return string
	 */
	private function putCurl($url,$file) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PUT, 1);
		$fh = fopen($file,"r");
		curl_setopt($ch, CURLOPT_INFILE, $fh);
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
		
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			$result = curl_error($ch);
		}
		curl_close($ch);
		if($this->debug) {
			echo "PUT '$url'\n";
			echo file_get_contents($file);
			echo "RESPONSE\n'$result'\n";
		}
		return $result;
	}
	
	/**
	 * This is used to make an API get call.  It handles doing all of the fancy things
	 * necessary to make the API call work, such as computing the signature.  If
	 * the API call returns a fail response, this throws an exception with information
	 * about the failure included.
	 *
	 * @param string $path
	 * @return DOMDocument
	 * @throws Exception
	 */
	private function doGet($path, $aParams=array()) {
		$path = ltrim($path,"/");
		$requeststring = "?";
		if (count($aParams) > 0){
			foreach ($aParams as $key => $value) {
				$requeststring .= $key . "=" . $value . "&";
			}
		}
		$url = "{$this->base}/v{$this->getVersion()}/$path/" . $requeststring . "api_key={$this->getKey()}&api_sig={$this->computeSignature($path,null,$aParams)}";
		
		if($this->authorized) {
			$url .= "&api_seq={$this->getSeq()}&api_tok={$this->getToken()}";
			$this->iterateSeq();
		}
		
		if($this->remote_debug) {
			$url .= "&{$this->remote_debug}";
		}
		
		$xml = $this->toXml($this->getCurl($url));

		$response_list = $xml->getElementsByTagName("response");
		$response = $response_list->item(0);
		
		$status = $response->getAttribute("status");
		
		if($status == "fail") {
			$error_message_list = $response->getElementsByTagName("error_message");
			$error_code_list = $response->getElementsByTagName("error_code");
			$message = $error_message_list->item(0)->nodeValue;
			$code = $error_code_list->item(0)->nodeValue;
			throw new Exception($message, $code);
		}
		
		return $xml;
	}
	
	/**
	 * This is used to make an API post call.  It handles doing all of the fancy things
	 * necessary to make the API call work, such as computing the signature.  If
	 * the API call returns a fail response, this throws an exception with information
	 * about the failure included.
	 *
	 * @param string $path
	 * @param string $file the name of the file that contains the XML to put
	 * @return DOMDocument
	 * @throws Exception
	 */
	private function doPut($path,$file) {
		$putdata = file_get_contents($file);
		$url = "{$this->base}/v{$this->getVersion()}/$path/?api_key={$this->getKey()}&api_sig={$this->computeSignature($path,$putdata)}";

		if($this->authorized) {
			$url .= "&api_seq={$this->getSeq()}&api_tok={$this->getToken()}";
			$this->iterateSeq();
		}
		
		if($this->remote_debug) {
			$url .= "&{$this->remote_debug}";
		}
		
		$xml = $this->toXml($this->putCurl($url,$file));

		$response_list = $xml->getElementsByTagName("response");
		$response = $response_list->item(0);
		
		return $xml;
	}
	
	/**
	 * This computes a signature of the path provided.  It is smart
	 * enough to know whether to include the token in the signature.
	 *
	 * @param string $path
	 * @param string $putdata
	 * @return string
	 */
	private function computeSignature($path,$putdata=null,$getdata=null) {
		$aPath = explode("/", trim($path, "/ "));
		$sKey= $this->getKey();
		$aRequest['api_key']=$sKey;
		if($putdata) {
			$aRequest['api_put'] = $putdata;
		}
		$aRequest['api_seq'] = $this->getSeq();
		$aRequest['api_tok'] = $this->getToken();
		if (count($getdata) > 0) {
			$aRequest = array_merge($aRequest,$getdata);
		}
		$sSigStr = $this->getSecret() . trim($path, "/ ");
		if(!($aPath[0] == "auth" && $aPath[1] == "login")) {
			@ksort($aRequest, SORT_STRING);
			foreach($aRequest as $sKey=>$sVal)
			{
				if($sKey=='api_sig') continue;
				$sSigStr .= "$sKey$sVal";
			}
		}
		else
			$sSigStr .= "api_key" . $sKey;
		if($this->debug) {
			echo "signature='$sSigStr'\n";
		}
		return md5($sSigStr);
	}
	
	/**
	 * This goes off and gets the XML from the server.  It relies on getUrl(), which
	 * must be implemented by the children classes.
	 *
	 * @param IcResource resource
	 */
	public function get(IcResource $resource) {
		if(!$this->authorized) {
			$this->authorize();
		}
		$aParams = $resource->getFilterBy();
		$path = $resource->getLocation();
		$result = $this->doGet($path, $aParams);
		$resource->setXml($result);
	}
	
	/**
	 * This takes the resource specified and PUTs it on the server.  The server's response is
	 * returned as an IcResponse.
	 *
	 * @param IcResource $resource
	 * @return IcResponse
	 */
	public function put(IcResource $resource) {
		if(!$this->authorized) {
			$this->authorize();
		}
		$path = $resource->getLocation();
		
		$name = tempnam("/tmp","IcDomPut") . ".xml";
		
		$resource->getXml()->save($name);
		
		$result = $this->doPut($path,$name);
		return new IcResponse($result);
	}
	
	/**
	 * This follows an xlink:href attribute of the element specified by $child.
	 *
	 * @param IcResource $resource
	 * @param string $child
	 * @param int $limit The maximum number of children links to follow
	 */
	public function follow(IcResource $resource, $child, $limit=1) {
		$xml = $resource->getXml();

		$xpath = new DOMXPath($xml);
		$xpath->registerNamespace("xlink","http://www.w3.org/1999/xlink");
		$nodelist = $xpath->query("//$child");
		
		if($nodelist->length == 0) {
			throw new Exception("Can't follow resource to $child");
		}

		$iFollows = $nodelist->length;
		
		for($i=0;$i<$iFollows;$i++) {
			if($i >= $limit) break;

			$node = $nodelist->item($i);
			
			$path = $node->getAttributeNS("http://www.w3.org/1999/xlink","href");
	
			if(strlen($path) == 0) {
				throw new Exception("Invalid path: $path");
			}
			
			if(!$this->authorized) {
				$this->authorize();
			}
			
			$xml = $this->doGet($path);
			$resource->processChild($xml,$child);
			
		}
	}
}
?>
