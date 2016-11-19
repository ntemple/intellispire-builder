<?php
/**
* @version    $Id: upgrade13.php 21 2013-03-15 19:35:01Z ntemple $
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

function upgrade_tables($db) {

  # Upgrade members table
  $table = upgrade_check_fields($db, 'members');
  if (isset($table['stormpay_email'])) $db->query('alter table members drop stormpay_email');
  if (isset($table['p_stormpay_email']))  $db->query('alter table members drop p_stormpay_email'); 
  if (!isset($table['admin'])) $db->query("ALTER TABLE `members` ADD `admin` CHAR( 1 ) NOT NULL DEFAULT '0' AFTER `jv`");

  $table = upgrade_check_fields($db, 'mwg_setting');
  if (! $table) {
     run_upgrade($db, 'table.setting.txt');
  }

  # this may produce an error that can safely be ignored
  $db->query("ALTER TABLE `mwg_setting` ADD UNIQUE ( `name`)");

  $table = upgrade_check_fields($db, 'mwg_gizmo');
  if (! $table) {
     run_upgrade($db, 'table.gizmo.txt');
     $table = upgrade_check_fields($db, 'mwg_gizmo');
  }
  # We now have to upgrade the gizmo table
  if (!isset($table['position'])) $db->query("ALTER TABLE `mwg_gizmo` ADD `position` VARCHAR( 255 ) 
                                              NOT NULL AFTER `title` , ADD `ordre` INT NOT NULL AFTER `position`");
}

function upgrade_check_fields($db, $table) {
  $tables = $db->get_results("show tables like ?", $table);
  if (count($tables) == 0) return false;

  $fields = array();

  $cols = $db->get_results("show columns from $table");
  foreach($cols as $field) {
    $fields[$field['Field']] = $field;
  }
  return $fields; 
}


function run_upgrade($db, $file, $extra = '') {
  $txt = file_get_contents(MWG_BASE . '/lib/upgrade/' . $file);
  $queries = explode(";\n", $txt);
  foreach ($queries as $q) {
    $q = trim($q);
    if (! $q) continue;
    $q .= ' ' . $extra;
    print ".$q.\n";
    $db->query($q);
    print $db->_error;
  }
  return $db->get_value('select value from mwg_setting where name=?', 'site_dbversion');
}


