<?php

define('STATUS_CODE_SUCCESS', 200);

function callResource($url, $method, $data = null)
{
	$url    = $GLOBALS['config']['apiUrl'] . $url;
	$handle = curl_init();
	
	$headers = array(
		'Accept: application/json',
		'Content-Type: application/json',
		'Api-Version: 2.0',
		'Api-AppId: ' . $GLOBALS['config']['appId'],
		'Api-Username: ' . $GLOBALS['config']['username'],
		'Api-Password: ' . $GLOBALS['config']['password'],
	);
	
	curl_setopt($handle, CURLOPT_URL, $url);
	curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
	
	switch ($method) {
		case 'POST':
			curl_setopt($handle, CURLOPT_POST, true);
			curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($data));
		break;
		case 'PUT':
			curl_setopt($handle, CURLOPT_PUT, true);
			$file_handle = fopen($data, 'r');
			curl_setopt($handle, CURLOPT_INFILE, $file_handle);
		break;
		case 'DELETE':
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
		break;
	}
	
	$response = curl_exec($handle);
	$response = json_decode($response, true);
	$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	
	curl_close($handle);
	
	return array(
		'code' => $code,
		'data' => $response,
	);
}

function dump($array)
{
	echo "<pre>" . print_r($array, true) . "</pre>";
}

?>