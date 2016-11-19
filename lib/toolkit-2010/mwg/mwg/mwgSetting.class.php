<?php
/**
* @version    $Id: mwgSetting.class.php 21 2013-03-15 19:35:01Z ntemple $
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

defined('_MWG') or die ('Restricted Access');

class mwgSetting {
  

  function get_setting($name, $default = null) {
    $db = MWG::getDb();
    $value = $db->get_value('select value from mwg_setting where name=?', $name);
    if ($value == null) return $default;
    return $value;
  }
  
  
}
