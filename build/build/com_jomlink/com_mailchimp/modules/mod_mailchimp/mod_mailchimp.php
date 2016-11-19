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

defined('_JEXEC') or die( 'Restricted access' );
$path =  JPATH_ADMINISTRATOR . "/components/com_{lcname}/{lcname}.class.php";
require_once($path);

if ($module) {
  // This is a module  
  $ar = new /*{UNIQUEID}*//*{cname}*/Intellispire1AR("{name}", $params);
  $ar->module_dispatch($module); // module
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
