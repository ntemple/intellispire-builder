<?php
require_once('../config/config.php');
require_once('simpletest/autorun.php');

class AllTests extends TestSuite {
    function AllTests() {
        $this->TestSuite('All AWS tests');
        $this->addFile('sdb.test.php');
    }
}
