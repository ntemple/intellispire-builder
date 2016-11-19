<?php

/*
 * Adds a contact and a list and then subscribes the contact to the list
 */

require_once '../config.php';
require_once '../util.php';

/***********************************/
/** set your account details here **/
/***********************************/
$accountId        = 1001413;
$clientFolderId   = 9196527;
$welcomeMessageId = 182863;

$contactId = addContact();
$listId    = addList();
subscribeContactToList($contactId, $listId);

function addContact()
{
	global $accountId, $clientFolderId;
	
	$contactId = null;
	
	$response = callResource("/a/{$accountId}/c/{$clientFolderId}/contacts",
		'POST', array(
			array(
				'firstName' => 'John',
				'lastName'  => 'Doe',
				'email'     => 'john.doe-' . uniqid() . '@example.com',
			)
		));
	
	if ($response['code'] == STATUS_CODE_SUCCESS) {
		echo "<h1>Success - Add Contact</h1>\n";
		
		$contactId = $response['data']['contacts'][0]['contactId'];
		
		$warningCount = 0;
		if (!empty($response['data']['warnings'])) {
			$warningCount = count($response['data']['warnings']);
		}
		
		echo "<p>Added contact {$contactId}, with {$warningCount} warnings.</p>\n";
		
		dump($response['data']);
	} else {
		echo "<h1>Error - Add Contact</h1>\n";
		
		echo "<p>Error Code: {$response['code']}</p>\n";
		
		dump($response['data']);
	}
	
	return $contactId;
}

function addList()
{
	global $accountId, $clientFolderId, $welcomeMessageId;
	
	$listId = null;
	
	$response = callResource("/a/{$accountId}/c/{$clientFolderId}/lists",
		'POST', array(
			array(
				'name' => 'my new list',
				'welcomeMessageId'   => $welcomeMessageId,
				'emailOwnerOnChange' => 0,
				'welcomeOnManualAdd' => 0,
				'welcomeOnSignupAdd' => 0,
			)
		));
	
	if ($response['code'] == STATUS_CODE_SUCCESS) {
		echo "<h1>Success - Add List</h1>\n";
		
		$listId = $response['data']['lists'][0]['listId'];
		
		$warningCount = 0;
		if (!empty($response['data']['warnings'])) {
			$warningCount = count($response['data']['warnings']);
		}
		
		echo "<p>Added list {$listId}, with {$warningCount} warnings.</p>\n";
		
		dump($response['data']);
	} else {
		echo "<h1>Error - Add List</h1>\n";
		
		echo "<p>Error Code: {$response['code']}</p>\n";
		
		dump($response['data']);
	}
	
	return $listId;
}

function subscribeContactToList($contactId, $listId)
{
	global $accountId, $clientFolderId, $welcomeMessageId;
	
	$response = callResource("/a/{$accountId}/c/{$clientFolderId}/subscriptions",
		'POST', array(
			array(
				'contactId' => $contactId,
				'listId'    => $listId,
				'status'    => 'normal',
			),
		));
	
	if ($response['code'] == STATUS_CODE_SUCCESS) {
		echo "<h1>Success - Subscribe Contact to List</h1>\n";
		
		$warningCount = 0;
		if (!empty($response['data']['warnings'])) {
			$warningCount = count($response['data']['warnings']);
		}
		
		echo "<p>Subscribed contact {$contactId} to list {$listId}, with {$warningCount} warnings.</p>\n";
		
		dump($response['data']);
	} else {
		echo "<h1>Error - Subscribe Contact to List</h1>\n";
		
		echo "<p>Error Code: {$response['code']}</p>\n";
		
		dump($response['data']);
	}
}

?>