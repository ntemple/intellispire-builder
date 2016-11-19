<?php

require_once('../config/config.php');
require_once('lib/sbutil.class.php');
require_once('sdb.class.php');
require_once('simpletest/autorun.php');

define('SDB_TEST_DOMAIN', 'domain1');

class TestOfSDB extends UnitTestCase {

   var $sdb;

   function show($var, $label = '') {
      if ($label) print "$label:\n"; 
      print_r($var);
   }

   function __construct() {
      $this->sdb = new SDB(API_AMAZON_ACCESS_KEY, API_AMAZON_SECRET);
      $this->sdb->setDomain(SDB_TEST_DOMAIN);
   }

   function __destruct() {
   //  print_r($this->sdb);
   }

   // Called before and after every test
   function setUp() { } 
   function tearDown() { }


   function testSetup() {
     $this->assertIsA($this->sdb, 'SDB');
   }

   function X_testCreateDomain() {
     sbutil::trace();
     try {
       $this->sdb->createDomain('');
       $this->fail(); // should never get here
     } catch (Exception $e) {
       $this->pass();
     } 
     
     $this->sdb->createDomain(SDB_TEST_DOMAIN);
   
     $domains = $this->sdb->listDomains();
     $this->assertTrue(is_array($domains));

     $this->assertTrue(in_array(SDB_TEST_DOMAIN, $domains)); 
   }

   function testListDomains() {
     $domains = $this->sdb->listDomains();
     $this->assertTrue(is_array($domains));
 
     $this->assertTrue(in_array(SDB_TEST_DOMAIN, $domains)); 
   }  


   function XtestAttributes() {
      $sdb = $this->sdb; // shprtcut

      $sdb->putAttributes('item1', array('name' => array('value1', 'value2')));
      $sdb->putAttributes('item1', array('var' => 'value1'));

     
      $r = $sdb->getAttributes('item1');
      $this->assertTrue($r['name'] == 'value1,value2');
      $this->assertTrue($r['var'] == 'value1');
   
 
      $sdb->deleteAttributes('item1', array('name'));

      $r = $sdb->getAttributes('item1');
      $this->assertTrue($r['name'] == 'value1,value2');
      $this->assertTrue($r['var']  == 'value1');
   }

   function testQuery() {
      $sdb = $this->sdb; // shprtcut
      $id = sbutil::uuid_raw();

      $type = 'item';
 
      // We need to test multi_curl for performance 
/* 
      for ($i = 0; $i < 300; $i ++) {
        $id = sbutil::uuid_raw();
        $params = array();
        $params['color'] = 'blue';
        $sdb->putAttributes($type . '.' . $id, $params);
print "X";
      }
   
      for ($i = 0; $i < 300; $i ++) {
        $id = sbutil::uuid_raw();
        $params = array();
        $params['color'] = 'green';
        $sdb->putAttributes($type . '.' . $id, $params);
print "x";
      }

      for ($i = 0; $i < 300; $i ++) {
        $id = sbutil::uuid_raw();
        $params = array();
        $params['color'] = 'red';
        $sdb->putAttributes($type . '.' . $id, $params);
print ".";
      }

*/
      $id = 'inthemoney';
      $params = array();
      $params['color'] = 'gold';
      $sdb->putAttributes($type . '.' . $id, $params);

      $data = $sdb->limit_query('',250);
      print_r($data); 
/*
      $data = $sdb->query("['color' = 'gold']");
      print_r($data);
*/
      while($sdb->nextToken) {
         $data = $sdb->limit_query('', 250, true);
         print_r($data);
      }

      $data = $sdb->query();
      print_r($data);


/*
      $data = $sdb->query('', 1);
      print_r($data);

      $data = $sdb->query("['color' = 'black']");
      print_r($data);
*/


   }


   function X_testDeleteDomain() {
     $this->sdb->deleteDomain(SDB_TEST_DOMAIN);
 
     $domains = $this->sdb->listDomains();
     $this->assertTrue(is_array($domains));
   
     $this->assertFalse(in_array(SDB_TEST_DOMAIN, $domains));
   }


/*   
   function error($s, $m, $f, $l) {
     print_r($this->sdb);
   }
*/

}


