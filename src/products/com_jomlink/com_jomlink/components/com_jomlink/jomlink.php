<?php
/**
 * @copyright   Copyright (C) 2008-2012 Intellispire. All Rights Reserved.
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

/**
 * processModule
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl/modulesincontent
 * @copyright  Copyright (C) 2008 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
if (!function_exists('AR_processModule')) {

	function AR_processModule($module, $style = 'none') {
		$_user = JFactory::getUser();

		if (version_compare(JVERSION, '1.6.0', 'ge')) {
			$mainframe = JFactory::getApplication();
			$_aid = ($_user->id) ? 2 : 1;
		} else {
			global $mainframe;
			$_aid = $_user->get('aid', 0);
		}

		$_db = JFactory::getDBO();


		if (is_numeric($module)) {
			$_where = ' AND m.id=' . $module;
		} else {
			$_where = ' AND m.title="' . $module . '"';
		}
		$_query = 'SELECT *' .
                ' FROM #__modules AS m' .
                ' WHERE m.access <= '.(int) $_aid.
                ' AND m.client_id = '.(int) $mainframe->getClientId().
		$_where .
                ' ORDER BY ordering' .
                ' LIMIT 1';

		$_db->setQuery($_query);
		$_row = $_db->loadObject();

		if (version_compare(JVERSION, '1.6.0', 'ge')) {
			$_params = json_decode($_row->params,true);
			$pos = ($_params['display'] == 1) ? true : false;
		}
		else{
			// NLT Make sure this module is allowed access from here
			$pos = strpos($_row->params, 'display=1');
		}

		$task = JRequest::getString('task');
		if ( (!$pos) && ($task != 'subscribe') ) die('Restricted acceess.');
				
		$html = '';
		if ($_row) {
			//determine if this is a custom module
			$_row->user = ( substr($_row->module, 0, 4) == 'mod_' ) ? 0 : 1;
			$_row->style = $style;

			$_attribs = array();
			$_attribs['style'] = $style;

			$html = JModuleHelper::renderModule($_row, $_attribs);
		}
		return $html;
	}

}

//Entry Point

$mod_id = JRequest::getInt('id', 0);

if ($mod_id) {
	print AR_processModule($mod_id);
} else {
	die('Restricted access');
}


