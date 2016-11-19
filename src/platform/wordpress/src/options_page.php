<?php

/**
 * Create a wordpress OptionsPage from
 * an array of fields.
 */

class /*{UNIQUEID}*//*{cname}*/OptionsPage {

    var $prefix;
    var $params;

    var $PLUGINS_HANDLE;
    var $OPTIONS_HANDLE;
    var $SECTION_HANDLE;

    function __construct($params, $prefix = '') {
        $this->params = $params;
        $this->prefix = $prefix;


        $this->PLUGINS_HANDLE = $prefix . "_plugins";
        $this->OPTIONS_HANDLE = $prefix . "_options";
        $this->SECTION_HANDLE = $prefix . "_section";


        add_action('admin_menu', array($this, 'admin_add_page'));
        add_action('admin_init', array($this, 'admin_init'));

    }

    function admin_add_page() {
        add_submenu_page ( 'edit.php?post_type=brackets', 'BracketPress > {name} Newsletter', '{name} Newsletter', 'manage_options', 'bracketpress_{lcname}', array($this, 'settings_page'));
    }


    function admin_init() {

        // One settings group for now. Eventually allow split into pages
        add_settings_section($this->SECTION_HANDLE, '{name} Settings', array($this, 'main_section_text'), $this->PLUGINS_HANDLE);

        // We only have one setting
        register_setting ( $this->OPTIONS_HANDLE, $this->OPTIONS_HANDLE, array($this, 'validate') );


        foreach ($this->params as $param) {

            add_settings_field(
                $this->prefix . '_setting_' . $param['name'],
                $param['label'],
                array($this, 'displaySetting'),
                $this->PLUGINS_HANDLE,
                $this->SECTION_HANDLE,
                $param
            );

        }
    }

    /**
     * @param $param create an input widget
     */

    function displaySetting($param) {
        $options = get_option($this->OPTIONS_HANDLE);
        $name =  $param['name'];
        $value = $options[$name];

        echo "<input id='{$this->prefix}_$name' name='{$this->prefix}_options[$name]' size='{$param[size]}' xtype='{$param[type]}' value='$value' />";
    }

    function main_section_text() {
        echo "<p>{name} Options</p>";
    }

    function validate($input) {
        $options = get_option($this->OPTIONS_HANDLE, array());
        update_option($this->OPTIONS_HANDLE . '_old', $options); // @todo allow for undo

        foreach ($this->params as $param) {
            $name = $param['name'];
            if (isset($input[$name])) $options[$name] = $input[$name];
        }

//        print "\n<pre>\n";
//        print_r($input);
//        print_r($options);
//        print "\n</pre>\n";


        return $options;
    }

    function settings_page() {
        //@todo: this needs to run earlier in the request cycle, but only on this page
        if (isset($_GET['settings-updated'])) {
            add_action('admin_notices', array($this, 'admin_notice'));
        }
        ?>
        <div class="wrap">
        <?php screen_icon(); ?>
        <h2>{name} Plugin</h2>
        <form method="post" action="options.php">
            <?php settings_fields($this->OPTIONS_HANDLE); ?>
            <?php do_settings_sections($this->PLUGINS_HANDLE); ?>
            <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
        </form>
    </div>
    <?php
    }

    function admin_notice() {
        echo '<div class="updated">Settings Updated</div>';
    }
}


