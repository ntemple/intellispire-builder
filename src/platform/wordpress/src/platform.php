<?php

class /*{UNIQUEID}*//*{cname}*/Platform {

    var $debug = false;

    /**
     * The main autoresponder function.
     * This function calls out to the appropriate auto-responder class
     * @param mixed $user: The normalized user array
     */
    function notify($user) {
        $success = false;

        $userdata = $this->normalize($user);

        try {
            $ar = new /*{UNIQUEID}*//*{cname}*/MailClass();
            $success = $ar->notify($this, $userdata);
        } catch(Exception $e) {
            $this->setResponse($e);
        }

        $this->save();
        return $success;
    }

    function normalize($data) {

        $userdata = array();
        foreach ($userdata as $key => $value) {
            if (is_scalar($value)) {
                $userdata[$key] = $value;
            }
        }

        $userdata['email'] = $data['user_email'];
        $userdata['name'] =  $data['display_name'];

        return $userdata;
    }

    function save() {
        // Save results
    }


    function warn($msg) {
        // Emit warning message
    }

    function sendmail($emailFrom, $nameFrom, $recipient, $subject, $msg) {

        $headers = array();
        $headers[] = "From: $nameFrom <$emailFrom>";
        wp_mail($recipient, $subject, $msg, $headers);
    }

    function get_param($key, $default = null) {
        $options = get_option('bpaweber_options', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }

    function set_param($key, $val) {
        $options = get_option('{lcname}_options', array());
        $options[$key] = $val;

        update_option('{lcname}_options', $options);
        return true;
    }

    function setRequest($val) {
        $val = substr($val, 0, 2048); // protect from large requests
        $this->set_param('request', $val);
        return true;
    }

    function setResponse($val) {
        $val = substr($val, 0, 2048); // protect from large responses
        $this->set_param('response', $val);
        return true;
    }

    function setDebug($val) {
        static $msg = '';
        if (!$this->debug) return true;

        $msg .= $val . "\n";
        $msg = substr($msg, 0, 2048); // protect from large responses
        $this->set_param('debug', $val);

        return true;
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
