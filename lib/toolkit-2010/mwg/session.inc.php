<?php
/**
* @version    $Id: session.inc.php 21 2013-03-15 19:35:01Z ntemple $
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


function mwg_session_start() {
  @session_start();
}

function mwg_session_destroy() {
  @session_destroy();
}

class mwgSession {
  
  static function isAdmin() {
    if (!isset($_SESSION['admin_sess_id'])) return false;
    
    if ($_SESSION['admin_sess_id'] == md5(get_setting("secret_string")."-".ADMIN_PASSWORD)) {
      return true; // We're logged in
    } else {
      // We've been spoofed
      mwg_session_destroy();
      return false();
    } 
  }
  
  static function isLoggedIn() {
    if (isset($_SESSION['sess_id'])) return true;
  }
     
} 