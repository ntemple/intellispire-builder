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

        $clientid  = $platform->get_param('clientid');
        $formid    = $platform->get_param('formid');
        $listid    = $platform->get_param('listid');
        $specialid = $platform->get_param('specialid');
        $doubleopt = $platform->get_param('doubleopt');

        $fname = '';
        $lname = '';

        $name = trim($user['name']);
        if (strpos($name, ' ') > 0) {
            $name_parts = explode(' ', $name);
            $fname = array_shift($name_parts);
            $lname = array_pop($name_parts);
        } else {
            $fname = $name;
        }

        $data = array(
            'listid' => trim($listid),
            'fields_email' => trim($user['email']),
            'fields_fname' => $fname,
            'fields_lname' => $lname,
            'clientid' => trim($clientid),
            'formid' => trim($formid),
            'reallistid' => 1,
            'doubleopt' => trim($doubleopt),
        );

        $data['specialid:' . $listid] = $specialid;

        unset($user['email']);
        unset($user['name']);

        foreach ($user as $name => $value) {
            $data['fields_' . $name] = trim($value);
        }

        $msg = '';
        foreach ($data as $name => $value) {
            $msg .= "$name: $value\n";
        }
        $platform->setRequest($msg);


        $result = $platform->curl_post('http://app.icontact.com/icp/signup.php', $data);
        $platform->setResponse($result);
    }

}
