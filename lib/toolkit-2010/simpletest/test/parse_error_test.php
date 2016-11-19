<?php
// $Id: parse_error_test.php 21 2013-03-15 19:35:01Z ntemple $
require_once('../unit_tester.php');
require_once('../reporter.php');

$test = &new TestSuite('This should fail');
$test->addFile('test_with_parse_error.php');
$test->run(new HtmlReporter());
?>