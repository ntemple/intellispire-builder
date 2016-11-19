<?php
/* SVN FILE: $Id: isnid.class.php 21 2013-03-15 19:35:01Z ntemple $*/
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
 * @version    SVN: $Id: isnid.class.php 21 2013-03-15 19:35:01Z ntemple $
 * @since      File available since Release 1.0
 * 
 */

class isnid {

    var $s;
  
    function isnid($id = null, $version = 'N1') {      
      if (is_int($id)) {
        $s = array ('id' => $id, 'isn_version' => $version);
        $s['isn_account'] = self::GetRandomString(5);
        $s['isn_secret']  = self::GetRandomString(8);
        $id = self::isnid_implode($s);        
      } else if (is_array($id)) {
        $id = self::isnid_implode($id);
      }               
      $this->s = self::isnid_explode(trim($id));
            
    }
    
     
    function getISNID() {
      return self::isnid_implode($this->s);
    }
     
    function getISNIDArray() {
      return $this->s;      
    }
     
    function getUserName() {
      return $this->s['id'] . '-' .$this->s['isn_account'];    
    } 

    function getUserId() {
      return $this->s['id'];    
    } 
        
    function getSecret() {
      return $this->s['isn_secret'];    
    }
           
    /* static */ function GetRandomString($length = 8) 
    {

       settype($template, "string");

       // you could repeat the alphabet to get more randomness if needed
       $template = "23456789abcdefghkmnpqrstuvwxyz";

       settype($length, "integer");
       settype($rndstring, "string");
       settype($a, "integer");
       settype($b, "integer");

       for ($a = 0; $a < $length; $a++) {
               $b = rand(0, strlen($template) - 1);
               $rndstring .= $template[$b];
       }

       return $rndstring;
     }
     
     /* static */ function isnid_implode($s) 
     {
       return implode ('-', array($s['id'], $s['isn_account'], $s['isn_version'], $s['isn_secret']) );
     }

      /*static*/ function isnid_explode($i) 
      {
        $s = array();
        $s['isnid']       = $i;
                
        $i = explode('-', $i);        
        $s['id'] = array_shift($i);
        $s['isn_account'] = array_shift($i);
        $s['isn_version'] = array_shift($i);
        $s['isn_secret']  = array_shift($i);


        return $s;
      }
    
}

?>