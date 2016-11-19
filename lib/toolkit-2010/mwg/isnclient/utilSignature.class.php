<?php
/* SVN FILE: $Id: utilSignature.class.php 21 2013-03-15 19:35:01Z ntemple $*/
/**
 * 
 * ISN - Intellispire Network Client Toolkit
 * Copyright (c) 2008 Nick Temple, Intellispire 
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License. (and no other version)
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * @category   ISN
 * @package    Client
 * @author     Nick Temple <Nick.Temple@intellispire.com>
 * @copyright  2008 Intellispire
 * @license    LGPL 2.1
 * @version    SVN: $Id: utilSignature.class.php 21 2013-03-15 19:35:01Z ntemple $
 * @since      File available since Release 1.0
 * 
 */

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


?>