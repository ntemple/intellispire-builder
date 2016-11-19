<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  
 * 
 * @version $Id: IcResource_Contacts.php 21 2013-03-15 19:35:01Z ntemple $
 * @package IcApi
 */


/**
 * This class represents the Contact resource in the IntelliContact API.
 *
 * @author jtravis@broadwick.com
 */
class IcResource_Contacts extends IcResource {
	
	private $filter_by;

	private $contacts;
	
	public function __construct() {
		parent::__construct();
		$this->contacts = array();
	}
	
	public function processChild(DOMDocument $child_xml, $childname) {
		if($childname != "contact") return;
		
		$contact = new IcResource_Contact();
		$contact->setXml($child_xml);
		$this->contacts[] = $contact;
	}
	
	public function getLocation() {
		return "{$this->getName()}";
	}
	
	public function getName() {
		return "contacts";
	}
	
	public function filterBy($type) {
		$this->filter_by = $type;
	}
	
	public function getFilterBy() {
		return $this->filter_by;
	}
	
	public function getContacts() {
		return $this->contacts;
	}
}

?>
