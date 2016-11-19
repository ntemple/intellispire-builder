#!/usr/bin/php
<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  The most we can promise is that using
 * this won't cause God to kill kittens.
 * 
 * @version $Id: main.php 21 2013-03-15 19:35:01Z ntemple $
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

/*
 * Gets info for certain campaign
$campaign = new IcResource_Campaign();
$campaign->setCampaignId(52430);
$api->get($campaign);
//echo $campaign->getXml()->saveXml();
 */

/*
 * Gets list of Campaigns
$campaigns = new IcResource_Campaigns();
$api->get($campaigns);
//echo $campaigns->getXml()->saveXml();
 */

/*
 * Get list info
$list = new IcResource_List();
$list->setListId(6292);
$api->get($list);
 */

/*
 * Gets a list of your lists and info for up to 3 of them
$lists = new IcResource_Lists();
$api->get($lists);
//echo $lists->getXml()->saveXml();
$api->follow($lists,'list',3);
 */

/*
 * Get a message
$message = new IcResource_Message();
$message->setMessageId(95655);
$api->get($message);
 */

/*
 * Get a message summary
$message = new IcResource_Message();
$message->setMessageId(95656);
$message->getSendingInfo();
$api->get($message);
 */

/*
 * Get a message stats
$message = new IcResource_Message();
$message->setMessageId(95656);
$message->getStats('opens');
$api->get($message);
 */

/*
 * Creates a message
$new_message = new IcResource_Message();
$new_message->newMessage("Subject",50838,"text","html");
$response = $api->put($new_message);
 */

/*
 * Gets a list of contacts and info for up to 6
$contacts = new IcResource_Contacts();
$api->get($contacts);
$api->follow($contacts,'contact',6);
 */

/*
 * Get a contacts info
$contact = new IcResource_Contact();
$contact->setContactId(1109793);
$result = $api->get($contact);
 */

/*
 * Get a contact subscriptions info
 */
$contact = new IcResource_Contact();
$contact->setContactId(42605);
$contact->getSubscriptions();
$contact->getContact(42605);
die();
$result = $api->get($contact);

/*
 * Creating a new contact and double check the contact got set right
 */
$new_contact = new IcResource_Contact();
$new_contact->updateContact(42605,"test@broadwick.com",'john','doe','mebus','','','123 miami','','miami','fl','12345','1112221212');
$response = $api->put($new_contact);
$new_contact->setXml($response->getXml());
$api->follow($new_contact,'contact',1);
echo $new_contact->getXml()->saveXml();

/*
 * Subscribes a contact to a list
 */
$contact = new IcResource_Contact();
$contact->setContactId(42605);
$contact->newSubscription(3204,'subscribed');
$contact->putSubscription();
$response = $api->put($contact);

/*
 * Get a contact subscriptions info
 */
$contact = new IcResource_Contact();
$contact->setContactId(42605);
$contact->getSubscriptions();
$result = $api->get($contact);

/*
 * Subscribes a contact to a list
$contact = new IcResource_Contact();
$contact->setContactId(1107599);
$contact->getCustomFields();
$response = $api->get($contact);
 */

?>
