<?php
/**
* Amazon PHP Classes and Functions
*
* @category   Amazon
* @package    AWS
* @author     Nick Temple <Nick.Temple@intellispire.com>
* @copyright  2002-2008 Intellispire
* @license    MIT: http://www.opensource.org/licenses/mit-license.php
* @version    SVN: $Id: s3.test.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*
* Requires PHP 5+
*/

require('s3.class.php');
require("../config.php");

test();

function test()
{
    $datastore = new S3(API_AMAZON_ACCESS_KEY, API_AMAZON_SECRET, 's3.intellispire.com');
    print $datastore->getRequestURL('Priest, Cherie - Four and Twenty Blackbirds.pdf', time() + 60*5);
    print $datastore->put('test.txt', 'test-data', 'text/plain', 'public-read');
    // private
    print $datastore->putfile('s3.test.php.X', 's3.test.php');
    print $datastore->get('s3.test.php.X');
    print $datastore->getfile('s3.test.php.X', 's3.test.php.Y');
    
    print $datastore->delete('test.txt');
    print $datastore->delete('s3.test.php.X');
}
