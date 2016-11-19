<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  
 * 
 * @version $Id: IcResource_Messages.php 21 2013-03-15 19:35:01Z ntemple $
 * @package IcApi
 */


/**
 * This class represents the message resource in the IntelliContact API.
 *
 * @author acox@broadwick.com
 */
class IcResource_Messages extends IcResource {
	
	private $messages;
	
	public function __construct() {
		parent::__construct();
		$this->messages = array();
	}
	
	public function processChild(DOMDocument $child_xml, $childname) {
		if($childname != "message") return;
		
		$message = new IcResource_Message();
		$message->setXml($child_xml);
		$this->messages[] = $message;
	}
	
	public function getLocation() {
		return "{$this->getName()}/{$this->filter_by}";
	}
	
	public function getName() {
		return "messages";
	}
	
	public function getMessages() {
		return $this->messages;
	}
}

?>
