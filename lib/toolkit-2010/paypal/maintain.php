#!/usr/bin/php -q
<?php

define('_SB_VALID_',1);
require_once('/home/paydotco/www/vars.inc.php');

$q=new cdb;
$q->query('select 1 from members'); // connect


# Clear old, unused sessions
# We filter on hits, for now, as those by be "problem" transactions
# Eventually we'll want to clear those, too
$query = 'delete from purchase_session 
          where ts < DATE_SUB(NOW(), INTERVAL 2 DAY) 
          and valid=0
          and hits=0';


$r = sql_execute($query);

?>
