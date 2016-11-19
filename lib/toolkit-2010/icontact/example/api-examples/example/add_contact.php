<?php

/*
 * Adds a contact
 */

require_once '../config.php';
require_once '../util.php';

/***********************************/
/** set your account details here **/
/***********************************/
$accountId      = 1001413;
$clientFolderId = 9196527;

$response = callResource("/a/{$accountId}/c/{$clientFolderId}/contacts",
	'POST', array(
		array(
			'firstName' => 'John',
			'lastName'  => 'Doe',
			'email'     => 'john.doe-' . uniqid() . '@example.com',
		)
	));

if ($response['code'] == STATUS_CODE_SUCCESS) {
	echo "<h1>Success</h1>\n";
	
	$contactId = $response['data']['contacts'][0]['contactId'];
	
	$warningCount = 0;
	if (!empty($response['data']['warnings'])) {
		$warningCount = count($response['data']['warnings']);
	}
	
	echo "<p>Added contact {$contactId}, with {$warningCount} warnings.</p>\n";
	
	dump($response['data']);
} else {
	echo "<h1>Error</h1>\n";
	
	echo "<p>Error Code: {$response['code']}</p>\n";
	
	dump($response['data']);
}

?>