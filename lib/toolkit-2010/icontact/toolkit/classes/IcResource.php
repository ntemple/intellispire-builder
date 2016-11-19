<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  
 * 
 * @version $Id: IcResource.php 21 2013-03-15 19:35:01Z ntemple $
 * @package IcApi
 */

/**
 * This class represents a resource that lives on the IntelliContact servers.  Each resource that IntelliContact
 * supports has a class that extends this class.
 *
 * @author acox@broadwick.com
 */
abstract class IcResource {

	/**
	 * The XML that represents this resource
	 *
	 * @var DOMDocument
	 */
	protected $xml;
	
	public function __construct() {
		$this->xml = null;
	}
	
	/**
	 * This returns the location of the resource that is being accessed.
	 * For instance, a book with ID #123 might return book/123.  If the
	 * remote API supported it, this function might even return something
	 * like book/123/title or book/123/publisherid
	 * 
	 * @return string
	 */
	abstract public function getLocation();

	/**
	 * This returns the name of this resource.
	 * 
	 * @return string
	 */
	abstract public function getName();

	/**
	 * This takes the XML that is returned by the IntelliContact API
	 * and stores in within this resource.
	 *
	 * @param DOMDocument $child_xml The xml that is returned by the IntelliContact API
	 * @param string $childname The name of the child that was followed
	 */
	public function processChild(DOMDocument $child_xml, $childname) {
		$xpath = new DOMXPath($this->xml);
		$nodelist = $xpath->query("//$childname");
		
		if($nodelist->length == 0) {
			throw new Exception("Can't follow resource to $childname");
		}
		
		$oldnode = $nodelist->item(0);
		$oldparent = $oldnode->parentNode;
		
		$xpath = new DOMXPath($child_xml);
		$nodelist = $xpath->query("//$childname");
		
		if($nodelist->length == 0) {
			throw new Exception("Can't follow resource to $childname");
		}
		
		$newnode = $nodelist->item(0);
		$newparent = $newnode->parentNode;
		
		$parent_name_new = $newparent->nodeName;
		$parent_name_old = $oldparent->nodeName;

		$oldparent->removeChild($oldnode);
		$oldparent->appendChild($this->xml->importNode($newnode,true));
	}
	
	/**
	 * Returns the xml that represents this object
	 *
	 * @return DOMDocument
	 */
	public function getXml() {
		return $this->xml;
	}
	
	/**
	 * Sets the xml that represents this object
	 *
	 * @param DOMDocument $xml
	 */
	public function setXml(DOMDocument $xml) {
		$this->xml = $xml;
	}
	
	public function getFilterBy() {
		return null;
	}

}

?>
