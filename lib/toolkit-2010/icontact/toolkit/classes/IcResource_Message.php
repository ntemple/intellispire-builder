<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  
 * 
 * @package IcApi
 */


/**
 * This class represents the message resource in the IntelliContact API.
 *
 * @author acox@broadwick.com
 */
class IcResource_Message extends IcResource {
	
	private $message_id;
	private $what_to_get;
	
	public function __construct() {
		parent::__construct();
		$this->what_to_get = null;
	}
	
	/**
	 * @param DOMDocument $xml
	 */
	public function setXml($xml) {
		parent::setXml($xml);
		$nodelist = $this->xml->getElementsByTagName("message");
		if($nodelist->length === 0) {
			throw new Exception("Couldn't locate message resource in xml");
		}
		$message = $nodelist->item(0);
		$id = $message->getAttributeNode("id")->nodeValue;
		$this->setMessageId($id);
	}
	
	/**
	 * Returns the message id
	 *
	 * @return int
	 */
	public function getMessageId() {
		return (int) $this->message_id;
	}

	/**
	 * Returns the location of this resource (ie, the part of the URL that
	 * IcApi needs)
	 *
	 * @return string
	 */
	public function getLocation() {
		if($this->what_to_get) {
			return "{$this->getName()}/{$this->getMessageId()}/{$this->what_to_get}";
		} elseif($this->message_id) {
			return "{$this->getName()}/{$this->getMessageId()}";
		} else {
			return "{$this->getName()}";
		}
	}
	
	/**
	 * Returns the name of this resource
	 *
	 * @return string
	 */
	public function getName() {
		return "message";
	}
	
	/**
	 * Sets the message id
	 *
	 * @param int $message_id
	 */
	public function setMessageId($message_id) {
		$this->message_id = (int) $message_id;
	}

	public function getSummary() {
		$this->what_to_get = "";
	}

	public function getStats($stat_type = '') {
		$this->what_to_get = "stats";
		if (strlen($stat_type) > 0) {
			switch ($stat_type) {
				case "opens":
					$this->what_to_get .= "/opens";
					break;
				case "clicks":
					$this->what_to_get .= "/clicks";
					break;
				case "bounces":
					$this->what_to_get .= "/bounces";
					break;
				case "unsubscribes":
					$this->what_to_get .= "/unsubscribes";
					break;
				case "forwards":
					$this->what_to_get .= "/forwards";
					break;
			}
		}
	}

	/**
	 * Specifies that we want to get sent statistics about this
	 * message, and not just the message properties
	 *
	 */
	public function getSendingInfo() {
		$this->what_to_get = "sending_info/summary";
	}

	public function setSendingInfo($lists, $segments=array(), $feeds=array(), $archive=true, $time="now") {
		$this->what_to_get = "sending_info/summary";

		$xml = new DOMDocument();
		$message = $xml->createElement("message");
		$xml->appendChild($message);
		$message->setAttribute("id",$this->getMessageId());
		$sending_info = $xml->createElement("sending_info");
		$channels = $xml->createElement("channels");
		$channels->setAttribute("archive",$archive ? "true" : "false");

		$time = date("r",strtotime($time));
		$sending_info->setAttribute("time",$time);

		if(!is_array($lists)) {
			$lists = array($lists);
		}

		if(!is_array($segments)) {
			$segments = array($segments);
		}

		if(!is_array($feeds)) {
			$feeds = array($feeds);
		}

		foreach($lists as $list) {
			$channel = $xml->createElement("list");
			$channel->setAttribute("id",$list);
			$channels->appendChild($channel);
		}

		foreach($segments as $segment) {
			$channel = $xml->createElement("segment");
			$channel->setAttribute("id",$segment);
			$channels->appendChild($channel);
		}

		foreach($feeds as $feed) {
			$channel = $xml->createElement("feed");
			$channel->setAttribute("id",$feed);
			$channels->appendChild($channel);
		}

		$sending_info->appendChild($channels);
		$message->appendChild($sending_info);
		$this->setXml($xml);
	}

	public function newMessage($subject, $campaign, $text_body, $html_body=null) {
		$this->what_to_get = null;

		$xml = new DOMDocument();
		$message = $xml->createElement("message");
		$xml->appendChild($message);
		$subject = $xml->createElement("subject",$subject);
		$message->appendChild($subject);
		$campaign = $xml->createElement("campaign",$campaign);
		$message->appendChild($campaign);
		$text_body = $xml->createElement("text_body",$text_body);
		$message->appendChild($text_body);
		if($html_body) {
			$html_body = $xml->createElement("html_body", $html_body); 
			$message->appendChild($html_body);
		}
		
		$this->setXml($xml);
	}
}

?>
