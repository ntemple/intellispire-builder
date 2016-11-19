<?php
/**
* @version    $Id: mwgRequest.class.php 21 2013-03-15 19:35:01Z ntemple $
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

class mwgRequest {
  
  var $req;
  var $get;
  var $post = false;
  
  function __construct($req = null) {
    $this->req = $req;
    if ($this->req) {
      $this->req = $req;
    } else {
      $this->req = array_merge($_GET, $_POST);
    }    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $this->post = true;
    }
    $this->get = $_GET; // Save so we can parse later
  }

  function isPost() { return $this->post; }
  function isGet()  { return !$this->post; }
  
  function get($name, $default = '') {
    if (isset($this->req[$name])) return stripslashes($this->req[$name]);
    return $default;
  }

  function safeGet($name, $default = '') {
    $value = $this->get($name, $default);
    return preg_replace("/[^a-zA-Z0-9\s]/", "", $value);
  }

  function persist(&$response) {
     $response->setdata($this->req);
  }
  
}


