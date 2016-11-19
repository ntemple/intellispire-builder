<?php

 if (!defined('INCLUDED_AMEMBER_CONFIG')) 
    die("Direct access to this location is not allowed");

require_once('/usr/local/share/phplib/init.inc.php');
require_once('lib/util.class.php');
require_once('lib/sbSoap.class.php');
 
util::$LOG = true;
util::trace('tuyu_protect_plugin loaded');


function check_setup_tuyu() {
    global $plugin_config;
    util::trace();

    $this_config = $plugin_config['protect']['tuyu_protect_plugin'];
    $wsdl = $this_config['cfg_wsdl'];
  
    if (!$wsdl) {
        $error = "Error. Please configure Tuyu plugin at aMember CP -> Setup -> mod_auth_mysql";
        if (!$_SESSION['check_setup_tuyu_error']) $db->log_error ($error);
        $_SESSION['check_setup_tuyu_error'] = $error;
        util::debug($error);
        return false;
    }
    return true;
}

if (check_setup_tuyu()) {
  util::trace("setting hooks");
  setup_plugin_hook('subscription_added',   'tuyu_protect_plugin_added');
  setup_plugin_hook('subscription_updated', 'tuyu_protect_plugin_updated');
  setup_plugin_hook('subscription_deleted', 'tuyu_protect_plugin_deleted');
  setup_plugin_hook('subscription_removed', 'tuyu_protect_plugin_removed');
  setup_plugin_hook('subscription_rebuild', 'tuyu_protect_plugin_rebuild');
  setup_plugin_hook('subscription_check_uniq_login', 'tuyu_protect_plugin_check_uniq_login');
  setup_plugin_hook('fill_in_signup_form', 'tuyu_protect_plugin_fill_in_signup_form');

/*
setup_plugin_hook('check_logged_in', 'ua_check_logged_in')
setup_plugin_hook('after_logout', 'ua_after_logout')
setup_plugin_hook('after_login', 'ua_after_login')
*/

}


function tuyu_protect_plugin_check_uniq_login($login, $username, $password) {
  global $config, $db, $plugin_config;
  util::trace();

  $this_config = $plugin_config['protect']['tuyu_protect_plugin'];  
  $wsdl = $this_config['cfg_wsdl'];

  $soap = new sbSoapClient($this_config['cfg_wsdl']);
  $parameters = array();

  $parameters['login'] = $login;
  $parameters['email'] = $email;
  $parameters['password'] = $email;

  $result = $soap->call('subscription_check_uniq_login', $parameters);
  if($error = $soap->getError()){
    util::trace("error");
    util::debug($wsdl);
    util::debug($this_config);
    $db->log_error ($error);
    throw new Exception($error);
  }
  return $result;

}

function tuyu_protect_plugin_fill_in_signup_form(&$vars) {
  global $config, $db, $plugin_config;
  util::trace('+begin');

  util::debug($vars, 'original vars');

  $this_config = $plugin_config['protect']['tuyu_protect_plugin'];
  $wsdl = $this_config['cfg_wsdl'];
  $soap = new sbSoapClient($this_config['cfg_wsdl']);

  $parameters = array();
  $parameters['vars'] = serialize($vars);

  $result = $soap->call('fill_in_signup_form', $parameters);
  if($error = $soap->getError()){
    util::trace("error");
    util::debug($wsdl);
    util::debug($this_config);
    $db->log_error ($error);
    throw new Exception($error);
  }

 
  # this is actually a reference to an array
  # Since we can't (?confirm) change the array reference,
  # we ned to overwrite the elements, instead

  $result = unserialize($result);
  util::debug($result, 'result');

  foreach ($result as $name => $value) {
    $vars['name'] = $value;
  } 
  util::debug($vars, 'new vars');
  util::trace('-end');

}

/**
    /// some actions when admin click aMember CP -> Rebuild Db
    /// it should compare all records in your third-party
    /// database with aMember supplied-list ($members)
    /// Or you may just skip this hook
*/

function tuyu_protect_plugin_rebuild(&$members){
  global $config, $db, $plugin_config;
  $this_config = $plugin_config['protect']['tuyu_protect_plugin'];
  util::trace();

  $wsdl = $this_config['cfg_wsdl'];
  $soap = new sbSoapClient($this_config['cfg_wsdl']);

  $parameters['members'] = serialize($members);

  $result = $soap->call('subscription_rebuild', $parameters);
  if($error = $soap->getError()){
      util::trace("error");
      util::debug($wsdl);
      util::debug($this_config);
      $db->log_error ($error);
      throw new Exception($error);
  }
  return $result;

}
   
/**
    /// It's a most important function - when user subscribed to
    /// new product (and his subscription status changed to ACTIVE
    /// for this product), this function will be called
    /// In fact, you should add user to database here or update
    /// his record if it is already exists (it is POSSIBLE that
    /// record exists)

*/
 
function tuyu_protect_plugin_added($member_id, $product_id,
    $member){
    global $config, $db, $plugin_config;
    util::trace();

    $this_config = $plugin_config['protect']['tuyu_protect_plugin'];
    $wsdl = $this_config['cfg_wsdl'];
    $soap = new sbSoapClient($this_config['cfg_wsdl']);

    $parameters['member_id'] = $member_id;
    $parameters['product_id'] = $product_id;
    $parameters['member'] = serialize($member);

    $result = $soap->call('subscription_added', $parameters);
    if($error = $soap->getError()){ 
      util::trace("error");
      util::debug($wsdl);
      util::debug($this_config);
      $db->log_error ($error);
      throw new Exception($error);
    }
    return $result;
     

}

/**
    /// this function will be called when member updates
    /// his profile. If user profile is exists in your
    /// database, you should update his profile with
    /// data from $newmember variable. You should use
    /// $oldmember variable to get old user profile -
    /// it will allow you to find original user record.
    /// Don't forget - login can be changed too! (by admin)
*/

function tuyu_protect_plugin_updated($member_id, $oldmember,
    $newmember){
    global $config, $db, $plugin_config;

    util::trace();
    $this_config = $plugin_config['protect']['tuyu_protect_plugin'];

    $wsdl = $this_config['cfg_wsdl'];
    $soap = new sbSoapClient($this_config['cfg_wsdl']);
  
    $parameters['member_id'] = $member_id;
    $parameters['oldmember'] = serialize($oldmember);
    $parameters['newmember'] = serialize($newmember);

    $result = $soap->call('subscription_updated', $parameters);
    if($error = $soap->getError()){
      util::trace("error");
      util::debug($wsdl);
      util::debug($this_config);
      $db->log_error ($error);
      throw new Exception($error);
    }
    return $result;

}

/**
    /// This function will be called when user subscriptions
    /// status for $product_id become NOT-ACTIVE. It may happen
    /// if user payment expired, marked as "not-paid" or deleted
    /// by admin
    /// Be careful here - user may have active subscriptions for
    /// another products and he may be should still in your
    /// database - check $member['data']['status'] variable
*/

function tuyu_protect_plugin_deleted($member_id, $product_id,
    $member){
    global $config, $db, $plugin_config;

    util::trace();
    $this_config = $plugin_config['protect']['tuyu_protect_plugin'];

    $wsdl = $this_config['cfg_wsdl'];
    $soap = new sbSoapClient($this_config['cfg_wsdl']);
    $parameters = array();

    $parameters['member_id'] = $member_id;
    $parameters['product_id'] = $product_id;
    $parameters['member'] = $member;

    $result = $soap->call('subscription_deleted', $parameters);
    if($error = $soap->getError()){
      util::trace("error");
      util::debug($wsdl);
      util::debug($this_config);
      $db->log_error ($error);
      throw new Exception($error);
    }

    return $result;
}


/**
    /// This function will be called when member profile
    /// deleted from aMember. Your plugin should delete
    /// user profile from database (if your application allows it!),
    /// or it should just disable member access if your application
    /// if application doesn't allow profiles deletion

*/


function tuyu_protect_plugin_removed($member_id, 
    $member){
    global $config, $db, $plugin_config;

    util::trace();
    global $config, $db, $plugin_config;
    $this_config = $plugin_config['protect']['tuyu_protect_plugin'];

    $wsdl = $this_config['cfg_wsdl'];
    $soap = new sbSoapClient($this_config['cfg_wsdl']);

    $parameters = array();

    $parameters['member_id'] = $member_id;
    $parameters['member'] = $member;

    $result = $soap->call('subscription_removed', $parameters);
    if($error = $soap->getError()){
      util::trace("error");
      util::debug($wsdl);
      util::debug($this_config);
      $db->log_error ($error);
      throw new Exception($error);
    }
    return $result;

}

?>
