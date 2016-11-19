<?php

/*
 * Uploads a set of contacts
 */

require_once '../config.php';
require_once '../util.php';

/***********************************/
/** set your account details here **/
/***********************************/
$accountId      = 1001413;
$clientFolderId = 9196527;
$listId         = 8485;

$uploadId = createUpload();
uploadData($uploadId, '../data/upload.csv');
$status = getUpload($uploadId);
while ($status != 'complete') {
	$status = getUpload($uploadId);
	sleep(1);
}

function createUpload()
{
	global $accountId, $clientFolderId, $listId;
	
	$uploadId = null;
	
	$response = callResource("/a/{$accountId}/c/{$clientFolderId}/uploads",
		'POST', array(
			array(
				'action' => 'add',
				'lists'  => array($listId),
			)
		));
	
	if ($response['code'] == STATUS_CODE_SUCCESS) {
		echo "<h1>Success - Create Upload</h1>\n";
		
		$uploadId = $response['data']['uploadId'];
		
		$warningCount = 0;
		if (!empty($response['data']['warnings'])) {
			$warningCount = count($response['data']['warnings']);
		}
		
		echo "<p>Added upload {$uploadId}, with {$warningCount} warnings.</p>\n";
		
		dump($response['data']);
	} else {
		echo "<h1>Error - Create Upload</h1>\n";
		
		echo "<p>Error Code: {$response['code']}</p>\n";
		
		dump($response['data']);
	}
	
	return $uploadId;
}

function uploadData($uploadId, $file)
{
	global $accountId, $clientFolderId;
	
	$response = callResource("/a/{$accountId}/c/{$clientFolderId}/uploads/{$uploadId}/data",
		'PUT', $file);
	
	if ($response['code'] == STATUS_CODE_SUCCESS) {
		echo "<h1>Success - Upload Data</h1>\n";
		
		$uploadId = $response['data']['uploadId'];
		
		$warningCount = 0;
		if (!empty($response['data']['warnings'])) {
			$warningCount = count($response['data']['warnings']);
		}
		
		echo "<p>Updated upload {$uploadId}, with {$warningCount} warnings.</p>\n";
		
		dump($response['data']);
	} else {
		echo "<h1>Error - Upload Data</h1>\n";
		
		echo "<p>Error Code: {$response['code']}</p>\n";
		
		dump($response['data']);
	}
}


function getUpload($uploadId)
{
	global $accountId, $clientFolderId;
	
	$status = null;
	
	$response = callResource("/a/{$accountId}/c/{$clientFolderId}/uploads/{$uploadId}", 'GET');
	
	if ($response['code'] == STATUS_CODE_SUCCESS) {
		echo "<h1>Success - Get Upload</h1>\n";
		
		$status = $response['data']['status'];
		
		$warningCount = 0;
		if (!empty($response['data']['warnings'])) {
			$warningCount = count($response['data']['warnings']);
		}
		
		echo "<p>Added upload {$uploadId}, with {$warningCount} warnings.</p>\n";
		
		dump($response['data']);
	} else {
		echo "<h1>Error - Get Upload</h1>\n";
		
		$status = 'complete';
		
		echo "<p>Error Code: {$response['code']}</p>\n";
		
		dump($response['data']);
	}
	
	return $status;
}

?>