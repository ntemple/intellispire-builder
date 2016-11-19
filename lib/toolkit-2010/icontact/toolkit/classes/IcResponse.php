<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  
 * 
 * @version $Id: IcResponse.php 21 2013-03-15 19:35:01Z ntemple $
 * @package IcApi
 */



/**
 * This represents a response from IntelliContact that is not resource.  For instance, a message
 * saying that your changes have been accepted could be represented as an instance of this.
 * 
 * @author acox@broadwick.com
 */
class IcResponse {
	
	/**
	 * The XML that is returned from the server
	 *
	 * @var DOMDocument
	 */
	private $xml;
	
	/**
	 * @param DOMDocument $xml
	 */
	public function __construct($xml=null) {
		if($xml) $this->setXml($xml);
	}

	/**
	 * Set the XML associated with this response
	 *
	 * @param DOMDocument $xml
	 */
	public function setXml($xml) {
		$this->xml = $xml;
		$status = $this->getStatus();
		if($status != "success") {
			$error_code = $xml->getElementsByTagName("response")->item(0)->getElementsByTagName("error_code")->item(0)->nodeValue;
			$error_message = $xml->getElementsByTagName("response")->item(0)->getElementsByTagName("error_message")->item(0)->nodeValue;
			throw new Exception($error_message,$error_code);
		}
	}

	/**
	 * Returns whether or not the XML represents a successful transaction or a failed one.
	 *
	 * @return string
	 */
	public function getStatus() {
		if($this->xml) {
			$xml = $this->xml;
			$response = $xml->getElementsByTagName("response")->item(0);
			$status = $response->getAttribute("status");
			return $status;
		} else {
			return false;
		}
	}		
	
	/**
	 * Returns the XML associated with this response
	 *
	 * @return DOMDocument
	 */
	public function getXml() {
		return $this->xml;
	}
	
}

?>
