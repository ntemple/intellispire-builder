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
require_once('fps.class.php');

test();

function test()
{
    $p = new FPS(API_AMAZON_ACCESS_KEY, API_AMAZON_SECRET, 'https://fps.amazonaws.com');   
 
    $result = $p->getAccountBalance();
    print_r($p);
    print_r($result);

}

?>
