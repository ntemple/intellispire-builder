<?php
/**
 * This file contains the code for the SQS client.
 *
 * Copyright 2006-2007 Intellispire.
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0 
 *
 * Unless required by applicable law or agreed to in writing, 
 * software distributed under the License is distributed on an 
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, 
 * either express or implied. See the License for the specific 
 * language governing permissions and limitations under the License.  
 *
 * @category   Web Services
 * @package    SQS 
 * @author     Nick Temple <Nick.Temple@intellispire.com>  Original Author
 * @copyright  2006 Nick Temple
 * @license    http://www.intellispire.com/license.html 
 * @link       http://www.intellispire.com/
 */

/**
 * The Simple Queue Service.
 *
 * All functions return the result or TRUE on success,
 * NULL or false on failure.
 *
 * You can check the exact status using $SQS->statuscode, which should be "Success".
 * for successful transactions.
 * The last requestid and errormsg are also stored.
 *
 * This implementation automatically stores the activeQueue, which can be change
 * by calling createQueue to get a new (or existing queue), or setActiveQueue if you
 * already have queue URL.
 *
 * The permissions system has not been tested.
 */

require_once('aws.class.php');

class SQS extends AWS {

  var $access_key;
  var $secret;
  var $activeQueue;

  // Results
  var $statuscode;
  var $requestid;
  var $erromsg;
  
  function SQS($a, $s) {
    $this->access_key = $a;
    $this->secret = $s;
  }

  function setActiveQueue($q) {
    $this->activeQueue = $q;
  }

  function ListQueues($QueueNamePrefix = '') {
     $params = array();
     if ($QueueNamePrefix != '') {
       $params['QueueNamePrefix'] = $QueueNamePrefix;
     }
     $result = $this->_call('ListQueues', $params);
     if ($this->statuscode != 'Success') return NULL; 
     return $result['QUEUEURL'];
  }

  function CreateQueue($QueueName, $setActive = true, $DefaultVisibilityTimeout = '') {
     $params = array();
     if ($DefaultVisibilityTimeout != '') {
       $params['DefaultVisibilityTimeout'] = $DefaultVisibilityTimeout;
     }

     $params['QueueName'] = $QueueName;
     $result = $this->_call('CreateQueue', $params); 
     $q = $result['QUEUEURL'];
     if ($this->statuscode != 'Success') return NULL;
     if ($q && $setActive) $this->activeQueue = $q;
     return $q;
  }    

  function DeleteQueue($QueueName) {
     $params = array();
     
     $oldActiveQueue = $this->activeQueue;
     $this->activeQueue = $QueueName;
     $result = $this->_call('DeleteQueue', $params);
     $this->activeQueue = $oldActiveQueue;
     if ($this->statuscode != 'Success') return false;
     return true;
  }

  function SendMessage($MessageBody ) {
     $params = array();

     $params['MessageBody'] = $MessageBody;
     $result = $this->_call('SendMessage', $params, $this->activeQueue);
     if ($this->statuscode != 'Success') return NULL;
     return $result['MESSAGEID'];

  }

  // Returns 0 or 1 messages
  function ReceiveMessage($VisibilityTimeout = -1) {
    $params = array();
    if ($VisibilityTimeout > -1) $params['VisibilityTimeout'] = $VisibilityTimeout;
    $result = $this->_call('ReceiveMessage', $params, $this->activeQueue);
    if ($this->statuscode != 'Success') return NULL; 
    return $result; 
  }

  // Returns 0 or more messages, formatted an an array of messages
  function ReceiveMessages($NumberOfMessages = -1, $VisibilityTimeout = -1) {
    $params = array();
    if ($NumberOfMessages  > 0)  $params['NumberOfMessages'] = $NumberOfMessages;
    if ($VisibilityTimeout > -1) $params['VisibilityTimeout'] = $VisibilityTimeout;
    $result = $this->_call('ReceiveMessage', $params, $this->activeQueue);
    if ($this->statuscode != 'Success') return NULL;
 
    $id   = $result['MESSAGEID'];
    $body = $result['MESSAGEBODY'];
    $messages = array();

    if (is_array($id)) {
      $count = 0;
  
 
      for ($i = 0; $i < count($id); $i++) {
        $msg = array();
        $msg['MESSAGEID']   = $id[$i];
        $msg['MESSAGEBODY'] = $body[$i];
        $messages[] = $msg;
      }
    } else {
      // Only 1 message, but we want it as part of an array
      $messages[] = $result;
    }
    return $messages; 
 
  }

  function DeleteMessage($MessageId) {
     $params = array();

     $params['MessageId'] = $MessageId;
     $result= $this->_call('DeleteMessage', $params, $this->activeQueue);
     if ($this->statuscode != 'Success') return false;
     return true;
  }

  function PeekMessage($MessageId) {
     $params = array();

     $params['MessageId'] = $MessageId;
     $result = $this->_call('PeekMessage', $params, $this->activeQueue);
     if ($this->statuscode != 'Success') return NULL;
     return $result;
  }

 /**
   * Set queue visibility
   *
   * According to the docs, it is possible to set the visibility on a 
   * per message basis.  The docs are inaccurate, it can only be done per queue. 
   *
   */

  function SetVisibilityTimeout($VisibilityTimeout /*, $MessageId = '' */) {
    $params = array();
    // if ($MessageId != '') $params['MessageId'] = $MessageId;
    $params['VisibilityTimeout'] = $VisibilityTimeout;
    $this->_call('SetVisibilityTimeout', $params, $this->activeQueue);
    if ($this->statuscode != 'Success') return NULL;
    return true;
  }

  function GetVisibilityTimeout() {
    $params = array();
    $result = $this->_call('GetVisibilityTimeout', $params, $this->activeQueue);
    if ($this->statuscode != 'Success') return NULL;
    return ($result['VISIBILITYTIMEOUT']); 
  }

  // [RECEIVEMESSAGE, FULLCONTROL, SENDMESSAGE]
  function AddGrant($Grantee, $Permission = 'RECEIVEMESSAGE', $MessageId = '') {
    $params = array();
    $params['Grantee.EmailAddress'] = $Grantee;
    $params['Permission'] = $Permission;
    if ($MessageId != '') $params['MessageId'] = $MessageId;
    $result = $this->_call('AddGrant', $params, $this->activeQueue);
    if ($this->statuscode != 'Success') return NULL; 
    return $this->errorcode;

   }
     
  function RemoveGrant($Grantee, $Permission = 'RECEIVEMESSAGE', $MessageId = '') {
    $params = array();
    $params['Grantee.EmailAddress'] = $Grantee;
    $params['Permission'] = $Permission;
    if ($MessageId != '') $params['MessageId'] = $MessageId;
    $result = $this->_call('RemoveGrant', $params, $this->activeQueue);
    if ($this->statuscode != 'Success') return NULL; 
    return true;
  }
 
  // [RECEIVEMESSAGE, FULLCONTROL, SENDMESSAGE]
  function ListGrants($Grantee, $Permission = '') {
    $params = array();
    $params['Grantee.EmailAddress'] = $Grantee;
    if($Permission != '') $params['Permission'] = $Permission;
    $result = $this->_call('ListGrants', $params, $this->activeQueue);
    if ($this->statuscode != 'Success') return NULL;
    return $result;
  }
   
  function _call($action, &$params, $q = '') {
    if ($params == '') {
      $params = array();
    }
    
    // Add Actions
    $params['Action'] = $action;
    $params['Version'] = '2006-04-01';
    $params['AWSAccessKeyId'] = $this->access_key;
    $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');   

    // Sign the string
    $string_to_sign = $params['Action'] . $params['Timestamp'];
    $params['Signature'] =  $this->hex2b64($this->hasher($string_to_sign));

    $this->endpoint = "http://queue.amazonaws.com/";

    if ($action == 'DeleteQueue') {
      $this->endpoint = $this->activeQueue;
    } else
    {
      if ( strpos($q, ':') === false ) {
        $this->endpoint .= $q;
      } else {
        $this->endpoint = $q;
      }
    }

    $result = $this->_call2($action, $params, $q); 
    return $result;
  }

}
?>
