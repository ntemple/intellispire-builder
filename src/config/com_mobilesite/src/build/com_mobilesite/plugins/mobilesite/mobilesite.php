<?php
/**
 * Copyright (c)2012 Nick Temple, Intellispire
 * Loosely Based on WPTap Mobile Detector
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2
 * of the License, and no other version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @author       WPTap Development Team
 * @author       Nick Temple <nickt@nicktemple.com>
 * @license      GNU/GPL 2.0 http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @copyright    2012 Nick Temple/Intellipire
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.methods' );

class plgSystemMobilesite extends JPlugin {

        var $link;

	function __construct($subject, $config) {
		parent::__construct($subject, $config);
	}

	function onAfterRender() {
		// Ignore if we are running in the administrator
		$app = JFactory::getApplication();
		if ($app->isAdmin()) return;

		// Check to see if we are on the front page
		if(version_compare(JVERSION,'1.6.0','ge')) {
			$menu = $app->getMenu();
		} else {
			$menu = JSite::getMenu();
		}

		if ($menu->getActive() == $menu->getDefault()) {
                        $this->link = $this->params->get('location', null);
			$this->mobileRedirect();
		}
	}


	function mobileRedirect() {

		if (isset($_REQUEST['mobileoverride']) && ($_REQUEST['mobileoverride']) == 1) {
			setcookie('mobileoverride', 1);
			return;
		}

		if (isset($_COOKIE['mobileoverride']) && ($_COOKIE['mobileoverride']) == 1) {
			return;
		}

		if ($this->mobileDetect()) {

                   if (! $this->link) {

			$this->link = $this->mobileRoute('index.php?option=com_mobilesite');
                   }
			header("Location:  {$this->link}");
			exit();
		}

	}

	function mobileRoute($url) {

		$this->brokenroute = JRoute::_($url);
			
		$router = JRouter::getInstance('site');
		$uri = $router->build($url);
		$url = $uri->toString(array('path', 'query', 'fragment'));

		if(version_compare(JVERSION,'1.6.0','ge')) {
			$current = JURI::current();
			$uri->parse($current);
			$url = $uri->getScheme() . '://' . $uri->getHost() . $url;
		} else {
			$base = JURI::base(false);
			$url = $base . $url;
		}
		return $url;
	}

	function mobileDetect($container = null)
	{
		// return 'Test Agent';

		$useragents = array(
		  'iPhone/iPod' => 'iPhone|iPod|aspen|webmate',
		  'Android' => 'android|dream|cupcake',
		  'BlackBerry Storm' => 'blackberry9500|blackberry9530',
		  'Nokia' => 'series60|series40|nokia|Nokia',
		  'Apple iPad' => 'ipad|iPad',
		  'Opera' => 'opera mini|Opera',
		  'Palm' => 'pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine',
		  'Windows Smartphone' => 'iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile',
		  'Blackberry' => 'blackberry|Blackberry'
		  );
		  
		  if (!$container) {
		    $container = $_SERVER['HTTP_USER_AGENT'];
		  }
		  
		  $mobile_current_id = null;

		  foreach ($useragents as $mobile_id => $useragent) {
		  	$useragent = explode('|', $useragent);
		  		
		  	foreach($useragent as $agent) {
		  		if (preg_match("/$agent/i", $container)) {
		  			$mobile_current_id = $mobile_id;
		  			break;
		  		}
		  	}
		  }

		  return $mobile_current_id;
	}

}
