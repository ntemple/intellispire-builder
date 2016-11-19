<?php

/*
 * Updates a contact
 */

require_once '../config.php';
require_once '../util.php';

/***********************************/
/** set your account details here **/
/***********************************/
$accountId      = 1001413;
$clientFolderId = 9196527;
$contactId      = 1049733100;

$response = callResource("/a/{$accountId}/c/{$clientFolderId}/contacts/{$contactId}",
	'POST', array(
		'firstName' => 'John',
		'lastName'  => 'Doe',
	));

if ($response['code'] == STATUS_CODE_SUCCESS) {
	echo "<h1>Success</h1>\n";
	
	$contact = $response['data']['contact'];
	
	$warningCount = 0;
	if (!empty($response['data']['warnings'])) {
		$warningCount = count($response['data']['warnings']);
	}
	
	echo "<p>Updated contact {$contactId}, with {$warningCount} warnings.</p>\n";
	
	dump($response['data']);
} else {
	echo "<h1>Error</h1>\n";
	
	echo "<p>Error Code: {$response['code']}</p>\n";
	
	dump($response['data']);
}

?>