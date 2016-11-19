<?php

/*
 * Gets the history of actions a contact has performed
 */

require_once '../config.php';
require_once '../util.php';

/***********************************/
/** set your account details here **/
/***********************************/
$accountId      = 1001413;
$clientFolderId = 9196527;
$contactId      = 1049733100;

$response = callResource("/a/{$accountId}/c/{$clientFolderId}/contacts/{$contactId}/actions", 'GET');

if ($response['code'] == STATUS_CODE_SUCCESS) {
	echo "<h1>Success</h1>\n";
	
	$actions = $response['data']['actions'];
	$count    = count($actions);
	$total    = $response['data']['total'];
	
	$warningCount = 0;
	if (!empty($response['data']['warnings'])) {
		$warningCount = count($response['data']['warnings']);
	}
	
	echo "<p>Got {$count} of {$total} actions, with {$warningCount} warnings.</p>\n";
	
	dump($response['data']);
} else {
	echo "<h1>Error</h1>\n";
	
	echo "<p>Error Code: {$response['code']}</p>\n";
	
	dump($response['data']);
}

?>