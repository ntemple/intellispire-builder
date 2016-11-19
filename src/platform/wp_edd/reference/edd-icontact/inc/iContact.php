<?php

class iContact {

	const STATUS_CODE_SUCCESS = 200;
	private $apiUrl;
	private $username;
	private $password;
	private $appId;
	private $accountId;
	private $clientFolderId;
	private $debugMode;
	
	/**
	 *
	 * @param type $apiUrl
	 * @param type $username
	 * @param type $password
	 * @param type $appId
	 * @param type $accountId
	 * @param type $clientFolderId
	 * @param type $debugMode 
	 */
	public function __construct($apiUrl, $username, $password, $appId, $accountId, $clientFolderId = null, $debugMode = false) {
		$this->apiUrl = $apiUrl;
		$this->username = $username;
		$this->password = $password;
		$this->appId = $appId;
		$this->accountId = $accountId;
		$this->clientFolderId = $clientFolderId;
		$this->debugMode = $debugMode;
	}
	
	public function setClientFolderId($clientFolderId) {
		$this->clientFolderId = $clientFolderId;
	}
	
	public function setDebugeMode($debugMode) {
		$this->debugMode = $debugMode;
	}
	
	/**
	 * Create multiple contacts
	 * @param array $contacts contains list of contacts with 'email', 'firstName', 'lastName'
	 * @return array contactIds
	 */
	public function createContacts($contacts) {
		$contactIds = null;

		$response = $this->callResource("/a/{$this->accountId}/c/{$this->clientFolderId}/contacts",'POST', $contacts);
		
		if ($response['code'] == self::STATUS_CODE_SUCCESS) {
			if(count($response['data']['contacts']) > 0) {
				foreach($response['data']['contacts'] as $contact) {
					$contactIds[$contact['email']] = $contact['contactId'];
				}
			}
		} else {
			throw new Exception('iContact returned ' . $response['code']);
		}
		if($this->debugMode) $this->dump($response);

		return $contactIds;
	}
	
	/**
	 * Create a contact
	 * @param string $email
	 * @param string $firstName
	 * @param string $lastName
	 * @return string $contactId
	 */	
	public function createContact($email, $firstName = null, $lastName = null) {
		$result = $this->createContacts(array(array('email'=>$email, 'firstName'=>$firstName, 'lastName'=>$lastName)));
		if(is_null($result)) 
			$contactId = null;
		else
			$contactId = array_shift($result);
		
		return $contactId;
	}
	
	/**
	 * Delete a contact
	 * @param string $contactId
	 * @return boolean $success
	 */
	public function deleteContact($contactId) {
		$success = false;
		$response = $this->callResource("/a/{$this->accountId}/c/{$this->clientFolderId}/contacts/$contactId",'DELETE');

		if ($response['code'] == self::STATUS_CODE_SUCCESS) {
			$success = true;
		} else {
			throw new Exception('iContact returned ' . $response['code']);
		}
		if($this->debugMode) $this->dump($response);
		return $success;		
	}
	/**
	 * Create one or more lists
	 * @param array $lists
	 * @return string listId of new created list
	 */
	public function createLists($lists) {
		$listIds = null;
		$this->addDefaultsToLists($lists);
		$response = $this->callResource("/a/{$this->accountId}/c/{$this->clientFolderId}/lists",'POST', $lists);

		if ($response['code'] == self::STATUS_CODE_SUCCESS) {
			if(count($response['data']['lists']) > 0) {
				foreach($response['data']['lists'] as $list) {
					$listIds[$list['name']] = $list['listId'];
				}				
			}
		} else {
			throw new Exception('iContact returned ' . $response['code']);
		}
		if($this->debugMode) $this->dump($response);
		return $listIds;		
	}
	
	/**
	 * Create a list
	 * @param string $listName
	 * @param string $welcomeMessageId
	 * @param bool $emailOwnerOnChange
	 * @param bool $welcomeOnManualAdd
	 * @param bool $welcomeOnSignupAdd
	 * @return string listId 
	 */
	public function createList($listName, $welcomeMessageId, $emailOwnerOnChange = 0, $welcomeOnManualAdd = 0, $welcomeOnSignupAdd = 0) {
		$result = $this->createLists(array(array(
			'name'					=> $listName, 
			'welcomeMessageId'		=> $welcomeMessageId, 
			'emailOwnerOnChange'	=> $emailOwnerOnChange,
			'welcomeOnManualAdd'	=> $welcomeOnManualAdd,
			'welcomeOnSignupAdd'	=> $welcomeOnSignupAdd
		)));
		
		if(is_null($result))
			$listId = null;
		else
			$listId = array_shift($result);
		
		return $listId;		
	}

	/**
	 * Delete a list
	 * @param string $listId
	 * @return boolean $success
	 */
	public function deleteList($listId) {
		$success = false;
		$response = $this->callResource("/a/{$this->accountId}/c/{$this->clientFolderId}/lists/$listId",'DELETE');

		if ($response['code'] == self::STATUS_CODE_SUCCESS) {
			$success = true;
		} else {
			throw new Exception('iContact returned ' . $response['code']);
		}
		if($this->debugMode) $this->dump($response);
		return $success;
	}	
	
	/**
	 * Get an array containing all available lists
	 * @return array 
	 */
	public function getLists() {
		$lists;
		$response = $this->callResource("/a/{$this->accountId}/c/{$this->clientFolderId}/lists/$listId",'GET');
		if ($response['code'] == self::STATUS_CODE_SUCCESS) {
			$lists = $response['data']['lists'];
		} else {
			throw new Exception('iContact returned ' . $response['code']);
		}
		if($this->debugMode) $this->dump($response);
		return $lists;		
	}
	
	/**
	 * Add an array of contacts to a list
	 * @param string $listId
	 * @param array $contactIds 
	 */
	public function subscribeContactsToList($listId, $contactIds) {
		if(!is_array($contactIds) || count($contactIds) < 1) {
			throw new Exception('contactIds array is empty or invalid');
		}		
		foreach($contactIds as $contactId) {
			$contacts[] = array('contactId'=>$contactId, 'listId'=>$listId, 'status'=>'normal');
		}
		$response = $this->callResource("/a/{$this->accountId}/c/{$this->clientFolderId}/subscriptions", 'POST', $contacts);

		if ($response['code'] != self::STATUS_CODE_SUCCESS) {
			throw new Exception('iContact returned ' . $response['code']);
		}
		if($this->debugMode) $this->dump($response);
	}
	
	/**
	 * Send an email to a list or lists
	 * @param String $messageId
	 * @param String $listId can be a comma seperated list of listIds
	 */
	public function sendEmail($messageId, $listId) {
		$response = $this->callResource("/a/{$this->accountId}/c/{$this->clientFolderId}/sends",'POST', array(
			array (
				'messageId'			=> $messageId,
				'includeListIds'	=> $listId,
			)
		));
		if ($response['code'] != self::STATUS_CODE_SUCCESS) {
			throw new Exception('iContact returned ' . $response['code']);
		}
		if($this->debugMode) $this->dump($response);		
	}
	/**
	 * Function to make the curl request
	 * @param string $url
	 * @param type $method
	 * @param type $data
	 * @return type 
	 */
	protected function callResource($url, $method, $data = null) {
		$url = $this->apiUrl . $url;
		$handle = curl_init();

		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Api-Version: 2.0',
			'Api-AppId: ' . $this->appId,
			'Api-Username: ' . $this->username,
			'Api-Password: ' . $this->password,
		);

		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

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
			case 'GET':
				break;
			default:
				throw new Exception("$method is not a supported method");
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
	
	private function dump($array) {
		echo "<pre>" . print_r($array, true) . "</pre>";
	}
	
	private function addDefaultsToLists(&$lists) {
		foreach($lists as $list) {
			$list['emailOwnerOnChange']	= 0;
			$list['welcomeOnManualAdd']	= 0;
			$list['welcomeOnSignupAdd']	= 0;		
		}
	}
	
	private function addDefaultsToList(&$list) {
		addDefaultsToLists($list);	
	}
}

?>