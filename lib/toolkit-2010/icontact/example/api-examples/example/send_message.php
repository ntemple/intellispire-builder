<?php

/*
 * Sends a message to a list
 */

require_once '../config.php';
require_once '../util.php';

/***********************************/
/** set your account details here **/
/***********************************/
$accountId      = 1001413;
$clientFolderId = 9196527;
$messageId      = 206870;
$listId         = 8485;

$response = callResource("/a/{$accountId}/c/{$clientFolderId}/sends",
	'POST', array(
		array(
			'messageId'      => $messageId,
			'includeListIds' => $listId,
		)
	));

if ($response['code'] == STATUS_CODE_SUCCESS) {
	echo "<h1>Success</h1>\n";
	
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
	echo "<h1>Error</h1>\n";
	
	echo "<p>Error Code: {$response['code']}</p>\n";
	
	dump($response['data']);
}

?>