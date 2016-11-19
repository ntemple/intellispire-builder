<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  
 * 
 * @version $Id: IcResource_Lists.php 21 2013-03-15 19:35:01Z ntemple $
 * @package IcApi
 */


/**
 * This class represents the Lists resource in the IntelliContact API.
 *
 * @author jtravis@broadwick.com
 */
class IcResource_Lists extends IcResource {
	
	private $lists;
	
	public function __construct() {
		parent::__construct();
		$this->Lists = array();
	}
	
	public function processChild(DOMDocument $child_xml, $childname) {
		if($childname != "list") return;
		
		$list = new IcResource_List();
		$list->setXml($child_xml);
		$this->lists[] = $list;
	}
	
	public function getLocation() {
		return "{$this->getName()}";
	}
	
	public function getName() {
		return "lists";
	}
	
	public function getLists() {
		return $this->lists;
	}
}

?>
