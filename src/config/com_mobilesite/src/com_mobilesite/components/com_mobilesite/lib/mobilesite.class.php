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

class mobileSite {

}


class WordPressMobileSite extends mobileSite {

}

class JoomlaMobileSite extends mobileSite {

	var $params; // Site Component Params
	var $menu_params; // Parameters for this item
	var $menu_item;   // The current item
	var $ismenu = false; // Is this the menu page?

	function __construct() {
		$this->params = JComponentHelper::getParams('com_mobilesite');

		$menu = JSite::getMenu();
		$this->menu_item = $menu->getActive();
		if ($this->menu_item) {
			$this->menu_params = new JParameter( $this->menu_item->params );
		} else {
			$this->menu_params = $this->params;
		}

		if (isset($_GET['layout'])) {
			$view = $_GET['layout'];
		} else {
			$view = 'default';
		}

		if (!ctype_alnum($view)) $view = 'default';
		$file = $this->templateForView($view);
		if (!file_exists($file)) {
			$view = 'default';
		}
		if ($view == 'default') {
			$this->ismenu = true;
			$file = $this->templateForView($view);
		}
		$this->file = $file;

	}

	function rss($url, $num_items, $cache_time) {
		if (!class_exists('SimplePie')) {
			require_once('simplepie.class.php');
		}

		$feed = new SimplePie();
		$feed->set_feed_url($url);

		if ($cache_time) {
			$feed->enable_cache(true);
			$feed->set_cache_duration($cache_time * 60);
			$feed->set_cache_location($this->getTempDirectory() ); 
		} else {
			$feed->enable_cache(false);
		}
	  
		$feed->init();
		$feed->handle_content_type();
		 
		$items = array();
		 
		$feed_items = $feed->get_items(0, $num_items);

		foreach($feed_items as $feed_item) {
			$item = array(
    	    'permalink' => $feed_item->get_permalink(),
    	    'title' => $feed_item->get_title(),
    	    'date'  => $feed_item->get_date('j M Y'),
    	    'content' => $feed_item->get_content(),
			);
			$items[] = $item;
		}
		 
		return $items;
	}

	function templateForView($view) {
		return dirname(dirname(__FILE__)) . '/views/page/tmpl/' . $view . '.php';
	}


	function get($name, $default = '') {
		$out = null;
		$out = $this->params->get($name, null);
		if ($out) return $out;

		$out = $this->menu_params->get($name, null);
		if ($out) return $out;

		if (isset($this->menu_item->$name)) {
			return $this->menu_item->$name;
		}

		return $default;
	}

	function __get($name) {
		return $this->get($name, null);
	}


	function getArticle($articleId = null) {

		if (!$articleId) $articleId = JRequest::getInt('id');
		if (!$articleId) return '';

		$db = JFactory::getDBO();

		$sql = "SELECT introtext FROM #__content WHERE id = ".intval($articleId);

		$db->setQuery($sql);

		$article = $db->loadResult();
		return $article;
	}

	function getLogo() {
		$logo = $this->get('logoimage');

		if (!$logo || $logo == -1) {
			return "<div style='font-size:180%'>" . $this->bizname . '</div>'; // @todo NLT stylize
		}

		$url =  JURI::root() . '/images/' . $logo;

		$width = $this->logowidth;
		if ($width) {
			$width = "width='$width'";
		} else {
			$width = '';
		}

		$height = $this->logoheight;
		if ($height) {
			$height = "height='$height'";
		} else {
			$height = '';
		}

		return "<img src='$url' $height $width alt='{$this->bizname}'>\n";

	}

	function getMetaTitle() {
		$title = $this->get('mobilepage_title');

		if (!$title) {
			$title = $this->get('page_title');
		}

		if (!$title) {
			$title = $this->get('bizname');
		}
		return $title;
	}


	function getMetaDescription() {
		$description = $this->get('mobilepage_description');

		if (!$description) {
			$description = $this->tagline;
		}

		return $description;
	}

	function getTempDirectory() {
		global $mainframe;

		$mainframe = JFactory::getApplication();
		return $mainframe->getCfg('tmp_path');
	}

	function getMenuItemName() {
		if (isset($this->menu_item->name)) {
			return $this->menu_item->name;
		}

		if (isset($this->menu_item->title)) {
			return $this->menu_item->title;
		}
		return "Page Contents";
	}

	function getBody() {
		$site = $this;
		include($this->file);
	}

	function getMenu() {
		$out = '';
		$menuName = $this->get('menu');
		$menu = JSite::getMenu();
		$rows = $menu->getItems('menutype', $menuName);

		$items = array();

		if ($rows) foreach ($rows as $mitem) {
			parse_str(parse_url($mitem->link, PHP_URL_QUERY), $nvp);
			$mitem->menu_params = new JParameter($mitem->params);

			$item = new stdClass();
			$item->icon = $mitem->menu_params->get('icon');
			if (isset($mitem->name)) {
				$item->name = $mitem->name;
			} else if (isset($mitem->title)) {
				$item->name = $mitem->title; // j25
			} else {
				$item->name = 'Tap for Link';
			}

			if (isset($nvp['layout'])) {
				$item->pagetype = $nvp['layout'];
			} else {
				$item->pagetyype = $mitem->type;
			}
			$item->url = JRoute::_($mitem->link . '&Itemid=' . $mitem->id);

			$items[] = $item;
		}

		return $items;

	}

    // Run the Joomla! content filters
    function runfilters($content) {

        JPluginHelper::importPlugin('content');

        $app = JFactory::getApplication();


        if($app->isAdmin()) {
            return;
        }

        $dispatcher = JDispatcher::getInstance();

        $article = new stdClass();
        $article->text = $content;

        $params = array();

        $results = $dispatcher->trigger(
            'onPrepareContent', array (&$article, &$params, 0)
        );

        return $article->text;
    }

	/**
	 * Returns the next access key in line;
	 */
	function getKey() {
		static $next = 'A';
		return $next++;
	}

	function getHome() {
		return MS_BASE_URI . '?mobileoverride=1';
	}

	function getMobileURL($override = false) {
		$this->home = $this->route('index.php?option=com_mobilesite');
		return $this->home;
	}

	function route($url) {
		$this->brokenroute = JRoute::_($url); // Side effect: loads JRouter
			
		$router = JRouter::getInstance('administrator');
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

		$url = str_replace('/administrator/', '/', $url);
		return $url;
	}

	function getAddress() {
		return $this->get('address1') . ' ' . $this->get('address2');;
	}
}

