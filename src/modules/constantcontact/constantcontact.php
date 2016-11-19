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

        $apikey   = $platform->get_param('apikey');
        $username = $platform->get_param('username');
        $password = $platform->get_param('password');
        $list     = $platform->get_param('list');

        if (is_null($apikey) || empty($apikey)){
            return $platform->warn("The created account has not been exported to ConstactContact service due to invalid API key. Please, re-check your settings");
        }

        $contact = array(
            'EmailAddress' => $user['email'],
            'FirstName' => $user['first_name'],
            'LastName' => $user['last_name'],
        );

        $post = $this->add_contact_xml($contact, $list, $username);

        $auth = $apikey . '%' . $username . ':' . $password;
        $api_url = 'https://api.constantcontact.com/ws/customers/' . $username . '/contacts';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:application/atom+xml"));
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $auth);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , 0);
        $contents = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $platform->setRequest($post);
        $platform->setResponse($contents);

        $warning = $this->validateServiceResponse($contents);
        if ($warning) {
            $platform->warn($warning);
            return false;
        }
        return true;
    }

    function add_contact_xml($data, $list, $username) {
        $updated = date('Y-m-d\TH:i:s\Z');
        $post = <<<end
<entry xmlns="http://www.w3.org/2005/Atom">
	<title type="text"> </title>
	<updated>{$updated}</updated>
	<author></author>
	<id>data:,none</id>
	<summary type="text">Contact</summary>
	<content type="application/vnd.ctct+xml">
		<Contact xmlns="http://ws.constantcontact.com/ns/1.0/">
			<OptInSource>ACTION_BY_CUSTOMER</OptInSource>

end;

        foreach ($data as $key => $val)
            $post .= "\t\t\t<$key>{$val}</$key>\n";

        $post .= <<<end
      <ContactLists>
        <ContactList id="http://api.constantcontact.com/ws/customers/{$username}/lists/{$list}" />
			</ContactLists>
		</Contact>
	</content>
</entry>
end;
        return $post;
    }

    /**
     * The function checks responses from ConstantContact service and returns warning messages
     * in case of different kind of problems
     *
     * @param string $response
     * @return boolean
     */
    function validateServiceResponse($response)  {

        if ($response == null || empty($response)) {
            return "ConstantContact service didn't return any responses after the sent request";
        }

        $API_KEY_NOT_FOUND_MSG = "Application data not found for key";

        // 1. We need to verify that API key has been correctly entered
        if (preg_match("/$API_KEY_NOT_FOUND_MSG/i", $response)){
            return "ConstantContact service has reported about invalid or expired API key";
        }

        return false;
    }


}
