<?php

require('../config.php');
require('sdb.class.php');

//  var $remoteCallFunc = 'http_request';
//  var $remoteCallFunc = 'curl_request';
//  var $remoteCallFunc = 'multi_curl_request';


test('http_request', 10, 30);
test('curl_request', 10, 30);
test('multi_curl_request', 10, 30);


/**
* @param remoteCallFunc
* @param # of threads to start
* @param # of transactions per thread
*/

function test($requester, $i_max, $t_max)
{
    
    $domain = 'test.test1';
    
    $sd = new SDB(API_AMAZON_ACCESS_KEY, API_AMAZON_SECRET);
    $sdc = new SDB(API_AMAZON_ACCESS_KEY, API_AMAZON_SECRET);
    
    //   $sdc->createDomain($domain);
    $sdc->setDomain($domain);
    $sd->setDomain($domain);
    $sd->setRemotCallFunc($requester);
    
    $start = time();
    for ($i = 0; $i < $i_max; $i++) {
        for ($t = 0; $t < $t_max; $t++) {
            $sd->putAttributes("item$i$t", array('name' => array('value1', 'value2')));
        }
        $sd->commit();
        echo '.';
    }
    print "\n";
    $end = time();
    
    // TODO: Verify data
    
    //   $r = $sdc->deleteDomain($domain);
    
    print "$requester: Created " . $i_max * $t_max . " tuples in " . ($end - $start) . " seconds.\n";
    
}
