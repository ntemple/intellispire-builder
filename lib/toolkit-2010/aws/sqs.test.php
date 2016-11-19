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

<?php

require_once('../config.php');
# define('API_AMAZON_ACCESS_KEY',     '');
# define('API_AMAZON_SECRET',         '');

require_once 'HTTP/Request.php';
// grab this with "pear install --onlyreqdeps HTTP_Request"
require_once('sqs.class.php');

test();

function test()
{
    $q = new SQS(API_AMAZON_ACCESS_KEY, API_AMAZON_SECRET);
    
    $result = $q->CreateQueue('ntest1');
    print("-> $result\n");
    
    if (1) {
        $t1 = time();
        for ($i = 0; $i < 100; $i++) {
            $q->SendMessage('How fast are we?');
        }
        $t2 = time();
        $delta = $t2-$t1;
        print "Burned a penny! Sent 100 messages in $delta seconds.\n";
        
        //  $result = $q->DeleteQueue('ntest1');
        //  print("$result\n");
        
    }
    
    
    $result = $q->CreateQueue('ntest2');
    print("$result\n");
    
    $result = $q->ListQueues();
    print_r($result);
    
    $q->SendMessage('Hello SQS 1');
    $q->SendMessage('Hello SQS 2');
    $q->SendMessage('Hello SQS 3');
    
    // Receive exactly one message.
    $mymessage = $q->ReceiveMessage();
    print_r($mymessage);
    
    $result = $q->DeleteMessage($mymessage['ReceiptHandle']);
    print_r($result);
    
    // Receive one or more messages, always
    // returns an array of messages even if
    // only one is returned.
    
    //  $result = $q->ReceiveMessages(1);
    //  print_r($result);
    
    $result = $q->ReceiveMessages(6);
    //  print_r($result);
    
    /*
    $result = $q->SetTimeout(30);
    print_r("$result\n");
    
    $result = $q->GetTimeout();
    print_r("$result\n");
    */
}

?>