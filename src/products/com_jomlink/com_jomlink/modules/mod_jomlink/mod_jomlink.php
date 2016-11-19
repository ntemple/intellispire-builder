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
