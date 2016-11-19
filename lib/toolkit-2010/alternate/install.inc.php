<?php
/**
 * Sabrayla PHP Classes and Functions
 *
 * General install subroutines for all installers.
 * Make as generic as possible so we can use everywhere
 *
 * TODO: Rewrite installer, most of these functions are 
 * provided in other places (db class, init)
 *
 * @category   sabrayla
 * @package    sabrayla
 * @author     Nick Temple <Nick.Temple@intellispire.com>
 * @copyright  2002-2006 Intellispire
 * @license    http://www.sabrayla.com/license/1_0.txt Sabrayla License 1.0
 * @version    SVN: $Id: install.inc.php 21 2013-03-15 19:35:01Z ntemple $
 * @since      File available since Release 0.1
 */


/* General install subroutines for all installers.
   Make as generic as possible so we can use everywhere

*/


#SUPPORT FUNCTIONS
######################################

function install() {
  global $errors;
  $dbx = mysql_connect($_POST["DB_RW_HOST"],
                       $_POST["DB_RW_LOGIN"],
                       $_POST["DB_RW_PASSWORD"]);
  if (! $dbx) {
    $errors[] = "Could not connect to  database server: ". mysql_error();
    return false;
  }

  $res = mysql_select_db($_POST["DB_RW_DATABASE"]);
  if (! $res) {
    $errors[]  = "Could not select database: " . mysql_error();
    return false;
  }

  create_db($dbx);
  create_ini();

  return true;
}

function execute_sql($file) {

  $db = implode("", file($file));
  $sql_array = explode(';', $db);
  foreach ($sql_array as $sql) {
    @mysql_query($sql);
  }

  return true;
}

function create_db($dbx) {
  execute_sql('dbstructure.sql');
  return true; 
}

function getRoot($file) {
  $parts = explode('/', $file);
  array_pop($parts);
  array_pop($parts);  // we are now one subdir below. NLT.
  $file = implode('/', $parts); 
  return $file . '/';
}

function getDomain($file) {
  $parts = explode('.', $file);
  array_shift($parts);
  $file = implode('.', $parts);
  return $file;
}
?>
