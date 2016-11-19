<?php

class /*{UNIQUEID}*//*{cname}*/MailClass {

    /**
     * The main autoresponder function.
     * This function must:
     * - retrieve any parameters in order to create the "request" to send to the autoresponder, which list, etc
     * - store the request so the webmaster can se what was sent
     * - store any error or success messages in the response
     * @param Platform $platform: the platform API
     * @param array $user: The normalized user array
     */

    function notify($platform, $user) {

        $list_id   = $platform->get_param('list_id');
        $api_key   = $platform->get_param('api_key');
        $doubleopt = $platform->get_param('doubleopt');

        $apiList = explode('-', $api_key);

        $merge_vars = array(
            'FNAME' => $user['first_name'],
            'LNAME' => $user['last_name'],
            'OPTINIP' => $user['registration_ip']
        );

        foreach ($user as $name => $value) {
            $merge_vars[$name] = $value;
        }

        $data = array(
            'method' => 'listSubscribe',
            'apikey' => $api_key,
            'id' => $list_id,

            //@todo map merge fields
            'merge_vars' => $merge_vars,
            'email_address' => $user['email']
        );

        if ($doubleopt == 0) {
            $data['double_optin'] = false;
        } else {
            $data['double_optin'] = true;
        }

        $dc = $apiList[1];
        $data = urlencode(json_encode($data));
        $url = "http://$dc.api.mailchimp.com/1.3/?method=listSubscribe";

        $result = $platform->curl_post($url, $data);
        $platform->setRequest($url . "\n\n" . $data);

        $platform->setResponse($result);
        return true;
    }

}
