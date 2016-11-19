<?php
/**
* @version    $Id: upgrade.php 21 2013-03-15 19:35:01Z ntemple $
* @package    MWG
* @copyright  Copyright (C) 2010 Intellispire, LLC. All rights reserved.
* @license    GNU/GPL v2.0, see LICENSE.txt
*
* Marketing Website Generator is free software. 
* This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Check the database and peform an upgrade if necessary
* Needs to be very fast as it will be run on every call
*/

define('MWG_DB_VERSION', '1.5');

// upgrade_test();

$db = MWG::getDb();
ob_start();
upgrade($db);
$debug = ob_get_clean();

function upgrade_test() {
  ob_end_clean();
  print "<pre>";
  print "START UPGRADE\n";

  $db = MWG::getDb();
  upgrade($db);
  print_r($db); 
  exit();
}

function upgrade(mysqldb $db) {

  $db_version = '1.0';

  $tables = $db->get_results("show tables like 'mwg%'");
  if (count($tables) > 0) {
    $db_version = $db->get_value('select value from mwg_setting where name=?', 'site_dbversion');
  } 

  if ($db_version >= MWG_DB_VERSION) return;

  # perform upgrade
  include('upgrade13.php');

  upgrade_tables($db);
  run_upgrade($db, 'settings.txt', 'on duplicate key update id=id');
  run_upgrade($db, 'upgrade.txt'); 

  $db->query("UPDATE mwg_setting SET value=? WHERE name = 'site_dbversion'", MWG_DB_VERSION);

  //  $db->query('update settings set value=1 where name=?', 'lock');

  return $db->get_value('select value from mwg_setting where name=?', 'site_dbversion');

}



