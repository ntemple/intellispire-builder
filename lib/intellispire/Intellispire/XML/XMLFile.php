<?php

namespace Intellispire\XML;

class XMLFile {

   static function parse($string) {
       $xml = new \SimpleXMLElement($string);
       return simplexml2array($xml);
   }


  static function dump($data) {
      return array2xml($data);
  }

}

function simplexml2array($xml) {
   if (is_object($xml) && get_class($xml) == 'SimpleXMLElement') {
       $attributes = $xml->attributes();
       foreach($attributes as $k=>$v) {
           if ($v) $a[$k] = (string) $v;
       }
       $x = $xml;
       $xml = get_object_vars($xml);
   }
   if (is_array($xml)) {
       if (count($xml) == 0) return (string) $x; // for CDATA
       foreach($xml as $key=>$value) {
           $r[$key] = simplexml2array($value);
       }
       if (isset($a)) $r['@'] = $a;    // Attributes
       return $r;
   }
   return (string) $xml;
}


function _array2XML($arr,$root = 'xml') { 
$xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><{$root}></{$root}>"); 
$f = create_function('$f,$c,$a',' 
        foreach($a as $v) { 
            if(isset($v["@text"])) { 
                $ch = $c->addChild($v["@tag"],$v["@text"]); 
            } else { 
                $ch = $c->addChild($v["@tag"]); 
                if(isset($v["@items"])) { 
                    $f($f,$ch,$v["@items"]); 
                } 
            } 
            if(isset($v["@attr"])) { 
                foreach($v["@attr"] as $attr => $val) { 
                    $ch->addAttribute($attr,$val); 
                } 
            } 
        }'); 
$f($f,$xml,$arr); 
return $xml->asXML(); 
} 


/*
 * array2xml() will convert any given array into a XML structure.
 *
 * Version:     1.0
 *
 * Created by:  Marcus Carver © 2008
 *
 * Email:       marcuscarver@gmail.com
 *
 * Link:        http://marcuscarver.blogspot.com/
 *
 * Arguments :  $array      - The array you wish to convert into a XML structure.
 *              $name       - The name you wish to enclose the array in, the 'parent' tag for XML.
 *              $standalone - This will add a document header to identify this solely as a XML document.
 *              $beginning  - INTERNAL USE... DO NOT USE!
 *
 * Return:      Gives a string output in a XML structure
 *
 * Use:         echo array2xml($products,'products');
 *              die;
*/

function array2xml($array, $name='array', $standalone=FALSE, $beginning=TRUE) {

  global $nested;
  $output = '';


  if ($beginning) {
    if ($standalone) header("content-type:text/xml;charset=utf-8");
    $output .= '<'.'?'.'xml version="1.0" encoding="UTF-8"'.'?'.'>' . "\n";
    $output .= '<' . $name . '>' . "\n";
    $nested = 0;
  }
  
  // This is required because XML standards do not allow a tag to start with a number or symbol, you can change this value to whatever you like:
  $ArrayNumberPrefix = 'ARRAY_NUMBER_';
  
  foreach ($array as $root=>$child) {
    if (is_array($child)) {
      $output .= str_repeat(" ", (2 * $nested)) . '  <' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
      $nested++;
      $output .= array2xml($child,NULL,NULL,FALSE);
      $nested--;
      $output .= str_repeat(" ", (2 * $nested)) . '  </' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
    }
    else {
      $output .= str_repeat(" ", (2 * $nested)) . '  <' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '><![CDATA[' . $child . ']]></' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
    }
  }
  
  if ($beginning) $output .= '</' . $name . '>';
  
  return $output;
}
