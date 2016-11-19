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


Array
(
    [phing.file] => /Users/ntemple/dev/product/build.xml
    [phing.dir] => /Users/ntemple/dev/product
    [host.os] => Darwin
    [os.name] => Darwin
    [php.classpath] => /Users/ntemple/dev/product/vendor/phing/phing/bin/../classes:/Users/ntemple/dev/product/vendor/phpunit/php-text-template/:/Users/ntemple/dev/product/vendor/phpunit/phpunit-mock-objects/:/Users/ntemple/dev/product/vendor/phpunit/php-timer/:/Users/ntemple/dev/product/vendor/phpunit/php-token-stream/:/Users/ntemple/dev/product/vendor/phpunit/php-file-iterator/:/Users/ntemple/dev/product/vendor/phpunit/php-code-coverage/:/Users/ntemple/dev/product/vendor/phpunit/phpunit/:/Users/ntemple/dev/product/vendor/phpunit/phpunit/../../symfony/yaml:/Users/ntemple/dev/product/vendor/phing/phing/classes:.:/php/includes:/Users/ntemple/dev/php/pear/share/pear
    [host.fstype] => UNIX
    [php.interpreter] => 
    [line.separator] => 

    [php.version] => 5.3.15
    [user.home] => /Users/ntemple
    [application.startdir] => /Users/ntemple/dev/product
    [phing.startTime] => Sat, 16 Mar 2013 06:25:16 GMT
    [host.name] => phantom.local
    [host.arch] => x86_64
    [host.domain] => unknown
    [host.os.release] => 11.4.2
    [host.os.version] => Darwin Kernel Version 11.4.2: Thu Aug 23 16:25:48 PDT 2012; root:xnu-1699.32.7~1/RELEASE_X86_64
    [phing.home] => /Users/ntemple/dev/product/vendor/phing/phing
    [env.TERM_PROGRAM] => Apple_Terminal
    [env.TERM] => xterm-256color
    [env.SHELL] => /bin/bash
    [env.TMPDIR] => /var/folders/5q/zpnhffhs0wx9qg17f7ydv08w0000gn/T/
    [env.Apple_PubSub_Socket_Render] => /tmp/launch-f12sLt/Render
    [env.TERM_PROGRAM_VERSION] => 303.2
    [env.OLDPWD] => /Users/ntemple/dev/projects/platform/com_jomlink/src
    [env.TERM_SESSION_ID] => 69B096FF-1A53-4C49-920E-9E9C3801599F
    [env.USER] => ntemple
    [env.COMMAND_MODE] => unix2003
    [env.SSH_AUTH_SOCK] => /tmp/launch-xzEVwO/Listeners
    [env.__CF_USER_TEXT_ENCODING] => 0x1F5:0:0
    [env.Apple_Ubiquity_Message] => /tmp/launch-vC0mUa/Apple_Ubiquity_Message
    [env.PHP_CLASSPATH] => :/Users/ntemple/dev/product/lib
    [env.PATH] => /Library/Frameworks/Python.framework/Versions/2.7/bin:/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin:/usr/X11/bin:/usr/local/git/bin:/usr/local/share/npm/bin:/Users/ntemple/dev/php/pear/bin:/Users/ntemple/dev/product/bin:/Users/ntemple/dev/product/bin
    [env.PWD] => /Users/ntemple/dev/product
    [env.LANG] => en_US.UTF-8
    [env.NODE_PATH] => /usr/local/lib/node
    [env.SHLVL] => 1
    [env.HOME] => /Users/ntemple
    [env.LOGNAME] => ntemple
    [env.DISPLAY] => /tmp/launch-Cj5iy5/org.x:0
    [env._] => /Users/ntemple/dev/product/bin/phing
    [env.PHP_SELF] => /Users/ntemple/dev/product/bin/phing
    [env.SCRIPT_NAME] => /Users/ntemple/dev/product/bin/phing
    [env.SCRIPT_FILENAME] => /Users/ntemple/dev/product/bin/phing
    [env.PATH_TRANSLATED] => /Users/ntemple/dev/product/bin/phing
    [env.DOCUMENT_ROOT] => 
    [env.REQUEST_TIME] => 1363415116
    [env.argc] => 2
    [phing.version] => Phing DEV
    [phing.file.products] => /Users/ntemple/dev/product/build.xml
    [phing.dir.products] => /Users/ntemple/dev/product
    [phing.project.name] => Products
    [project.basedir] => /Users/ntemple/dev/product
    [deploy.products] => 
    [build.wpplugins] => 
    [build.products] => plg_dentalsite
    [all.products] => com_arhelper,mod_contentmenu,mod_isgetresponse,plg_arp3,plg_getresponse,plg_istools,com_genstaller,mod_isarp3,mod_isicontact,plg_cloudfront,plg_isaweber,plg_jems,com_updater,mod_isaweber,mod_jemsmenu,plg_curlemu,plg_isicontact,tpl_salespage
    [all.wpplugins] => wp_constantcontact,wp_icontact
    [s3.bucket] => network.intellispire.com
    [product_template] => com_jomlink
    [product_id] => JomLink
    [product_name] => MailChimp
    [product_version] => 6.0.0
    [/*com_{jomlink}*/] => com_mailchimp
)
