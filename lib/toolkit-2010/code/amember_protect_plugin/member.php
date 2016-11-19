<?php

error_reporting(E_ALL & ~E_NOTICE);  

require_once('/srv/phplib/init.inc.php');
require_once('lib/sbSoap.class.php');
require_once('lib/util.class.php');

util::$LOG = true;

$ns = 'http://wsdl.intellispire.com/mbrhooks';
$debug = $_GET['debug'] + 0;
$server = new sbSoapServer();
$server->configureWSDL('MBRHOOKS', $ns);
$server->wsdl->schemaTargetNamespace=$ns;

function amemberHooksFactory() {
  return new amemberHooks();
}

class amemberHooks {

  function subscription_added($member_id, $product_id, $member) {
    util::trace('',  SB_FULL_TRACE);
  }

  function subscription_updated($member_id, $oldmember, $newmember) {
    util::trace('',  SB_FULL_TRACE);
  }

  function subscription_deleted($member_id, $product_id, $member) {
    util::trace('',  SB_FULL_TRACE);
  }
 
  function subscription_removed($member_id, $member) {
    util::trace('',  SB_FULL_TRACE);
  }

  function subscription_rebuild($members) {
    util::trace('',  SB_FULL_TRACE);
    util::debug($members, 'members');
  }

  function subscription_check_uniq_login($login, $email, $password) {
    util::trace("hook", SB_FULL_TRACE);
    return 1; // Ok to use this password
    return 0; // Not ok to use this password
  }

  function fill_in_signup_form(&$vars) {
    util::trace('',  SB_FULL_TRACE);
  }
   
}


### Proxy Functions to class ###
define ('HOOK_ERR', -1);
define ('HOOK_OK',  1);


/**
* @desc Documentation here
* 
*  And more docs?
*/

$server->register_me('subscription_added');              

function subscription_added($member_id, $product_id, $member) {
  util::trace();  
  try {
    $hooks = amemberHooksFactory();
    $member = unserialize($member);
    $hooks->subscription_added($member_id, $product_id, $member);
  } catch (Exception $e) {
    writelog("exception", $e);
    return HOOK_ERR;
  }  
  return HOOK_OK;
}

$server->register_me('subscription_updated'); 
function subscription_updated($member_id, $oldmember,$newmember) {
  util::trace();
  try {
    $hooks = amemberHooksFactory();
    $oldmember = unserialize($oldmember);
    $newmember = unserialize($newmember);
    $hooks->subscription_updated($member_id, $oldmember, $newmember);
  } catch (Exception $e) {
    writelog("exception", $e);
    return HOOK_ERR;
  }  
  return HOOK_OK;
}

$server->register_me('subscription_deleted'); 
function subscription_deleted($member_id, $product_id, $member) {
  util::trace();
  try {
    $hooks = amemberHooksFactory();
    $member = unserialize($member);
    $hooks->subscription_deleted($member_id, $product_id, $member);
  } catch (Exception $e) {
    writelog("exception", $e);
    return HOOK_ERR;
  } 
  return HOOK_OK;
}

$server->register_me('subscription_removed');   
function subscription_removed($member_id, $member) {
  util::trace();
  try {
    $hooks = amemberHooksFactory();
    $member = unserialize($member);
    $hooks->subscription_removed($member_id, $member);
  } catch (Exception $e) {
    writelog("exception", $e);
    return HOOK_ERR;
  }
  return HOOK_OK;
}

$server->register_me('subscription_rebuild'); 
function subscription_rebuild($members) {
  util::trace('members - b4 deserialization', SB_FULL_TRACE);
  try {
    $hooks = amemberHooksFactory();
    $members = unserialize($members);
    $hooks->subscription_rebuild($members);
  } catch (Exception $e) {
    writelog("exception", $e);
    return HOOK_ERR;
  } 
  return HOOK_OK;
}

/* Return 1 if it's ok (uniq), 0 if it is not */
$server->register_me('subscription_check_uniq_login'); 
function subscription_check_uniq_login($login, $email, $password) {
  util::trace();
  try {
    $hooks = amemberHooksFactory();
    $result = $hooks->subscription_check_uniq_login($login, $email, $password);
  } catch (Exception $e) {
    writelog("exception", $e);
    return HOOK_ERR;
  } 
  return $result;
}

/**
* @desc Returns serialized array of vars to replace the ones sent.
*/

$server->register_me('fill_in_signup_form', 'xsd:string');   
function fill_in_signup_form($vars) {
  util::trace();
  try {
    $hooks = amemberHooksFactory();
    $vars = 
    $hooks->fill_in_signup_form($vars);
  } catch (Exception $e) {
    writelog("exception", $e);
  }
  return serialize($vars);
}

$server->register_me('test_setup'); 
function test_setup() {
  util::trace();
}

$server->register_me('test_teardown'); 
function test_teardown() {
  util::trace();
}

# ===
/*
if ( ( !isset($_SERVER['PHP_AUTH_USER'] )) || (!isset($_SERVER['PHP_AUTH_PW']))
     || ( $_SERVER['PHP_AUTH_USER'] != 'tuyu' ) || ( $_SERVER['PHP_AUTH_PW'] != 'tuyu' ) ) {

    header( 'WWW-Authenticate: Basic realm="Tuyu"' );
    header( 'HTTP/1.0 401 Unauthorized' );
    echo 'Authorization Required.';
    exit;
}
*/

if ($_GET['client'] == 1) {
  util::trace("client=1");
  header("content-type: text/plain");
  print $server->GetClientProxy();
  exit();
}  
util::trace('service');              
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);




