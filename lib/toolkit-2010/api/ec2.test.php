/**
* Amazon PHP Classes and Functions
*
* Copyright (c)2008 Intellispire and original author(s)
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
* @version    SVN: $Id: ec2.test.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*
* Requires PHP 5+
*
* Original code from: http://code.google.com/p/php-aws/
*/

<?php

require_once('../config.php');
# define('API_AMAZON_ACCESS_KEY',     '');
# define('API_AMAZON_SECRET',         '');

require_once('ec2.class.php');

test();

function test()
{
    $ec2 = new EC2(API_AMAZON_ACCESS_KEY, API_AMAZON_SECRET);

    $r = $ec2->getImages();
//    print_r($r);

    $r = $ec2->getInstances();
//    print_r($r);

    $imageId = 'ami-255cb94c';
    $userData = NULL;
    

    $r = $ec2->runInstances($imageId, 1, 1, $keyName = "ec2key", 'development', 
                          'm1.small', 
                          NULL,
                          $userData
                          );
 
    print_r($r); 
    print_r($ec2);
}
