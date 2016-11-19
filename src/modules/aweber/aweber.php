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

        $addresses = $platform->get_param('autoresponder');
        $sitename  = $platform->get_param('sitename', '');

        if (!$addresses)
            return $platform->warn('Mail Send Failed: No addresses.');
        if (!is_array($user))
            return $platform->warn('Mail Send Failed: invalid user array');

        $aremail = explode(',', $addresses);

        // Create message
        $msg = '';
        foreach ($user as $name => $value) {
            $msg .= "$name: " . $value . "\n";
        }
        $platform->setRequest($msg);

        $success = false;
        foreach ($aremail as $address) {
            $success = $platform->sendmail($user['email'], $user['name'], $address, "New Joomla Signup $sitename", $msg);
        }

        if ($success) {
            return $platform->setResponse('Mail Sent: ' . date(DATE_RFC822));
        } else {
            return $platform->setResponse('Mail Send Failed.');
        }
    }

}
