<?php

require('../config.php');
require('sdb.class.php');

test();
function test()
{
    
    $sd = new SDB(API_AMAZON_ACCESS_KEY, API_AMAZON_SECRET);
    $sd->setDomain("domain1");
    
    $r = $sd->listDomains();
    print_r($r);
    //   $r = $sd->query("domain1");
    //   print_r($r);
    
    //   $r = $sd->createDomain("domain1");
    //   print_r($r);
    
    $r = $sd->putAttributes('item1', array('name' => array('value1', 'value2')));
    print_r($r);
    
    $r = $sd->getAttributes('item1');
    print_r($r);
    
    $r = $sd->deleteAttributes('item1', array('name', 'name2'));
    print_r($r);
    //   $r = $sd->deleteDomain('domain1');
    //   print_r($r);
}
