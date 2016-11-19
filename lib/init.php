<?php

date_default_timezone_set('America/New_York');

define('BASEDIR', __DIR__);
require BASEDIR.'/../vendor/autoload.php';

# @todo replace with auto loader
require(BASEDIR . '/template/context.class.php');

use Symfony\Component\Yaml\Yaml;
use Composer\Json\JsonFile;


