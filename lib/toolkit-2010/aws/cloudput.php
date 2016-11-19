<?php

  $hostname ='network.intellispire.com';
  $bucket = 'network.intellispire.com';
  $bucketbase = '';
  $cloudbase  = 'http://network.intellispire.com/download/';
  $filebase = 'tmp/download';
  # $expires  = 'Thu, 15 Apr 2010 20:00:00 GMT';
  $usessl = ''; // or "-s";

require_once('/srv/lib/aws/cloudput.inc.php');

process($bucket, $bucketbase, $filebase);

