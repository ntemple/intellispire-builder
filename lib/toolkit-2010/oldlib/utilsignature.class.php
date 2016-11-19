<?php

class utilSignature {
  
    /* static */ function sign($params, $key)
    {
        uksort($params, 'strcasecmp');
        $string_to_sign = '';
        foreach($params as $name => $value) {
            $string_to_sign .= "$name$value";
        }

        return self::signString($string_to_sign, $key);
    }
  
  /* static */ function hasher($data, $key)
    {
        if (strlen($key) > 64) {
            $key = pack("H40", sha1($key));
        }
        if (strlen($key) < 64) {
            $key = str_pad($key, 64, chr(0));
        }
        $ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
        $opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));
        return sha1($opad . pack("H40", sha1($ipad . $data)));
    }

    
    /* static */ function hex2b64($str) 
    {
        $raw = '';
        for ($i=0; $i < strlen($str); $i+=2) {
            $raw .= chr(hexdec(substr($str, $i, 2)));
        }
        return base64_encode($raw);
    }

    /* static */ function signString($stringToSign, $key) 
    {    
      $hash  = self::hasher($stringToSign, $key);
      return self::hex2b64($hash);
    }

}
