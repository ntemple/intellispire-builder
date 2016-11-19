<?php
/**
 * @copyright   Copyright (C) 2008-2013 Intellispire. All Rights Reserved.
 * @license GNU/GPL v2.0, see LICENSE.php
 *
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');


jimport( 'joomla.html.parameter' );
require_once('lib/mobilesite.class.php');

// error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
// ini_set('display_errors',1);


// Setup environment
define('MS_ASSETS_URI', JURI::base().'components/com_mobilesite/assets/');
define('MS_BASE_URI', JURI::base());

$site = new JoomlaMobileSite();
include(dirname(__FILE__) . '/views/template.php');
exit();

