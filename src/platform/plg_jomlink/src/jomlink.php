<?php
/**
 * Copyright (c)2006-2013 Nick Temple, Intellispire
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
 * @copyright    2006-2013 Nick Temple/Intellipire
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if (version_compare(PHP_VERSION, '5.2.0', '<')) {
    print 'This system requires PHP v5.2 or better: ' . __FILE__;
    die('This system requires PHP v5 or better.');
}

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

/**
 * Intellispire {name} User Plugin
*/

class plgUser/*{cname}*/  extends JPlugin {

    /**
     * The main autoresponder function. Modify if necessary.
     * This function calls out to the appropriate auto-responder class
     * @param mixed $user: The normalized user array
     */
    function notify($user) {
        $success = false;

        try {
            $ar = new /*{UNIQUEID}*//*{cname}*/MailClass();
            $success = $ar->notify($this, $user);
        } catch(Exception $e) {
            $this->setResponse($e);
        }

        $this->save();
        return $success;
    }

    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+

    function warn($msg) {
        if (version_compare(JVERSION, '2.6.0', 'gt')) {
            JLog::add($msg, JLog::WARNING, 'jerror');
        }
        else{
            JError::raiseWarning(500, $msg);
        }
        return false;
    }

    function sendmail($email, $name, $address, $subject, $msg) {

        if (version_compare(JVERSION, '2.6.0', 'ge')) {
            $mailer = new JMail();
            return $mailer->sendMail($email, $name, $address, $subject, $msg);
        } else {
            return JUtility::sendMail($email, $name, $address, $subject, $msg);
        }
    }

    function get_param($key, $default = null) {
        $result = $this->params->get($key, $default);
        if(is_scalar($result)) {
            return trim($result);
        } else {
            return $result;
        }
    }

    function set_param($key, $val) {
        $this->params->set($key, $val);
        return true;
    }

    function setRequest($val) {
        $val = substr($val, 0, 2048); // protect from large requests
        $this->params->set('request', $val);
        return true;
    }

    function setResponse($val) {
        $val = substr($val, 0, 2048); // protect from large responses
        $this->params->set('response', $val);
        return true;
    }

    function setDebug($val) {
        static $msg = '';
        if (!$this->debug) return true;

        $val = substr($val, 0, 1024); // protect from large responses
        $msg .= $val . "\n";
        $this->params->set('debug', $msg);
        $this->save();

        return true;
    }

    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+

    var $users;
    var $debug = false;
    var $table;

    // Constructor
    function __construct(& $subject, $config) {
        $this->users = array();
        if (version_compare(JVERSION, '1.6.0', 'ge')) {
            $this->table = "_extensions";
        } else {
            $this->table = "_plugins";
        }
        parent::__construct($subject, $config);
    }

    /**
     * Joomla! 1.5 Event Trigger
     *
     * This only gets called by 1.5, thus referring it to the 1.6/1.7 method.
     *
     * @param mixed $user
     * @param mixed $isnew
     */
    function onBeforeStoreUser($user, $isnew) {
        $this->onUserBeforeSave($user, $isnew);
    }

    /**
     * Used to detect if user field "blocked" was changed.
     *
     * @param mixed $user
     * @param mixed $isnew
     */
    function onUserBeforeSave($user, $isnew) {
        $this->setDebug("onUserBeforeSave\n" . print_r($user, true));

        // Was this user blocked?
        $id = $user['id'];
        $blocked = $user['block'];
        $this->users[$id] = $blocked;
    }

    /**
     * New event, based on the change of blocked
     *
     * @param mixed $user
     * @param mixed $isnew
     */
    function onActivation($user, $isnew) {
        $this->setDebug("onActivation\n" . print_r($user, true));
    }

    /* This is where we're trying to get ! */
    function onRegistrationComplete($user) {
        $this->setDebug("onRegistrationComplete\n" . print_r($user, true));
        $user['registration_ip'] = $_SERVER['REMOTE_ADDR'];

        // This is done during the notify stage.
        // Is it necessary here?
        $names = explode(' ', $user['name']);
        switch (count($names)) {
            case 1: // We have a first name, no last name
                $user['first_name'] = $names[0];
                $user['last_name'] = '';
                break;
            case 2: // expected, first name and last name
                $user['first_name'] = $names[0];
                $user['last_name'] = $names[1];
                break;
            default: // more than one name
                $user['last_name'] = array_pop($names);
                $user['first_name'] = implode(' ', $names);
        }

        if (defined('COMMUNITY_COM_PATH')) {
            $user = $this->extendJomSocial($user);
        }

        if (defined('_CB_VALIDATE_NEW')) {
            $user = $this->extendCommunityBuilder($user);
        }

        $to_unset = array('password', 'password_clear', 'password2', 'verifyPass', 'params');
        foreach ($to_unset as $var) {
            if (isset($user[$var])) unset($user[$var]);
        }

        /* Clean user array */
        foreach ($user as $name => $value) {
            if (!is_scalar($value)) {
                unset($user[$name]);
            }
        }

        $this->notify($user);
    }

    /**
     * 1.5 event that redirects to the onUserAfterSave event.
     *
     * @param mixed $user
     * @param mixed $isnew
     * @param mixed $success
     * @param mixed $msg
     */
    function onAfterStoreUser($user, $isnew, $success, $msg) {
        $this->onUserAfterSave($user, $isnew, $success, $msg);
    }

    /**
     * Main method for sending the notifications
     *
     * @param mixed $user
     * @param mixed $isnew
     * @param mixed $success
     * @param mixed $msg
     */
    function onUserAfterSave($user, $isnew, $success, $msg) {
        $this->setDebug("onUserAfterSave\n" . print_r($user, true));
        // 0 - wait for confirmation click
        // 1 (or any other )- activate immediately
        $activation = $this->params->get('activation', 0);

        if ($activation == 0) {
            // Wait for activation link update ...

            $id = $user['id'];
            $blocked = $user['block'];

            if (!$success) return; // don't do anything with failed users
            if ($blocked) return; // don't do anything with blocked users

            if ($this->users[$id] == 1 && $blocked == 0) {
                // This was an unblocking action, always(?) an activation
                $this->onActivation($user, $isnew);
                $isnew = true; // For our purposes, this is a new user;
            }
        }

        if ($isnew) return $this->onRegistrationComplete($user);
    }

    function save() {
        // Save our updated params
        $db = JFactory::getDBO();
        $element = $db->Quote($this->_name);
        $db->setQuery("update #_{$this->table} set params=" . $db->Quote($this->params->toString()) . " where element=$element");
        $db->query();
    }

    function extendCommunityBuilder($user) {
        // At this point, Community Builder may not have
        // yet saved the extended field data, so we need to
        // get it from the $_REQUEST

        $data = $_REQUEST; // clone array
        $clear = array('id', 'user_id', 'password', 'password2', 'verifypass', 'password__verify', 'cbsecuritym3', 'cbrasitway');
        foreach ($clear as $value) unset($data[$value]);

        foreach ($data as $n => $v) {
            $user[$n] = $v;
            $n2 = str_replace('cb_', '', $n);
            $user[$n2] = $v; // Also add a version without the "CB"
        }

        if (isset($user['firstname'])) $user['first_name'] = $user['firstname'];
        if (isset($user['lastname'])) $user['last_name'] = $user['lastname'];

        return $user;

    }

    function extendJomSocial($user) {
        if (!defined('COMMUNITY_COM_PATH')) return $user;

        try {
            $db = JFactory::getDBO();

            $jspath = JPATH_ROOT . "/components/com_community";
            include_once("$jspath/libraries/core.php");

            $q = "select id from #__community_fields where fieldcode='IP'";
            $db->setQuery($q);
            $fieldid = $db->loadResult();

            if ($fieldid) {
                $db->setQuery("insert into #__community_fields_values(user_id, field_id, value) values ($user[id], $fieldid, '$user[ip]')");
                $db->query();
            }

            // Get CUser object
            $jsUser = CFactory::getUser($user['id']);
            $this->setDebug(print_r($jsUser, true));
            $user['display_name'] = $jsUser->getDisplayName();

            $db->setQuery("
      select #__community_fields.fieldcode as name, #__community_fields_values.value as value
      from   #__community_fields, #__community_fields_values 
      where  #__community_fields.id = #__community_fields_values.field_id
      and    #__community_fields_values.user_id=$user[id]
      ");

            $fields = $db->loadAssocList();
            if ($fields)
                foreach ($fields as $field) {
                    $name = 'js_' . strtolower($field['name']);
                    $value = $field['value'];
                    $user[$name] = $value;
                }
            $this->setDebug(print_r($db, true));
            $this->setDebug(print_r($fields, true));
            $this->setDebug(print_r($user, true));
        } catch (Exception $e) {
            $this->setDebug($e->__toString());
        }
        $this->save();
        return $user;
    }

    function curl_post($url, $data) {
        if (!function_exists('curl_version')) {
            throw new Exception('Curl not loaded, cannot retrieve file.');
        }

        if (is_array($data)) {
            $data = http_build_query($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1); // RETURN HTTP HEADERS
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

        $contents = curl_exec($ch);
        curl_close($ch);

        return $contents;
    }

    function url_retrieve($url, $query = '') {
        if (is_array($query)) $query = http_build_query($query);
        if ($query) $url .= '?' . $query;
        return $this->url_retrieve_curl($url);
    }


    function url_retrieve_curl($url, $timeout = 30) {

        if (!function_exists('curl_version')) {
            throw new Exception('Curl not loaded, cannot retrieve file.');
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        // Getting binary data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $contents = curl_exec($ch);
        curl_close($ch);
        return $contents;
    }

}



