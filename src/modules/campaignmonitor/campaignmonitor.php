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

        $list_id = $platform->get_param('list_id');
        $api_key = $platform->get_param('api_key');

        $req = new /*{UNIQUEID}*/IS_HttpRequest('http://api.createsend.com/api/v3/subscribers/'.$list_id.'.json',
            /*{UNIQUEID}*/IS_HttpRequest::PUT,
            $api_key . ':awesome',
            json_encode(
                array(
                    'EmailAddress' => $user['email'],
                    'Name' => $user['name'],
                )
        ));

        $platform->setRequest($req->url . "\n\n" . $req->data);

        $success = $req->send();

        $platform->setResponse($success);

        if ($success) {
            return true;
        } else {
            $platform->setResponse('Failed to call API.');
            return false;
        }
    }

}

class /*{UNIQUEID}*/IS_HttpRequest {

    private $properties = array();
    const GET  = 0x000001;
    const POST = 0x000002;
    const PUT  = 0x000003;

    public function __construct($url = false, $type = self::GET, $auth = false, $data = false) {
        if ($url)
            $this->url = $url;

        $this->type = $type;

        if ($data)
            $this->data = $data;

        if ($auth)
            $this->auth = $auth;
        else
            $this->auth = false;
    }

    public function __set($name, $value) {
        if ($name == 'data' && $this->type != self::PUT) {
            $value = http_build_query($value);
        }

        $this->properties[$name] = $value;
    }

    public function __get($name) {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        return false;
    }

    public function send() {
        $ch = curl_init();
        if ($this->type == self::GET) {

            $url = $this->url;

            if (strpos($url, '?') === false) {
                $url .= '?' . $this->data;
            } else {
                $url .= '&' . $this->data;
            }

        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
            $url = $this->url;
        }

        if ($this->auth) curl_setopt($ch, CURLOPT_USERPWD, $this->auth);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}

