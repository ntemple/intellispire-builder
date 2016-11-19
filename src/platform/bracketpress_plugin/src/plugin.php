<?php
/*
Plugin Name: BracketPress {name} Newsletter Signup
Plugin URI: http://www.bracketpress.com/
Description: Connect BracketPress Registration to {name}
Version:  {version}
Author: BracketPress Team
Author URI: http://www.bracketpress.com/
License: GPL v2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html

*/


// error_reporting(E_ALL);
// ini_set("display_errors", 1);
// set_site_transient( 'update_plugins', null );
// if (! function_exists('print_pr')) { function print_pr($m) { print "<pre>\n" . print_r($m, true) . "</pre>\n"; } }


function bracketpress_/*{lcname}*/() {
    return /*{UNIQUEID}*//*{cname}*/BracketPress::instance();
}
bracketpress_/*{lcname}*/();

class /*{UNIQUEID}*//*{cname}*/BracketPress {

    static $instance;

    // Globals
    var $version   = '{version}';
    var $item_name = '{name}';
    var $author    = 'BracketPress Team';
    var $update_server = 'http://www.bracketpress.com';
    var $prefix    = '{lcname}';

    // Classes
    var $optionsPage;  // /*{UNIQUEID}*//*{cname}*/BracketPressSettingsPage
    var $updater;      // /*{UNIQUEID}*//*{cname}*/BracketPressProEDD_SL_Plugin_Updater
    var $coreclass;

    var $params;

    /** @var array */
    var $options_key;
    var $options;


    var $json_params = '{params}';

    public static function instance()
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('bracketpress_loaded'. array($this, 'init'));
    }

    function init() {
        // Globals
        $this->options_key = $this->prefix . '_options';
        $this->options = get_option($this->options_key);
        $this->params = $this->setParams();

        // Includes
        // require_once( "updater.php");

        $this->update();
        if (is_admin()) {
            $this->setup_admin();
        }

        // Actions
        add_action('bracketpress_register_form',  array($this, 'registration_fields'), 100);
        add_action('bracketpress_user_register', array($this, 'check_for_email_signup'), 10, 2);

    }

    function get_option($key, $default = null) {
        if (isset($this->options[$key])) {
            return trim($this->options[$key]);
        } else {
            return $default;
        }
    }

    function set_option($key, $value) {
        $this->options[$key] = $value;
        update_option($this->options_key, $this->options);
    }

    private function setup_admin() {

        add_filter($this->prefix . '_update_options', array($this, 'activate_license'));
        $this->optionsPage = new /*{UNIQUEID}*//*{cname}*/BracketPressSettingsPage($this->item_name, $this->prefix, $this->params);
    }

    function isLicenseValid() {
        $license_valid = $this->get_option('license_valid', '');
        $license_key   = $this->get_option('license_key', '');

        if (! $license_key) return false;

        if ($license_valid == 'valid') {
            return true;
        } else {
            return false;
        }
    }

    function activate_license($options) {

        $old_options = get_option($this->options_key .'_old', array());
        $old_license = isset($old_options['license_key']) ? $old_license = $old_options['license_key'] : '';
        $license_key = isset($options['license_key']) ? $license_key = $options['license_key'] : '';

        if ($license_key != $old_license)  {
            $valid = $this->updater->activate($license_key);
            $options['license_valid'] = $valid;
        }
        return $options;
    }

    private function update() {

        $license_key = $this->get_option('license_key', '');

        $this->updater = new /*{UNIQUEID}*//*{cname}*/BracketPressProEDD_SL_Plugin_Updater( $this->update_server, __FILE__, array(
                'version' 	=> $this->version, 	   // current version number
                'license' 	=> $license_key, 	   // license key (used get_option above to retrieve from DB)
                'item_name' => $this->item_name,   // name of this plugin
                'author' 	=> $this->author       // author of this plugin
            )
        );
    }

    function setParams() {
        if ($this->isLicenseValid()) {
            $params = json_decode($this->json_params, true);
            array_unshift($params,  array('name' => 'signup_text',       'size' => 50, 'type' => 'text', 'default' => 'Sign up for our mailing list', 'label' => 'Label', 'description' => 'Label for checkbox' ));
        } else {
            $params = array();
        }

        array_unshift($params,  array('name' => 'license_key',       'size' => 50, 'type' => 'text', 'default' => '', 'label' => 'Activation Key', 'description' => 'Enter your activation to activate the plugin.'));
        $this->params = $params;
        return $params;
    }


    /**
     * Add new registration fields the signup form
     */
    function registration_fields()  {
        if (! $this->isLicenseValid()) return;

        $text = $this->get_option('signup_text', 'Sign up for our mailing list');
        $label = isset($options[$this->prefix .'_label']) ? $options[$this->prefix .'_label'] : __($text, $this->prefix);

        $out = "<p>
        <input name='{$this->prefix}_signup' id='{$this->prefix}_signup' type='checkbox' checked='checked'/>
        <label for='{$this->prefix}_signup'>$label</label>
        </p>\n";
        print $out;
    }

    /**
     * checks whether a user should be signed up for the list
     */

    function check_for_email_signup($userid) {
        if (isset($_POST[$this->prefix . '_signup'])) {
            $platform = new /*{UNIQUEID}*//*{cname}*/Platform($this);
            $userdata = get_userdata( $userid );
            $platform->notify((array) $userdata->data);
        }
    }
}


