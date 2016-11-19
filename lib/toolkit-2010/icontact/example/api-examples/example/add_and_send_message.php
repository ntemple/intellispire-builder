<?php

/*
 * Adds and sends a message to a list
 */

require_once '../config.php';
require_once '../util.php';

/***********************************/
/** set your account details here **/
/***********************************/
$accountId      = 1001413;
$clientFolderId = 9196527;
$campaignId     = 53917;
$listId         = 8485;

$messageId = addMessage();
sendMessageToList($messageId, $listId);

function addMessage()
{
	global $accountId, $clientFolderId, $campaignId;
	
	$messageId = null;
	
	$response = callResource("/a/{$accountId}/c/{$clientFolderId}/messages",
		'POST', array(
			array(
				'campaignId'  => $campaignId,
				'subject'     => 'Test Message',
				'messageType' => 'normal',
				'textBody'    => 'Hello, World!',
				'htmlBody'    => '<h1>Hello</h1><p>Hello, World!</p>',
			)
		));
	
	if ($response['code'] == STATUS_CODE_SUCCESS) {
		echo "<h1>Success - Add Message</h1>\n";
		
		$messageId = $response['data']['messages'][0]['messageId'];
		
		$warningCount = 0;
		if (!empty($response['data']['warnings'])) {
			$warningCount = count($response['data']['warnings']);
		}
		
		echo "<p>Added message {$messageId}, with {$warningCount} warnings.</p>\n";
		
		dump($response['data']);
	} else {
		echo "<h1>Error - Add Message</h1>\n";
		
		echo "<p>Error Code: {$response['code']}</p>\n";
		
		dump($response['data']);
	}
	
	return $messageId;
}

function sendMessageToList($messageId, $listId)
{
	global $accountId, $clientFolderId;
	
	$response = callResource("/a/{$accountId}/c/{$clientFolderId}/sends",
		'POST', array(
			array(
				'messageId'      => $messageId,
				'includeListIds' => $listId,
			)
		));
	
	if ($response['code'] == STATUS_CODE_SUCCESS) {
		echo "<h1>Success - Send Message</h1>\n";
		
		$sends = $response['data']['sends'];
		$sent = !empty($sends);
		if ($sent) {
			$recipientCount = $sends[0]['recipientCount'];
		}
		
		$warningCount = 0;
		if (!empty($response['data']['warnings'])) {
			$warningCount = count($response['data']['warnings']);
		}
		
		if ($sent) {
			echo "<p>The message was sent to {$recipientCount} recipients, with {$warningCount} warnings.</p>\n";
		} else {
			echo "<p>The message was not sent, see the {$warningCount} warnings below.</p>\n";
		}
		
		dump($response['data']);
	} else {
		echo "<h1>Error - Send Message</h1>\n";
		
		echo "<p>Error Code: {$response['code']}</p>\n";
		
		dump($response['data']);
	}
}

?>