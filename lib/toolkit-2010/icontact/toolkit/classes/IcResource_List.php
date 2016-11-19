<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  
 * 
 * @version $Id: IcResource_List.php 21 2013-03-15 19:35:01Z ntemple $
 * @package IcApi
 */


require_once 'IcResource.php';

/**
 * This class represents the List resource in the IntelliContact API.
 *
 * @author jtravis@broadwick.com
 */
class IcResource_List extends IcResource {
	
	private $list_id;
	
	public function getListId() {
		return $this->list_id;
	}
	
	public function getLocation() {
		$list_id = $this->getListId();
		if($list_id > 0) {
			return "{$this->getName()}/$list_id";
		}
	}
	
	public function getName() {
		return "list";
	}
	
	public function setListId($list_id) {
		$this->list_id = $list_id;
	}
	
}

?>
