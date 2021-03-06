<?php
/**
 * Copyright (c)2008-2012 Nick Temple, Intellispire
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
 * @author       Nick Temple <nickt@nicktemple.com>
 * @license      GNU/GPL 2.0 http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @copyright    2010-2012 Intellispire/Nick Temple
 *
 */

defined('_JEXEC') or die('Restricted access');
version_compare(PHP_VERSION, '5.2.0', '>') or die('PHP Version 5.2.0 or greater required');

if (!defined('IS_MOD_MODE_NORMAL')) define('IS_MOD_MODE_NORMAL', 0);
if (!defined('IS_MOD_MODE_IFRAME')) define('IS_MOD_MODE_IFRAME', 1);
jimport( 'joomla.html.parameter' );

if (!class_exists('/*{UNIQUEID}*//*{cname}*/Intellispire1AR')) {

	class /*{UNIQUEID}*//*{cname}*/Intellispire1AR {

		var $ar;     //  = '{ name}';
		var $option; // = 'com_{ name}';
		var $connector;

		/** @var JParameter */
		var $params;

		/** @var array */
		var $data;

		/** @var stdClass */
		var $module = null;

		function print_form() {

			$mapper = null;
			if ($this->params->get('useapi', 0) == 0) {
				$mapper = $this->connector->getFormMap();
			}
				
			if (!$mapper) {
				$mapper = array(
				  'action' => $this->route("index.php?option={$this->option}&task=subscribe&id=" . $this->module->id, false),
				  'defaults' => array('id' => $this->module->id, 'last_name' => ''),
				  'hidden' => array(),
				  'map' => array('n_name' => 'first_name', 'n_email' => 'email', 'redirect' => 'redirect')
				);
			}
				
			$hidden   = $mapper['hidden'];
			$defaults = $mapper['defaults'];
			$map      = $mapper['map'];

			if (isset($mapper['action'])) {
				$this->data['action'] = $mapper['action'];
			}


			return $this->internal_print_form($hidden, $defaults, $map);
		}

		function __construct($class, $params) {

			$this->params = $params;
			
			if (version_compare(JVERSION, '1.6.0', 'ge')) {				
				$this->data = $params->toArray();
			} else {
				$this->data = (array) $params->_registry['_default']['data'];
			}
									
			// Convert the class into the product name
			$name = strtolower(str_replace('Jomlink1', '', $class));
			$this->ar = $name;
			$this->option = 'com_' . $name;
				
			// Load the API
			// Load the associated plugin data					
						
			$pluginData = $this->loadPluginParams($name);
			$this->connector = Jomlink1API::getConnector($class, $pluginData);
		}

		function loadPluginParams($element) {
			if (version_compare(JVERSION, '1.6.0', 'ge')) {
				$this->table = "_extensions";
			} else {
				$this->table = "_plugins";
			}
											
			// Save our updated params
			$db = JFactory::getDBO();
			$element = $db->Quote($element);
			$db->setQuery("select params from #_{$this->table} where element=$element");
            $data = $db->loadResult();
            		
			$pluginParams	= new JParameter( $data );
			
			return $pluginParams;
		}

		function thankyou() {
			print $this->params->get('tytext');
		}

		function get_module_mode() {
			return $this->params->get('display', 0);
		}

		function module_dispatch($module) {

			$this->module = $module;

			if (JRequest::getCmd('task') == 'subscribe') {
				// we're using the API to subcribe.
				$userData = $_POST; // Get the raw post data
				$this->connector->notify($userData);
				header('Location:' . $this->data['redirect']);
				return;
			}

			if ($this->get_module_mode() == IS_MOD_MODE_IFRAME) {
				// Process Iframes
				if (JRequest::getCmd('option') != $this->option) {
					// We are not in the helper, so display iframe
					return $this->iframe();
				}

				// We ARE in the helper
				if (JRequest::getCmd('task') == 'ty') {
					return $this->thankyou();
				}
			}

			// All other cases (normal mode & initial helper call)
			// print the form
			return $this->print_form();
		}

		function get_template() {
			if (isset($this->data['template']) && strlen($this->data['template']) > 60) {
				return $this->data['template'];
			} else {
				return $this->connector->getForm();
			}
		}

		function iframe() {

			if (version_compare(JVERSION, '1.6.0', 'ge')) {
				$src = "index.php?tmpl=component&option={$this->option}&id={$this->module->id}";
			}
			else{
				$src = $this->route("index.php?option={$this->option}&id=" . $this->module->id, true);
			}
			$height = $this->params->get('height');
			$width = $this->params->get('width');
			print "<iframe src='$src' height='$height' width='$width' scrolling='no' align='top' frameborder='0' class='wrapper'></iframe>\n";
		}

		function route($url, $use_index2 = false) {
			$port = $_SERVER['SERVER_PORT'];
			$proto = 'http';
			$ssl = false;
			if ($port == 80)
			$port = '';
			if ($port == 443) {
				$proto = 'https';
				$port = '';
			} else {
				$port = ':' . "$port";
			}

			// Force index2 BEFORE routing, if necessary, in order to bypass SEF
			if ($use_index2)
			$url = str_replace('index.php', 'index2.php', $url);

			// Routes using SSL in order to force a full URL
			$out = JRoute::_($url, false, true);

			//@todo - find better way to determine if SSL should be used

			if (!$ssl) {
				$out = str_replace('https:', 'http:', $out);
			}

			return $out;
		}

		protected function internal_print_form($hidden, $defaults, $map) {

			$v = $this->data;

			if (isset($v['script']) && $v['script']) {
				print $v['teaser'] . "\n";
				print "<script type='text/javascript' src='$v[script]'></script>\n";
				return;
			}

			// What do we call the name and email fields?
			$v['n_name'] = $map['n_name'];
			$v['n_email'] = $map['n_email'];

			// We want the redirect to change if we are in iframe mode
			// NLT @todo test this.
			if ($v['display'] == 1) {
				$self = $this->self_url();
				$v['redirect'] = $self . '&task=ty';
			}
			foreach ($map as $normalized => $custom) {
				if (isset($v[$normalized]))
				$v[$custom] = $v[$normalized];
			}

			$fields = '';
			foreach ($defaults as $name => $value) {
				$fields .= "<input type='hidden' name='$name' value='$value' />\n";
			}

			foreach ($hidden as $h) {
				if (isset($v[$h])) {
					$fields .= "<input type='hidden' name='$h' value='$v[$h]' />\n";
				}
			}

			$v['fields'] = $fields;

			$form = $this->get_template();

			foreach ($v as $name => $value) {
				$form = str_replace('{' . $name . '}', $value, $form);
			}
			print ($form);
		}

		function self_url() {
			if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off'))
			{
				$https = 's://';
			}
			else
			{
				$https = '://';
			}

			// Since we are assigning the URI from the server variables, we first need
			// to determine if we are running on apache or IIS.  If PHP_SELF and REQUEST_URI
			// are present, we will assume we are running on apache.

			if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI']))
			{
				// To build the entire URI we need to prepend the protocol, and the http host
				// to the URI string.
				$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			}
			else
			{
				// Since we do not have REQUEST_URI to work with, we will assume we are
				// running on IIS and will therefore need to work some magic with the SCRIPT_NAME and
				// QUERY_STRING environment variables.

				// IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
				$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

				// If the query string exists append it to the URI string
				if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
				{
					$theURI .= '?' . $_SERVER['QUERY_STRING'];
				}
			}

			return $theURI;
		}

	}

} // Class Exists

//{jomlinkapi}


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
