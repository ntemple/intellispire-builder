<?php

if (!defined('INCLUDED_AMEMBER_CONFIG'))  die("Direct access to this location is not allowed");

require_once('/usr/local/share/phplib/init.inc.php');
require_once('lib/util.class.php');  
# util::$LOG = true;

util::trace('tuyu_protect_plugin config.php');

$notebook_page = 'Tuyu';
config_set_notebook_comment($notebook_page, 'Tuyu Master Integration');
if (file_exists($rm = dirname(__FILE__)."/readme.txt"))
    config_set_readme($notebook_page, $rm);
    
add_config_field('protect.tuyu_protect_plugin.cfg_wsdl', 'WSDL:',
    'text', "SOAP server URL",
    $notebook_page
    );

add_config_field('protect.tuyu_protect_plugin.cfg_user', 'Username:',
    'text', "",
    $notebook_page
    );

add_config_field('protect.tuyu_protect_plugin.cfg_pwd', 'Password:',
    'text', "",
    $notebook_page
    );

/*
add_config_field('protect.tuyu_protect_plugin.db', 'tuyu_protect_plugin Db and Tablename',
    'text', "AN EXAMPLE OF COMPLEX FIELD WITH VALIDATION<br />
    Database name (if other database) plus tables prefix<br />
    , like <i>tuyu_protect_plugin.invisionboard_</i><br />
    here <i>tuyu_protect_plugin</i> is a database name,<br />
    and tables prefix is <i>invisionboard_</i><br />
    ",
    $notebook_page, 
    'validate_tuyu_protect_plugin_db');

function validate_tuyu_protect_plugin_db($field,$vars){
    global $db;
    $v = $vars[$field['name']];
    $v = $db->escape($v);
    mysql_query("SELECT username FROM {$v} LIMIT 1");    
    if (mysql_errno()) 
        return "$field[title] - incorrect value. Error: " . mysql_error();
}
*/

?>
