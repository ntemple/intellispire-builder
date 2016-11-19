#!/usr/bin/php
<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  The most we can promise is that using
 * this won't cause God to kill kittens.
 * 
 * @version $Id: sentstats.php 21 2013-03-15 19:35:01Z ntemple $
 * @package IcMain
 */

/**
 * This includes important things such as our autoloader
 */
include 'include.php';

$api = new IcApi("http://api.intellicontact.com/icp");
$api->setVersion("1.0");
$api->setKey("");
$api->setSecret("");
$api->setLogin("");
$api->setPassword("");
$api->setDebug(true);

$messages = new IcResource_Messages();
$messages->filterBy("sent");
$api->get($messages);

$api->follow($messages,"message",1);

$message_list = $messages->getMessages();

foreach ($message_list as $amessage) {
	$api->follow($amessage,"sentstats",1);
	echo $amessage->getXml()->saveXml();
}

?>
