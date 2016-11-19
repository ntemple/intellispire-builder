<?PHP
/**
* Amazon PHP Classes and Functions
*
* Copyright (c)2008 Intellispire 
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
* @category   Amazon
* @package    AWS
* @author     Nick Temple <Nick.Temple@intellispire.com>
* @copyright  2002-2008 Intellispire
* @license    MIT: http://www.opensource.org/licenses/mit-license.php
* @version    SVN: $Id: sqs.class.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*
* Requires PHP 5+
*
* Code updated with adaptions from: http://code.google.com/p/php-aws/
*
*/

/**
* The Simple Queue Service.
*
*/


require_once('aws.class.php');

class SQS extends AWS
{
    
    var $_server     = "http://queue.amazonaws.com/";
    var $_date       = null;
    var $_error      = null;
    
    var $queue_url;
    
    function SQS($key, $secret, $queue_url = null)
    {
        $this->queue_url = $queue_url;
        parent::__construct($key, $secret);
    }
    
    function createQueue($queue_name, $default_timeout = 30)
    {
        if ($default_timeout < 30) {
            $default_timeout = 30;
        }
        $params = array("QueueName" => $queue_name, "DefaultVisibilityTimeout" => $default_timeout);
        $xml = $this->go("CreateQueue", $params);
        if ($xml === false) {
            return false;
        }
        
        $q = strval($xml->CreateQueueResult->QueueUrl);
        $this->queue_url = $q;
        
        return $q;
    }
    
    function listQueues($queue_name_prefix = "")
    {
        $params = ($queue_name_prefix == "") ? array() : array("QueueNamePrefix" => $queue_name_prefix);
        $xml = $this->go("ListQueues", $params);
        if ($xml === false) {
            return false;
        }
        $out = array();
        foreach($xml->ListQueuesResult->QueueUrl as $url)
        $out[] = strval($url);
        return $out;
    }
    
    function deleteQueue($queue_url = null)
    {
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        $xml = $this->go("DeleteQueue", array(), $queue_url);
        return $xml ? true : false;
    }
    
    function sendMessage($message_body, $queue_url = null)
    {
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        $params = array("MessageBody" => $message_body);
        $xml = $this->go("SendMessage", $params, $queue_url);
        if ($xml === false) {
            return false;
        }
        
        return strval($xml->MessageId);
    }
    
    function receiveMessage($timeout = null, $queue_url = null)
    {
        $out =  $this->receiveMessages(1, $timeout, $queue_url);
        if (count($out) > 0) {
            return $out[0];
        } else {
            return $out;
        }
    }
    
    function receiveMessages($number = 1, $timeout = null, $queue_url = null)
    {
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        
        $number = intval($number);
        if ($number < 1) {
            $number = 1;
        }
        if ($number > 256) {
            $number = 256;
        }
        
        $params = array();
        $params['MaxNumberOfMessages'] = $number;
        if (isset($timeout)) {
            $params['VisibilityTimeout'] = intval($timeout);
        }
        
        $xml = $this->go("ReceiveMessage", $params, $queue_url);
        
        if ($xml === false) {
            return $false;
        }
        
        $out = array();
        
        foreach($xml->ReceiveMessageResult->Message as $m)
        $out[] = array("MessageId"     => strval($m->MessageId),
        "Body"          => strval($m->Body),
        "ReceiptHandle" => strval($m->ReceiptHandle),
        "MD5OfBody"     => strval($m->MD5OfBody)
        ) ;
        return $out;
    }
    
    function deleteMessage($receiptHandle)
    {
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        $params = array("ReceiptHandle" => $receiptHandle);
        $xml = $this->go("DeleteMessage", $params, $queue_url);
        return($xml === false) ? false : true;
    }
    
    function clearQueue($limit = 100, $queue_url = null)
    {
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        
        $m = $this->receiveMessages($limit, null, $queue_url);
        foreach($m as $n)
        $this->deleteMessage($n['ReceiptHandle'], $queue_url);
    }
    
    function setQueueAttributes($name, $value, $queue_url = null)
    {
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        $params = array("Attribute.Name" => $name, "Attribute.Value" => $value);
        $xml = $this->go("SetQueueAttributes", $params, $queue_url);
        return($xml === false) ? false : true;
    }
    
    function getQueueAttributes($name, $queue_url = null)
    {
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        $params = array("AttributeName" => $name);
        $xml = $this->go("GetQueueAttributes", $params, $queue_url);
        return($xml === false) ? false : strval($xml->GetQueueAttributesResult->Attribute->Value);
    }
    
    function setTimeout($timeout, $queue_url = null)
    {
        $timeout = intval($timeout);
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        if (!is_int($timeout)) {
            $timeout = 30;
        }
        return $this->setQueueAttributes("VisibilityTimeout", $timeout, $queue_url);
    }
    
    function getTimeout($queue_url = null)
    {
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        return $this->getQueueAttributes("VisibilityTimeout", $queue_url);
    }
    
    function getSize($queue_url = null)
    {
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        return $this->getQueueAttributes("ApproximateNumberOfMessages", $queue_url);
    }
    
    function setQueue($queue_url)
    {
        $this->queue_url = $queue_url;
    }
    
    function go($action, $params, $q = null)
    {
        
        if (!isset($queue_url)) {
            $queue_url = $this->queue_url;
        }
        
        $this->endpoint = "http://queue.amazonaws.com/";
        
        if ($action == 'DeleteQueue') {
            $this->endpoint = $q;
        } else {
            if (strpos($q, ':') === false ) {
                $this->endpoint .= $q;
            } else {
                $this->endpoint = $q;
            }
        }
        
        // Add Actions
        $params['Action'] = $action;
        $params['Version'] = '2008-01-01';
        
        return $this->xmlrequest($params);
    }
    
}



class SQSMessage {
    
    function __construct($body, $messageId, $receiptHandle)
    {
        $this->body      = $body;
        $this->messageId = $messageId;
        $this->receiptHandle = $receiptHandle;
    }
    
    function __toString()
    {
        return $this->Body;
    }
    
}


?>
