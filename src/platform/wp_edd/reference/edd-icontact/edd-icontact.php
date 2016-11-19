<?php
/*
Plugin Name: Easy Digital Downloads - iContact
Plugin URL: http://easydigitaldownloads.com/extension/icontact
Description: Include a iContact signup option with your Easy Digital Downloads checkout
Version: 1.0
Author: Lorenzo Orlando Caum, Enzo12 LLC
Author URI: http://enzo12.com
Contributors: lorenzocaum
*/

// adds the settings to the Misc section
function eddicontact_add_settings($settings) {
  
  $eddicontact_settings = array(
		array(
			'id' => 'eddicontact_settings',
			'name' => '<strong>' . __('iContact Settings', 'eddicontact') . '</strong>',
			'desc' => __('Configure iContact Integration Settings', 'eddicontact'),
			'type' => 'header'
		),
        array(
			'id' => 'eddicontact_username',
			'name' => __('Username', 'eddicontact'),
			'desc' => __('Enter your iContact Username.', 'eddicontact'),
			'type' => 'text',
			'size' => 'regular'
		),
        array(
			'id' => 'eddicontact_app_password',
			'name' => __('APP Password', 'eddicontact'),
			'desc' => __('Enter your iContact APP Password. It can be found in the Developer Portal after you have registered your app.', 'eddicontact'),
			'type' => 'password',
			'size' => 'regular'
		),
        array(
			'id' => 'eddicontact_app_id',
			'name' => __('APP ID', 'eddicontact'),
			'desc' => __('Enter your iContact APP ID. It can be found in the Developer Portal after you have registered your app.', 'eddicontact'),
			'type' => 'text',
			'size' => 'regular'
		),
        array(
			'id' => 'eddicontact_account_id',
			'name' => __('Account ID', 'eddicontact'),
			'desc' => __('Enter your iContact Account ID. It can be found in the Developer Portal after you have registered your app.', 'eddicontact'),
			'type' => 'text',
			'size' => 'regular'
		),
        array(
			'id' => 'eddicontact_client_folder_id',
			'name' => __('Client Folder ID', 'eddicontact'),
			'desc' => __('Enter your iContact Client Folder ID. It can be found in the Developer Portal after you have registered your app.', 'eddicontact'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'eddicontact_list_id',
			'name' => __('List ID', 'eddicontact'),
			'desc' => __('Enter your List ID. It is the numeric value of your List Name.', 'eddicontact'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'eddicontact_label',
			'name' => __('Checkout Label', 'eddicontact'),
			'desc' => __('This is the text shown next to the signup option', 'eddicontact'),
			'type' => 'text',
			'size' => 'regular'
		)
	);
	
	return array_merge($settings, $eddicontact_settings);
}
add_filter('edd_settings_misc', 'eddicontact_add_settings');

// adds an email to the icontact subscription list
function eddicontact_subscribe_email($email, $first_name = '', $last_name = '' ) {
	global $edd_options;
	
	if( isset( $edd_options['eddicontact_app_id'] ) && strlen( trim( $edd_options['eddicontact_app_id'] ) ) > 0 ) {

		if( ! isset( $edd_options['eddicontact_client_folder_id'] ) || strlen( trim( $edd_options['eddicontact_client_folder_id'] ) ) <= 0 )
			return false;
        
        require_once('inc/iContact.php');
        
        $username = $edd_options['eddicontact_username'];
        $apppassword = $edd_options['eddicontact_app_password'];
        $appid = $edd_options['eddicontact_app_id'];
        $accountid = $edd_options['eddicontact_account_id'];
        $clientfolderid = $edd_options['eddicontact_client_folder_id'];
        
        $iContact = new iContact(
                                 'https://app.icontact.com/icp/',
                                 $username,	
                                 $apppassword,
                                 $appid,
                                 $accountid,
                                 $clientfolderid,
                                 false
                                 );
        
        $listID = $edd_options['eddicontact_list_id'];

        $contacts[] = array('email'=>$email,'firstName'=>$first_name, 'lastName'=>$last_name);
        
        $contactIds = $iContact->createContacts($contacts);
        
        $iContact->subscribeContactsToList($listID, $contactIds);
        
	}

	return false;
}

// displays the icontact checkbox
function eddicontact_icontact_fields() {
	global $edd_options;
	ob_start(); 
		if( isset( $edd_options['eddicontact_app_id'] ) && strlen( trim( $edd_options['eddicontact_app_id'] ) ) > 0 ) { ?>
		<p>
			<input name="eddicontact_icontact_signup" id="eddicontact_icontact_signup" type="checkbox" checked="checked"/>
			<label for="eddicontact_icontact_signup"><?php echo isset($edd_options['eddicontact_label']) ? $edd_options['eddicontact_label'] : __('Sign up for our mailing list', 'eddicontact'); ?></label>
		</p>
		<?php
	}
	echo ob_get_clean();
}
add_action('edd_purchase_form_before_submit', 'eddicontact_icontact_fields', 100);

// checks whether a user should be signed up for the icontact list
function eddicontact_check_for_email_signup($posted, $user_info) {
	if($posted['eddicontact_icontact_signup']) {

		$email = $user_info['email'];
		eddicontact_subscribe_email($email, $user_info['first_name'], $user_info['last_name'] );
	}
}
add_action('edd_checkout_before_gateway', 'eddicontact_check_for_email_signup', 10, 2);
