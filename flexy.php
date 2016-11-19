#!/usr/bin/php
<?php
include ('lib/init.php');


$ctx = new Context(__DIR__, __DIR__ . '/tmp');
# $ctx->_opt['nonHTML'] = true;
# $ctx->_opt['allowPHP'] = true;
# $ctx->_opt['debug'] = true;

$ctx->version = '1.0';
print $ctx->render('test.php');





