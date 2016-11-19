<?php

define('_SB_VALID_',1);
require_once('../vars.inc.php');


$ids = sql_get_row('select sessionid from members');
print_r($ids);

$q=new cdb;
$q->query('select 1 from members'); // connect
$q2=new cdb;

$ids = sql_get_row('select sessionid from members');
print_r($ids);



$start = time();
foreach ($ids as $id) {
  $sql = "
   SELECT * 
   FROM members
   WHERE '$id' = MD5( CONCAT( 'PDC-', id ) )";

# for ($i = 0; $i < 10; $i++ ) {
  mysql_query($sql);
# }
}
$end = time();

print $end - $start;
print "\n";


$start = time();
foreach ($ids as $id) {

  $sql = "
  SELECT *
  FROM members
  WHERE '$id' = sessionid";
  ";

# for ($i = 0; $i < 10; $i++ ) {
  mysql_query($sql);
# }
}
$end = time();

print $end - $start;
print "\n";

?>
