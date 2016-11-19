<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.
 * 
 * @version $Id: IcResource_Campaigns.php 21 2013-03-15 19:35:01Z ntemple $
 * @package IcApi
 */


/**
 * This class represents the Campaigns resource in the IntelliContact API.
 *
 * @author jtravis@broadwick.com
 */
class IcResource_Campaigns extends IcResource {
	
	private $campaigns;
	
	public function __construct() {
		parent::__construct();
		$this->Campaigns = array();
	}
	
	public function processChild(DOMDocument $child_xml, $childname) {
		if($childname != "Campaign") return;
		
		$campaign = new IcResource_Campaign();
		$campaign->setXml($child_xml);
		$this->Campaigns[] = $campaign;
	}
	
	public function getLocation() {
		return "{$this->getName()}";
	}
	
	public function getName() {
		return "campaigns";
	}
	
	public function getCampaigns() {
		return $this->campaigns;
	}
}

?>
