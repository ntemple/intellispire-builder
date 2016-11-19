<?php
/**
 * Kris Bailey <kbailey@moderngigabyte.com> 
 *
 * this file creates a serializer/unserializer for each of the following formats:
 * xml
 * json
 * SS_toXML();
 * SS_fromXML();
 * and for JSON and YAML, just switch with XML.
 * 
 * SS_XtoY();
 * where X is one of those and Y is a different one.yaml
 * 
 * SS_toXML();
 * SS_fromXML();
 * 
 * and for JSON and YAML, just switch with XML.
 * 
 * SS_XtoY();
 * where X is one of those and Y is a different one.
 */
 
/**
 * xml:
 */
 
class SS_XML {

	var $arrayData;
	var $stringData;

	function SS_XML($data=""){
		if ($data) $this->setData($data);
	}
	
	function serialize($data=""){
		if ($data) $this->setData($data);
		
		return $this->stringData = $this->toXML($this->arrayData);
	}

	function unserialize($data=""){
		if ($data) $this->setData($data);
	
		$this->arrayData = array();
		$spotCounts = array();
		$tagPath = array();
		$tokens = split("<", $this->stringData);
		unset($tokens[0]);
		
		foreach ($tokens as $token){
			switch ($token[0]){
				case "?":
					continue;
				break;
				case "/":
					end($tagPath);
					unset($tagPath[key($tagPath)]);
				break;
				default:
					$np = split(" |/", substr($token, 0, strpos($token, ">")));
					$tagName = $np[0];
					$tagPath[] = $tagName;
					$value = substr($token, strpos($token, ">")+1);
					$hctp = $this->mtp($tagPath, $spotCounts);
					$spotCounts[$hctp]++;
					$this->addToArray($tagPath, $spotCounts, $value);
					if (strpos($token, ">")-strpos($token, "/>")==1){
						end($tagPath);
						unset($tagPath[key($tagPath)]);
					}
				break;
			}
		}
		return $this->arrayData;
	}

	function toXML($array, $prefix='') {

		$output = "";
		if (count($array) && !is_numeric(key($array))){
			foreach ($array as $k => $v) {
				if((string) $v == "") {
					$output .= $prefix."<$k/>\n";
					continue;
				}
				$output .= $prefix."<$k>";
				if (is_array($v) && !is_numeric(key($v))){
					$output .= "\n".$this->toXML($v, $prefix."\t").$prefix;
				} elseif (is_array($v)){
					for ($i=0; $i<count($v); $i++){
						if ($i>0){
							$output .= "</$k>\n$prefix<$k>";
						}
						if (is_array($v[$i])){
							$output .= "\n".$this->toXML($v[$i], $prefix."\t").$prefix;
						} else {
							$output .= $v[$i];
						}
					}
				} else {
					$output .= $v;
				}
				$output .= "</$k>\n";
			}
		}
		return $output;
	}

	function setData($data=""){
		if (is_array($data)){
			$this->arrayData = $data;
		} else {
			$this->stringData = $data;
		}
	}

	function addToArray($path, $spotCounts, $value){
		$cpa = "";
		$tspot = array();
		$tspot[0] =& $this->arrayData;
		$tsp = 0;
		foreach ($path as $t){
			$at = $spotCounts[$cpa]."|";
			$cpa .= $at.$t."|";
			$sp = $spotCounts[$cpa]-1;
			if (!is_array($tspot[$tsp][$t])) $tspot[$tsp][$t] = array();
			if (!is_array($tspot[$tsp][$t][$sp])) $tspot[$tsp][$t][$sp] = array();
			$tspot[++$tsp] =& $tspot[$tsp-1][$t][$sp];
		}
		$tspot[$tsp] = (ereg_replace("[[:space:]]", "", $value)=="")?"":$value;
	}
	
	function mtp($tp, $spotCounts){
		$path = "";
		foreach ($tp as $t){
			$at = $spotCounts[$path]."|";
			$path .= $at.$t."|";
		}
		return $path;
	}

}

function SS_toXML($array){
	if (!is_array($array))return false;
	$x = new SS_XML($array);
	return $x->serialize();
}

function SS_fromXML($xml){
	if (is_array($xml))return false;
	$x = new SS_XML($xml);
	return $x->unserialize();
}


/**
 * json:
 */
 
class SS_JSON {

	var $arrayData;
	var $stringData;

	function SS_JSON($data=""){
		if ($data) $this->setData($data);
	}
/*	// used for prettiness
	function serialize($data=""){
		if ($data) $this->setData($data);
		
		return $this->stringData = $this->toJSON($this->arrayData);
	} // */

	function serialize($data=""){
		if ($data) $this->setData($data);
		$json = new Services_JSON();
		return $this->stringData = $json->encode($this->arrayData);
	}

	function unserialize($data=""){
		if ($data) $this->setData($data);
		$json = new Services_JSON();
		return $this->arrayData = $this->arrayize(get_object_vars($json->decode($this->stringData)));
	}


	function arrayize($array){
		if (!is_array($array)) return $array;
		foreach ($array as $k=>$item){
			if (is_object($item)){
				$array[$k] = $this->arrayize(get_object_vars($item));
			} else {
				$array[$k] = $this->arrayize($item);
			}
		}
		return $array;
	}

	function toJSON($array, $prefix="") {
		$output = array();
		
		foreach ($array as $k=>$v){
			if (is_array($v) || is_object($v)){
				$output[] = "\n".$prefix.'"'.str_replace("\"", "\\\"", $k).'" : '.$this->toJSON($v, "\t".$prefix);
			} else {
				$output[] = "\n".$prefix.'"'.str_replace("\"", "\\\"", $k).'" : "'.str_replace("\"", "\\\"", $v).'"';
			}
		}
		return "{".@implode(",", $output)."\n".$prefix."}";
	}

	function setData($data=""){
		if (is_array($data)){
			$this->arrayData = $data;
		} else {
			$this->stringData = $data;
		}
	}


}

function SS_toJSON($array){
	if (!is_array($array))return false;
	$x = new SS_JSON($array);
	return $x->serialize();
}

function SS_fromJSON($json){
	if (is_array($json))return false;
	$x = new SS_JSON($json);
	return $x->unserialize();
}


/**
 * yaml:
 */
 
class SS_YAML {

	var $arrayData;
	var $stringData;

	function SS_YAML($data=""){
		if ($data) $this->setData($data);
	}
	
	function serialize($data=""){
		if ($data) $this->setData($data);
		$spyc = new Spyc();
		return $this->stringData = $spyc->dump($this->arrayData, false, false);
	}

	function unserialize($data=""){
		if ($data) $this->setData($data);
	
		$spyc = new Spyc();
		return $this->arrayData = $spyc->load($this->stringData);
	}

	function setData($data=""){
		if (is_array($data)){
			$this->arrayData = $data;
		} else {
			$this->stringData = $data;
		}
	}


}

function SS_toYAML($array){
	if (!is_array($array))return false;
	$x = new SS_YAML($array);
	return $x->serialize();
}

function SS_fromYAML($yaml){
	if (is_array($yaml))return false;
	$x = new SS_YAML($yaml);
	return $x->unserialize();
}




// functions to convert
function SS_XMLtoYAML($xml, $clean=1){
	return SS_toYAML(($clean)?SS_cleanXMLArray(SS_fromXML($xml)):SS_fromXML($xml));
}

function SS_XMLtoJSON($xml, $clean=1){
	return SS_toJSON(($clean)?SS_cleanXMLArray(SS_fromXML($xml)):SS_fromXML($xml));
}

function SS_YAMLtoXML($yaml){
	return SS_toXML(SS_fromYAML($yaml));
}

function SS_YAMLtoJSON($yaml){
	return SS_toJSON(SS_fromYAML($yaml));
}

function SS_JSONtoXML($json){
	return SS_toXML(SS_fromJSON($json));
}

function SS_JSONtoYAML($json){
	return SS_toYAML(SS_fromJSON($json));
}



/*************************
 * helper classes for various things
 * I didn't write these, they are just bsd licensed code from various places, code headers in tact...
 */

function SS_cleanXMLArray($array){
	if (is_array($array)){
		foreach ($array as $k=>$a){
			if (is_array($a) && count($a)==1 && isset($a[0])){
				$array[$k] = SS_cleanXMLArray($a[0]);
			}
		}
	}
	return $array;
}


/**
 * Converts to and from JSON format.
 *
 * JSON (JavaScript Object Notation) is a lightweight data-interchange
 * format. It is easy for humans to read and write. It is easy for machines
 * to parse and generate. It is based on a subset of the JavaScript
 * Programming Language, Standard ECMA-262 3rd Edition - December 1999.
 * This feature can also be found in  Python. JSON is a text format that is
 * completely language independent but uses conventions that are familiar
 * to programmers of the C-family of languages, including C, C++, C#, Java,
 * JavaScript, Perl, TCL, and many others. These properties make JSON an
 * ideal data-interchange language.
 *
 * This package provides a simple encoder and decoder for JSON notation. It
 * is intended for use with client-side Javascript applications that make
 * use of HTTPRequest to perform server communication functions - data can
 * be encoded into JSON notation for use in a client-side javascript, or
 * decoded from incoming Javascript requests. JSON format is native to
 * Javascript, and can be directly eval()'ed with no further parsing
 * overhead
 *
 * All strings should be in ASCII or UTF-8 format!
 *
 * LICENSE: Redistribution and use in source and binary forms, with or
 * without modification, are permitted provided that the following
 * conditions are met: Redistributions of source code must retain the
 * above copyright notice, this list of conditions and the following
 * disclaimer. Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the
 * distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN
 * NO EVENT SHALL CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
 * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
 * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @category
 * @package     Services_JSON
 * @author      Michal Migurski <mike-json@teczno.com>
 * @author      Matt Knapp <mdknapp[at]gmail[dot]com>
 * @author      Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
 * @copyright   2005 Michal Migurski
 * @version     CVS: $Id: serializers.php 21 2013-03-15 19:35:01Z ntemple $
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @link        http://pear.php.net/pepr/pepr-proposal-show.php?id=198
 */

/**
 * Marker constant for Services_JSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_SLICE',   1);

/**
 * Marker constant for Services_JSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_STR',  2);

/**
 * Marker constant for Services_JSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_ARR',  3);

/**
 * Marker constant for Services_JSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_OBJ',  4);

/**
 * Marker constant for Services_JSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_CMT', 5);

/**
 * Behavior switch for Services_JSON::decode()
 */
define('SERVICES_JSON_LOOSE_TYPE', 16);

/**
 * Behavior switch for Services_JSON::decode()
 */
define('SERVICES_JSON_SUPPRESS_ERRORS', 32);

/**
 * Converts to and from JSON format.
 *
 * Brief example of use:
 *
 * <code>
 * // create a new instance of Services_JSON
 * $json = new Services_JSON();
 *
 * // convert a complexe value to JSON notation, and send it to the browser
 * $value = array('foo', 'bar', array(1, 2, 'baz'), array(3, array(4)));
 * $output = $json->encode($value);
 *
 * print($output);
 * // prints: ["foo","bar",[1,2,"baz"],[3,[4]]]
 *
 * // accept incoming POST data, assumed to be in JSON notation
 * $input = file_get_contents('php://input', 1000000);
 * $value = $json->decode($input);
 * </code>
 */
class Services_JSON
{
   /**
    * constructs a new JSON instance
    *
    * @param    int     $use    object behavior flags; combine with boolean-OR
    *
    *                           possible values:
    *                           - SERVICES_JSON_LOOSE_TYPE:  loose typing.
    *                                   "{...}" syntax creates associative arrays
    *                                   instead of objects in decode().
    *                           - SERVICES_JSON_SUPPRESS_ERRORS:  error suppression.
    *                                   Values which can't be encoded (e.g. resources)
    *                                   appear as NULL instead of throwing errors.
    *                                   By default, a deeply-nested resource will
    *                                   bubble up with an error, so all return values
    *                                   from encode() should be checked with isError()
    */
    function Services_JSON($use = 0)
    {
        $this->use = $use;
    }

   /**
    * convert a string from one UTF-16 char to one UTF-8 char
    *
    * Normally should be handled by mb_convert_encoding, but
    * provides a slower PHP-only method for installations
    * that lack the multibye string extension.
    *
    * @param    string  $utf16  UTF-16 character
    * @return   string  UTF-8 character
    * @access   private
    */
    function utf162utf8($utf16)
    {
        // oh please oh please oh please oh please oh please
        if(function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
        }

        $bytes = (ord($utf16{0}) << 8) | ord($utf16{1});

        switch(true) {
            case ((0x7F & $bytes) == $bytes):
                // this case should never be reached, because we are in ASCII range
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x7F & $bytes);

            case (0x07FF & $bytes) == $bytes:
                // return a 2-byte UTF-8 character
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0xC0 | (($bytes >> 6) & 0x1F))
                     . chr(0x80 | ($bytes & 0x3F));

            case (0xFFFF & $bytes) == $bytes:
                // return a 3-byte UTF-8 character
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0xE0 | (($bytes >> 12) & 0x0F))
                     . chr(0x80 | (($bytes >> 6) & 0x3F))
                     . chr(0x80 | ($bytes & 0x3F));
        }

        // ignoring UTF-32 for now, sorry
        return '';
    }

   /**
    * convert a string from one UTF-8 char to one UTF-16 char
    *
    * Normally should be handled by mb_convert_encoding, but
    * provides a slower PHP-only method for installations
    * that lack the multibye string extension.
    *
    * @param    string  $utf8   UTF-8 character
    * @return   string  UTF-16 character
    * @access   private
    */
    function utf82utf16($utf8)
    {
        // oh please oh please oh please oh please oh please
        if(function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
        }

        switch(strlen($utf8)) {
            case 1:
                // this case should never be reached, because we are in ASCII range
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return $utf8;

            case 2:
                // return a UTF-16 character from a 2-byte UTF-8 char
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x07 & (ord($utf8{0}) >> 2))
                     . chr((0xC0 & (ord($utf8{0}) << 6))
                         | (0x3F & ord($utf8{1})));

            case 3:
                // return a UTF-16 character from a 3-byte UTF-8 char
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr((0xF0 & (ord($utf8{0}) << 4))
                         | (0x0F & (ord($utf8{1}) >> 2)))
                     . chr((0xC0 & (ord($utf8{1}) << 6))
                         | (0x7F & ord($utf8{2})));
        }

        // ignoring UTF-32 for now, sorry
        return '';
    }

   /**
    * encodes an arbitrary variable into JSON format
    *
    * @param    mixed   $var    any number, boolean, string, array, or object to be encoded.
    *                           see argument 1 to Services_JSON() above for array-parsing behavior.
    *                           if var is a strng, note that encode() always expects it
    *                           to be in ASCII or UTF-8 format!
    *
    * @return   mixed   JSON string representation of input var or an error if a problem occurs
    * @access   public
    */
    function encode($var)
    {
        switch (gettype($var)) {
            case 'boolean':
                return $var ? 'true' : 'false';

            case 'NULL':
                return 'null';

            case 'integer':
                return (int) $var;

            case 'double':
            case 'float':
                return (float) $var;

            case 'string':
                // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
                $ascii = '';
                $strlen_var = strlen($var);

               /*
                * Iterate over every character in the string,
                * escaping with a slash or encoding to UTF-8 where necessary
                */
                for ($c = 0; $c < $strlen_var; ++$c) {

                    $ord_var_c = ord($var{$c});

                    switch (true) {
                        case $ord_var_c == 0x08:
                            $ascii .= '\b';
                            break;
                        case $ord_var_c == 0x09:
                            $ascii .= '\t';
                            break;
                        case $ord_var_c == 0x0A:
                            $ascii .= '\n';
                            break;
                        case $ord_var_c == 0x0C:
                            $ascii .= '\f';
                            break;
                        case $ord_var_c == 0x0D:
                            $ascii .= '\r';
                            break;

                        case $ord_var_c == 0x22:
                        case $ord_var_c == 0x2F:
                        case $ord_var_c == 0x5C:
                            // double quote, slash, slosh
                            $ascii .= '\\'.$var{$c};
                            break;

                        case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                            // characters U-00000000 - U-0000007F (same as ASCII)
                            $ascii .= $var{$c};
                            break;

                        case (($ord_var_c & 0xE0) == 0xC0):
                            // characters U-00000080 - U-000007FF, mask 110XXXXX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c, ord($var{$c + 1}));
                            $c += 1;
                            $utf16 = $this->utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;

                        case (($ord_var_c & 0xF0) == 0xE0):
                            // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c + 1}),
                                         ord($var{$c + 2}));
                            $c += 2;
                            $utf16 = $this->utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;

                        case (($ord_var_c & 0xF8) == 0xF0):
                            // characters U-00010000 - U-001FFFFF, mask 11110XXX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c + 1}),
                                         ord($var{$c + 2}),
                                         ord($var{$c + 3}));
                            $c += 3;
                            $utf16 = $this->utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;

                        case (($ord_var_c & 0xFC) == 0xF8):
                            // characters U-00200000 - U-03FFFFFF, mask 111110XX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c + 1}),
                                         ord($var{$c + 2}),
                                         ord($var{$c + 3}),
                                         ord($var{$c + 4}));
                            $c += 4;
                            $utf16 = $this->utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;

                        case (($ord_var_c & 0xFE) == 0xFC):
                            // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c + 1}),
                                         ord($var{$c + 2}),
                                         ord($var{$c + 3}),
                                         ord($var{$c + 4}),
                                         ord($var{$c + 5}));
                            $c += 5;
                            $utf16 = $this->utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                    }
                }

                return '"'.$ascii.'"';

            case 'array':
               /*
                * As per JSON spec if any array key is not an integer
                * we must treat the the whole array as an object. We
                * also try to catch a sparsely populated associative
                * array with numeric keys here because some JS engines
                * will create an array with empty indexes up to
                * max_index which can cause memory issues and because
                * the keys, which may be relevant, will be remapped
                * otherwise.
                *
                * As per the ECMA and JSON specification an object may
                * have any string as a property. Unfortunately due to
                * a hole in the ECMA specification if the key is a
                * ECMA reserved word or starts with a digit the
                * parameter is only accessible using ECMAScript's
                * bracket notation.
                */

                // treat as a JSON object
                if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {
                    $properties = array_map(array($this, 'name_value'),
                                            array_keys($var),
                                            array_values($var));

                    foreach($properties as $property) {
                        if(Services_JSON::isError($property)) {
                            return $property;
                        }
                    }

                    return '{' . join(',', $properties) . '}';
                }

                // treat it like a regular array
                $elements = array_map(array($this, 'encode'), $var);

                foreach($elements as $element) {
                    if(Services_JSON::isError($element)) {
                        return $element;
                    }
                }

                return '[' . join(',', $elements) . ']';

            case 'object':
                $vars = get_object_vars($var);

                $properties = array_map(array($this, 'name_value'),
                                        array_keys($vars),
                                        array_values($vars));

                foreach($properties as $property) {
                    if(Services_JSON::isError($property)) {
                        return $property;
                    }
                }

                return '{' . join(',', $properties) . '}';

            default:
                return ($this->use & SERVICES_JSON_SUPPRESS_ERRORS)
                    ? 'null'
                    : new Services_JSON_Error(gettype($var)." can not be encoded as JSON string");
        }
    }

   /**
    * array-walking function for use in generating JSON-formatted name-value pairs
    *
    * @param    string  $name   name of key to use
    * @param    mixed   $value  reference to an array element to be encoded
    *
    * @return   string  JSON-formatted name-value pair, like '"name":value'
    * @access   private
    */
    function name_value($name, $value)
    {
        $encoded_value = $this->encode($value);

        if(Services_JSON::isError($encoded_value)) {
            return $encoded_value;
        }

        return $this->encode(strval($name)) . ':' . $encoded_value;
    }

   /**
    * reduce a string by removing leading and trailing comments and whitespace
    *
    * @param    $str    string      string value to strip of comments and whitespace
    *
    * @return   string  string value stripped of comments and whitespace
    * @access   private
    */
    function reduce_string($str)
    {
        $str = preg_replace(array(

                // eliminate single line comments in '// ...' form
                '#^\s*//(.+)$#m',

                // eliminate multi-line comments in '/* ... */' form, at start of string
                '#^\s*/\*(.+)\*/#Us',

                // eliminate multi-line comments in '/* ... */' form, at end of string
                '#/\*(.+)\*/\s*$#Us'

            ), '', $str);

        // eliminate extraneous space
        return trim($str);
    }

   /**
    * decodes a JSON string into appropriate variable
    *
    * @param    string  $str    JSON-formatted string
    *
    * @return   mixed   number, boolean, string, array, or object
    *                   corresponding to given JSON input string.
    *                   See argument 1 to Services_JSON() above for object-output behavior.
    *                   Note that decode() always returns strings
    *                   in ASCII or UTF-8 format!
    * @access   public
    */
    function decode($str)
    {
        $str = $this->reduce_string($str);

        switch (strtolower($str)) {
            case 'true':
                return true;

            case 'false':
                return false;

            case 'null':
                return null;

            default:
                $m = array();

                if (is_numeric($str)) {
                    // Lookie-loo, it's a number

                    // This would work on its own, but I'm trying to be
                    // good about returning integers where appropriate:
                    // return (float)$str;

                    // Return float or int, as appropriate
                    return ((float)$str == (integer)$str)
                        ? (integer)$str
                        : (float)$str;

                } elseif (preg_match('/^("|\').*(\1)$/s', $str, $m) && $m[1] == $m[2]) {
                    // STRINGS RETURNED IN UTF-8 FORMAT
                    $delim = substr($str, 0, 1);
                    $chrs = substr($str, 1, -1);
                    $utf8 = '';
                    $strlen_chrs = strlen($chrs);

                    for ($c = 0; $c < $strlen_chrs; ++$c) {

                        $substr_chrs_c_2 = substr($chrs, $c, 2);
                        $ord_chrs_c = ord($chrs{$c});

                        switch (true) {
                            case $substr_chrs_c_2 == '\b':
                                $utf8 .= chr(0x08);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\t':
                                $utf8 .= chr(0x09);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\n':
                                $utf8 .= chr(0x0A);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\f':
                                $utf8 .= chr(0x0C);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\r':
                                $utf8 .= chr(0x0D);
                                ++$c;
                                break;

                            case $substr_chrs_c_2 == '\\"':
                            case $substr_chrs_c_2 == '\\\'':
                            case $substr_chrs_c_2 == '\\\\':
                            case $substr_chrs_c_2 == '\\/':
                                if (($delim == '"' && $substr_chrs_c_2 != '\\\'') ||
                                   ($delim == "'" && $substr_chrs_c_2 != '\\"')) {
                                    $utf8 .= $chrs{++$c};
                                }
                                break;

                            case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6)):
                                // single, escaped unicode character
                                $utf16 = chr(hexdec(substr($chrs, ($c + 2), 2)))
                                       . chr(hexdec(substr($chrs, ($c + 4), 2)));
                                $utf8 .= $this->utf162utf8($utf16);
                                $c += 5;
                                break;

                            case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
                                $utf8 .= $chrs{$c};
                                break;

                            case ($ord_chrs_c & 0xE0) == 0xC0:
                                // characters U-00000080 - U-000007FF, mask 110XXXXX
                                //see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 2);
                                ++$c;
                                break;

                            case ($ord_chrs_c & 0xF0) == 0xE0:
                                // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 3);
                                $c += 2;
                                break;

                            case ($ord_chrs_c & 0xF8) == 0xF0:
                                // characters U-00010000 - U-001FFFFF, mask 11110XXX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 4);
                                $c += 3;
                                break;

                            case ($ord_chrs_c & 0xFC) == 0xF8:
                                // characters U-00200000 - U-03FFFFFF, mask 111110XX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 5);
                                $c += 4;
                                break;

                            case ($ord_chrs_c & 0xFE) == 0xFC:
                                // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 6);
                                $c += 5;
                                break;

                        }

                    }

                    return $utf8;

                } elseif (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str)) {
                    // array, or object notation

                    if ($str{0} == '[') {
                        $stk = array(SERVICES_JSON_IN_ARR);
                        $arr = array();
                    } else {
                        if ($this->use & SERVICES_JSON_LOOSE_TYPE) {
                            $stk = array(SERVICES_JSON_IN_OBJ);
                            $obj = array();
                        } else {
                            $stk = array(SERVICES_JSON_IN_OBJ);
                            $obj = new stdClass();
                        }
                    }

                    array_push($stk, array('what'  => SERVICES_JSON_SLICE,
                                           'where' => 0,
                                           'delim' => false));

                    $chrs = substr($str, 1, -1);
                    $chrs = $this->reduce_string($chrs);

                    if ($chrs == '') {
                        if (reset($stk) == SERVICES_JSON_IN_ARR) {
                            return $arr;

                        } else {
                            return $obj;

                        }
                    }

                    //print("\nparsing {$chrs}\n");

                    $strlen_chrs = strlen($chrs);

                    for ($c = 0; $c <= $strlen_chrs; ++$c) {

                        $top = end($stk);
                        $substr_chrs_c_2 = substr($chrs, $c, 2);

                        if (($c == $strlen_chrs) || (($chrs{$c} == ',') && ($top['what'] == SERVICES_JSON_SLICE))) {
                            // found a comma that is not inside a string, array, etc.,
                            // OR we've reached the end of the character list
                            $slice = substr($chrs, $top['where'], ($c - $top['where']));
                            array_push($stk, array('what' => SERVICES_JSON_SLICE, 'where' => ($c + 1), 'delim' => false));
                            //print("Found split at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                            if (reset($stk) == SERVICES_JSON_IN_ARR) {
                                // we are in an array, so just push an element onto the stack
                                array_push($arr, $this->decode($slice));

                            } elseif (reset($stk) == SERVICES_JSON_IN_OBJ) {
                                // we are in an object, so figure
                                // out the property name and set an
                                // element in an associative array,
                                // for now
                                $parts = array();
                                
                                if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                    // "name":value pair
                                    $key = $this->decode($parts[1]);
                                    $val = $this->decode($parts[2]);

                                    if ($this->use & SERVICES_JSON_LOOSE_TYPE) {
                                        $obj[$key] = $val;
                                    } else {
                                        $obj->$key = $val;
                                    }
                                } elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                    // name:value pair, where name is unquoted
                                    $key = $parts[1];
                                    $val = $this->decode($parts[2]);

                                    if ($this->use & SERVICES_JSON_LOOSE_TYPE) {
                                        $obj[$key] = $val;
                                    } else {
                                        $obj->$key = $val;
                                    }
                                }

                            }

                        } elseif ((($chrs{$c} == '"') || ($chrs{$c} == "'")) && ($top['what'] != SERVICES_JSON_IN_STR)) {
                            // found a quote, and we are not inside a string
                            array_push($stk, array('what' => SERVICES_JSON_IN_STR, 'where' => $c, 'delim' => $chrs{$c}));
                            //print("Found start of string at {$c}\n");

                        } elseif (($chrs{$c} == $top['delim']) &&
                                 ($top['what'] == SERVICES_JSON_IN_STR) &&
                                 ((strlen(substr($chrs, 0, $c)) - strlen(rtrim(substr($chrs, 0, $c), '\\'))) % 2 != 1)) {
                            // found a quote, we're in a string, and it's not escaped
                            // we know that it's not escaped becase there is _not_ an
                            // odd number of backslashes at the end of the string so far
                            array_pop($stk);
                            //print("Found end of string at {$c}: ".substr($chrs, $top['where'], (1 + 1 + $c - $top['where']))."\n");

                        } elseif (($chrs{$c} == '[') &&
                                 in_array($top['what'], array(SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ))) {
                            // found a left-bracket, and we are in an array, object, or slice
                            array_push($stk, array('what' => SERVICES_JSON_IN_ARR, 'where' => $c, 'delim' => false));
                            //print("Found start of array at {$c}\n");

                        } elseif (($chrs{$c} == ']') && ($top['what'] == SERVICES_JSON_IN_ARR)) {
                            // found a right-bracket, and we're in an array
                            array_pop($stk);
                            //print("Found end of array at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                        } elseif (($chrs{$c} == '{') &&
                                 in_array($top['what'], array(SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ))) {
                            // found a left-brace, and we are in an array, object, or slice
                            array_push($stk, array('what' => SERVICES_JSON_IN_OBJ, 'where' => $c, 'delim' => false));
                            //print("Found start of object at {$c}\n");

                        } elseif (($chrs{$c} == '}') && ($top['what'] == SERVICES_JSON_IN_OBJ)) {
                            // found a right-brace, and we're in an object
                            array_pop($stk);
                            //print("Found end of object at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                        } elseif (($substr_chrs_c_2 == '/*') &&
                                 in_array($top['what'], array(SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ))) {
                            // found a comment start, and we are in an array, object, or slice
                            array_push($stk, array('what' => SERVICES_JSON_IN_CMT, 'where' => $c, 'delim' => false));
                            $c++;
                            //print("Found start of comment at {$c}\n");

                        } elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == SERVICES_JSON_IN_CMT)) {
                            // found a comment end, and we're in one now
                            array_pop($stk);
                            $c++;

                            for ($i = $top['where']; $i <= $c; ++$i)
                                $chrs = substr_replace($chrs, ' ', $i, 1);

                            //print("Found end of comment at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                        }

                    }

                    if (reset($stk) == SERVICES_JSON_IN_ARR) {
                        return $arr;

                    } elseif (reset($stk) == SERVICES_JSON_IN_OBJ) {
                        return $obj;

                    }

                }
        }
    }

    /**
     * @todo Ultimately, this should just call PEAR::isError()
     */
    function isError($data, $code = null)
    {
		if (is_object($data) && (get_class($data) == 'services_json_error' ||
                                 is_subclass_of($data, 'services_json_error'))) {
            return true;
        }

        return false;
    }
}

/**
 * @todo Ultimately, this class shall be descended from PEAR_Error
 */
class Services_JSON_Error
{
	function Services_JSON_Error($message = 'unknown error', $code = null,
									$mode = null, $options = null, $userinfo = null)
	{

	}
}



/**
* Spyc -- A Simple PHP YAML Class
* @version 0.2.(5) -- 2006-12-31
* @author Chris Wanstrath <chris@ozmm.org>
* @author Vlad Andersen <vlad@oneiros.ru>
* @link http://spyc.sourceforge.net/
* @copyright Copyright 2005-2006 Chris Wanstrath
* @license http://www.opensource.org/licenses/mit-license.php MIT License
* @package Spyc
*/

/**
* A node, used by Spyc for parsing YAML.
* @package Spyc
*/
class YAMLNode {
	/**#@+
	* @access public
	* @var string
	*/
	var $parent;
	var $id;
	/**#@+*/
	/**
	* @access public
	* @var mixed
	*/
	var $data;
	/**
	* @access public
	* @var int
	*/
	var $indent;
	/**
	* @access public
	* @var bool
	*/
	var $children = false;

	/**
	* The constructor assigns the node a unique ID.
	* @access public
	* @return void
	*/
	function YAMLNode($nodeId) {
		$this->id = $nodeId;
	}
}

/**
 * The Simple PHP YAML Class.
 *
 * This class can be used to read a YAML file and convert its contents
 * into a PHP array.  It currently supports a very limited subsection of
 * the YAML spec.
 *
 * Usage:
 * <code>
 *   $parser = new Spyc;
 *   $array  = $parser->load($file);
 * </code>
 * @package Spyc
 */
class Spyc {

	/**
	* Load YAML into a PHP array statically
	*
	* The load method, when supplied with a YAML stream (string or file),
	* will do its best to convert YAML in a file into a PHP array.  Pretty
	* simple.
	*  Usage:
	*  <code>
	*   $array = Spyc::YAMLLoad('lucky.yaml');
	*   print_r($array);
	*  </code>
	* @access public
	* @return array
	* @param string $input Path of YAML file or string containing YAML
	*/
	function YAMLLoad($input) {
		$spyc = new Spyc;
		return $spyc->load($input);
	}

	/**
	* Dump YAML from PHP array statically
	*
	* The dump method, when supplied with an array, will do its best
	* to convert the array into friendly YAML.  Pretty simple.  Feel free to
	* save the returned string as nothing.yaml and pass it around.
	*
	* Oh, and you can decide how big the indent is and what the wordwrap
	* for folding is.  Pretty cool -- just pass in 'false' for either if
	* you want to use the default.
	*
	* Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
	* you can turn off wordwrap by passing in 0.
	*
	* @access public
	* @return string
	* @param array $array PHP array
	* @param int $indent Pass in false to use the default, which is 2
	* @param int $wordwrap Pass in 0 for no wordwrap, false for default (40)
	*/
	function YAMLDump($array,$indent = false,$wordwrap = false) {
		$spyc = new Spyc;
		return $spyc->dump($array,$indent,$wordwrap);
	}

	/**
	* Load YAML into a PHP array from an instantiated object
	*
	* The load method, when supplied with a YAML stream (string or file path),
	* will do its best to convert the YAML into a PHP array.  Pretty simple.
	*  Usage:
	*  <code>
	*   $parser = new Spyc;
	*   $array  = $parser->load('lucky.yaml');
	*   print_r($array);
	*  </code>
	* @access public
	* @return array
	* @param string $input Path of YAML file or string containing YAML
	*/
	function load($input) {
	// See what type of input we're talking about
	// If it's not a file, assume it's a string
	if (!empty($input) && (strpos($input, "\n") === false)
		&& file_exists($input)) {
		$yaml = file($input);
	} else {
		$yaml = explode("\n",$input);
	}
	// Initiate some objects and values
	$base              = new YAMLNode (1);
	$base->indent      = 0;
	$this->_lastIndent = 0;
	$this->_lastNode   = $base->id;
	$this->_inBlock    = false;
	$this->_isInline   = false;
	$this->_nodeId     = 2;

	foreach ($yaml as $linenum => $line) {
		$ifchk = trim($line);

		// If the line starts with a tab (instead of a space), throw a fit.
		if (preg_match('/^(\t)+(\w+)/', $line)) {
		$err = 'ERROR: Line '. ($linenum + 1) .' in your input YAML begins'.
				' with a tab.  YAML only recognizes spaces.  Please reformat.';
		die($err);
		}

		if ($this->_inBlock === false && empty($ifchk)) {
		continue;
		} elseif ($this->_inBlock == true && empty($ifchk)) {
		$last =& $this->_allNodes[$this->_lastNode];
		$last->data[key($last->data)] .= "\n";
		} elseif ($ifchk{0} != '#' && substr($ifchk,0,3) != '---') {
		// Create a new node and get its indent
		$node         = new YAMLNode ($this->_nodeId);
		$this->_nodeId++;

		$node->indent = $this->_getIndent($line);

		// Check where the node lies in the hierarchy
		if ($this->_lastIndent == $node->indent) {
			// If we're in a block, add the text to the parent's data
			if ($this->_inBlock === true) {
			$parent =& $this->_allNodes[$this->_lastNode];
			$parent->data[key($parent->data)] .= trim($line).$this->_blockEnd;
			} else {
			// The current node's parent is the same as the previous node's
			if (isset($this->_allNodes[$this->_lastNode])) {
				$node->parent = $this->_allNodes[$this->_lastNode]->parent;
			}
			}
		} elseif ($this->_lastIndent < $node->indent) {
			if ($this->_inBlock === true) {
			$parent =& $this->_allNodes[$this->_lastNode];
			$parent->data[key($parent->data)] .= trim($line).$this->_blockEnd;
			} elseif ($this->_inBlock === false) {
			// The current node's parent is the previous node
			$node->parent = $this->_lastNode;

			// If the value of the last node's data was > or | we need to
			// start blocking i.e. taking in all lines as a text value until
			// we drop our indent.
			$parent =& $this->_allNodes[$node->parent];
			$this->_allNodes[$node->parent]->children = true;
			if (is_array($parent->data)) {
				$chk = '';
				if (isset ($parent->data[key($parent->data)]))
					$chk = $parent->data[key($parent->data)];
				if ($chk === '>') {
				$this->_inBlock  = true;
				$this->_blockEnd = ' ';
				$parent->data[key($parent->data)] =
						str_replace('>','',$parent->data[key($parent->data)]);
				$parent->data[key($parent->data)] .= trim($line).' ';
				$this->_allNodes[$node->parent]->children = false;
				$this->_lastIndent = $node->indent;
				} elseif ($chk === '|') {
				$this->_inBlock  = true;
				$this->_blockEnd = "\n";
				$parent->data[key($parent->data)] =
						str_replace('|','',$parent->data[key($parent->data)]);
				$parent->data[key($parent->data)] .= trim($line)."\n";
				$this->_allNodes[$node->parent]->children = false;
				$this->_lastIndent = $node->indent;
				}
			}
			}
		} elseif ($this->_lastIndent > $node->indent) {
			// Any block we had going is dead now
			if ($this->_inBlock === true) {
			$this->_inBlock = false;
			if ($this->_blockEnd = "\n") {
				$last =& $this->_allNodes[$this->_lastNode];
				$last->data[key($last->data)] =
					trim($last->data[key($last->data)]);
			}
			}

			// We don't know the parent of the node so we have to find it
			// foreach ($this->_allNodes as $n) {
			foreach ($this->_indentSort[$node->indent] as $n) {
			if ($n->indent == $node->indent) {
				$node->parent = $n->parent;
			}
			}
		}

		if ($this->_inBlock === false) {
			// Set these properties with information from our current node
			$this->_lastIndent = $node->indent;
			// Set the last node
			$this->_lastNode = $node->id;
			// Parse the YAML line and return its data
			$node->data = $this->_parseLine($line);
			// Add the node to the master list
			$this->_allNodes[$node->id] = $node;
			// Add a reference to the parent list
			$this->_allParent[intval($node->parent)][] = $node->id;
			// Add a reference to the node in an indent array
			$this->_indentSort[$node->indent][] =& $this->_allNodes[$node->id];
			// Add a reference to the node in a References array if this node
			// has a YAML reference in it.
			if (
			( (is_array($node->data)) &&
				isset($node->data[key($node->data)]) &&
				(!is_array($node->data[key($node->data)])) )
			&&
			( (preg_match('/^&([^ ]+)/',$node->data[key($node->data)]))
				||
				(preg_match('/^\*([^ ]+)/',$node->data[key($node->data)])) )
			) {
				$this->_haveRefs[] =& $this->_allNodes[$node->id];
			} elseif (
			( (is_array($node->data)) &&
				isset($node->data[key($node->data)]) &&
				(is_array($node->data[key($node->data)])) )
			) {
			// Incomplete reference making code.  Ugly, needs cleaned up.
			foreach ($node->data[key($node->data)] as $d) {
				if ( !is_array($d) &&
				( (preg_match('/^&([^ ]+)/',$d))
					||
					(preg_match('/^\*([^ ]+)/',$d)) )
				) {
					$this->_haveRefs[] =& $this->_allNodes[$node->id];
				}
			}
			}
		}
		}
	}
	unset($node);

	// Here we travel through node-space and pick out references (& and *)
	$this->_linkReferences();

	// Build the PHP array out of node-space
	$trunk = $this->_buildArray();
	return $trunk;
	}

	/**
	* Dump PHP array to YAML
	*
	* The dump method, when supplied with an array, will do its best
	* to convert the array into friendly YAML.  Pretty simple.  Feel free to
	* save the returned string as tasteful.yaml and pass it around.
	*
	* Oh, and you can decide how big the indent is and what the wordwrap
	* for folding is.  Pretty cool -- just pass in 'false' for either if
	* you want to use the default.
	*
	* Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
	* you can turn off wordwrap by passing in 0.
	*
	* @access public
	* @return string
	* @param array $array PHP array
	* @param int $indent Pass in false to use the default, which is 2
	* @param int $wordwrap Pass in 0 for no wordwrap, false for default (40)
	*/
	function dump($array,$indent = false,$wordwrap = false) {
	// Dumps to some very clean YAML.  We'll have to add some more features
	// and options soon.  And better support for folding.

	// New features and options.
	if ($indent === false or !is_numeric($indent)) {
		$this->_dumpIndent = 2;
	} else {
		$this->_dumpIndent = $indent;
	}

	if ($wordwrap === false or !is_numeric($wordwrap)) {
		$this->_dumpWordWrap = 40;
	} else {
		$this->_dumpWordWrap = $wordwrap;
	}

	// New YAML document
	$string = "---\n";

	// Start at the base of the array and move through it.
	foreach ($array as $key => $value) {
		$string .= $this->_yamlize($key,$value,0);
	}
	return $string;
	}

	/**** Private Properties ****/

	/**#@+
	* @access private
	* @var mixed
	*/
	var $_haveRefs;
	var $_allNodes;
	var $_allParent;
	var $_lastIndent;
	var $_lastNode;
	var $_inBlock;
	var $_isInline;
	var $_dumpIndent;
	var $_dumpWordWrap;
	/**#@+*/

	/**** Public Properties ****/

	/**#@+
	* @access public
	* @var mixed
	*/
	var $_nodeId;
	/**#@+*/

	/**** Private Methods ****/

	/**
	* Attempts to convert a key / value array item to YAML
	* @access private
	* @return string
	* @param $key The name of the key
	* @param $value The value of the item
	* @param $indent The indent of the current node
	*/
	function _yamlize($key,$value,$indent) {
	if (is_array($value)) {
		// It has children.  What to do?
		// Make it the right kind of item
		$string = $this->_dumpNode($key,NULL,$indent);
		// Add the indent
		$indent += $this->_dumpIndent;
		// Yamlize the array
		$string .= $this->_yamlizeArray($value,$indent);
	} elseif (!is_array($value)) {
		// It doesn't have children.  Yip.
		$string = $this->_dumpNode($key,$value,$indent);
	}
	return $string;
	}

	/**
	* Attempts to convert an array to YAML
	* @access private
	* @return string
	* @param $array The array you want to convert
	* @param $indent The indent of the current level
	*/
	function _yamlizeArray($array,$indent) {
	if (is_array($array)) {
		$string = '';
		foreach ($array as $key => $value) {
		$string .= $this->_yamlize($key,$value,$indent);
		}
		return $string;
	} else {
		return false;
	}
	}

	/**
	* Returns YAML from a key and a value
	* @access private
	* @return string
	* @param $key The name of the key
	* @param $value The value of the item
	* @param $indent The indent of the current node
	*/
	function _dumpNode($key,$value,$indent) {
	// do some folding here, for blocks
	if (strpos($value,"\n") !== false || strpos($value,": ") !== false || strpos($value,"- ") !== false) {
		$value = $this->_doLiteralBlock($value,$indent);
	} else {
		$value  = $this->_doFolding($value,$indent);
	}

	if (is_bool($value)) {
		$value = ($value) ? "true" : "false";
	}

	$spaces = str_repeat(' ',$indent);

	if (is_int($key)) {
		// It's a sequence
		$string = $spaces.'- '.$value."\n";
	} else {
		// It's mapped
		$string = $spaces.$key.': '.$value."\n";
	}
	return $string;
	}

	/**
	* Creates a literal block for dumping
	* @access private
	* @return string
	* @param $value
	* @param $indent int The value of the indent
	*/
	function _doLiteralBlock($value,$indent) {
	$exploded = explode("\n",$value);
	$newValue = '|';
	$indent  += $this->_dumpIndent;
	$spaces   = str_repeat(' ',$indent);
	foreach ($exploded as $line) {
		$newValue .= "\n" . $spaces . trim($line);
	}
	return $newValue;
	}

	/**
	* Folds a string of text, if necessary
	* @access private
	* @return string
	* @param $value The string you wish to fold
	*/
	function _doFolding($value,$indent) {
	// Don't do anything if wordwrap is set to 0
	if ($this->_dumpWordWrap === 0) {
		return $value;
	}

	if (strlen($value) > $this->_dumpWordWrap) {
		$indent += $this->_dumpIndent;
		$indent = str_repeat(' ',$indent);
		$wrapped = wordwrap($value,$this->_dumpWordWrap,"\n$indent");
		$value   = ">\n".$indent.$wrapped;
	}
	return $value;
	}

	/* Methods used in loading */

	/**
	* Finds and returns the indentation of a YAML line
	* @access private
	* @return int
	* @param string $line A line from the YAML file
	*/
	function _getIndent($line) {
	preg_match('/^\s{1,}/',$line,$match);
	if (!empty($match[0])) {
		$indent = substr_count($match[0],' ');
	} else {
		$indent = 0;
	}
	return $indent;
	}

	/**
	* Parses YAML code and returns an array for a node
	* @access private
	* @return array
	* @param string $line A line from the YAML file
	*/
	function _parseLine($line) {
	$line = trim($line);

	$array = array();

	if (preg_match('/^-(.*):$/',$line)) {
		// It's a mapped sequence
		$key         = trim(substr(substr($line,1),0,-1));
		$array[$key] = '';
	} elseif ($line[0] == '-' && substr($line,0,3) != '---') {
		// It's a list item but not a new stream
		if (strlen($line) > 1) {
		$value   = trim(substr($line,1));
		// Set the type of the value.  Int, string, etc
		$value   = $this->_toType($value);
		$array[] = $value;
		} else {
		$array[] = array();
		}
	} elseif (preg_match('/^(.+):/',$line,$key)) {
		// It's a key/value pair most likely
		// If the key is in double quotes pull it out
		if (preg_match('/^(["\'](.*)["\'](\s)*:)/',$line,$matches)) {
		$value = trim(str_replace($matches[1],'',$line));
		$key   = $matches[2];
		} else {
		// Do some guesswork as to the key and the value
		$explode = explode(':',$line);
		$key     = trim($explode[0]);
		array_shift($explode);
		$value   = trim(implode(':',$explode));
		}

		// Set the type of the value.  Int, string, etc
		$value = $this->_toType($value);
		if (empty($key)) {
		$array[]     = $value;
		} else {
		$array[$key] = $value;
		}
	}
	return $array;
	}

	/**
	* Finds the type of the passed value, returns the value as the new type.
	* @access private
	* @param string $value
	* @return mixed
	*/
	function _toType($value) {
	if (preg_match('/^("(.*)"|\'(.*)\')/',$value,$matches)) {
	$value = (string)preg_replace('/(\'\'|\\\\\')/',"'",end($matches));
	$value = preg_replace('/\\\\"/','"',$value);
	} elseif (preg_match('/^\\[(.+)\\]$/',$value,$matches)) {
		// Inline Sequence

		// Take out strings sequences and mappings
		$explode = $this->_inlineEscape($matches[1]);

		// Propogate value array
		$value  = array();
		foreach ($explode as $v) {
		$value[] = $this->_toType($v);
		}
	} elseif (strpos($value,': ')!==false && !preg_match('/^{(.+)/',$value)) {
		// It's a map
		$array = explode(': ',$value);
		$key   = trim($array[0]);
		array_shift($array);
		$value = trim(implode(': ',$array));
		$value = $this->_toType($value);
		$value = array($key => $value);
	} elseif (preg_match("/{(.+)}$/",$value,$matches)) {
		// Inline Mapping

		// Take out strings sequences and mappings
		$explode = $this->_inlineEscape($matches[1]);

		// Propogate value array
		$array = array();
		foreach ($explode as $v) {
		$array = $array + $this->_toType($v);
		}
		$value = $array;
	} elseif (strtolower($value) == 'null' or $value == '' or $value == '~') {
		$value = NULL;
	} elseif (preg_match ('/^[0-9]+$/', $value)) {
	// Cheeky change for compartibility with PHP < 4.2.0
		$value = (int)$value;
	} elseif (in_array(strtolower($value),
				array('true', 'on', '+', 'yes', 'y'))) {
		$value = true;
	} elseif (in_array(strtolower($value),
				array('false', 'off', '-', 'no', 'n'))) {
		$value = false;
	} elseif (is_numeric($value)) {
		$value = (float)$value;
	} else {
		// Just a normal string, right?
		$value = trim(preg_replace('/#(.+)$/','',$value));
	}

	return $value;
	}

	/**
	* Used in inlines to check for more inlines or quoted strings
	* @access private
	* @return array
	*/
	function _inlineEscape($inline) {
	// There's gotta be a cleaner way to do this...
	// While pure sequences seem to be nesting just fine,
	// pure mappings and mappings with sequences inside can't go very
	// deep.  This needs to be fixed.

	$saved_strings = array();

	// Check for strings
	$regex = '/(?:(")|(?:\'))((?(1)[^"]+|[^\']+))(?(1)"|\')/';
	if (preg_match_all($regex,$inline,$strings)) {
		$saved_strings = $strings[0];
		$inline  = preg_replace($regex,'YAMLString',$inline);
	}
	unset($regex);

	// Check for sequences
	if (preg_match_all('/\[(.+)\]/U',$inline,$seqs)) {
		$inline = preg_replace('/\[(.+)\]/U','YAMLSeq',$inline);
		$seqs   = $seqs[0];
	}

	// Check for mappings
	if (preg_match_all('/{(.+)}/U',$inline,$maps)) {
		$inline = preg_replace('/{(.+)}/U','YAMLMap',$inline);
		$maps   = $maps[0];
	}

	$explode = explode(', ',$inline);


	// Re-add the sequences
	if (!empty($seqs)) {
		$i = 0;
		foreach ($explode as $key => $value) {
		if (strpos($value,'YAMLSeq') !== false) {
			$explode[$key] = str_replace('YAMLSeq',$seqs[$i],$value);
			++$i;
		}
		}
	}

	// Re-add the mappings
	if (!empty($maps)) {
		$i = 0;
		foreach ($explode as $key => $value) {
		if (strpos($value,'YAMLMap') !== false) {
			$explode[$key] = str_replace('YAMLMap',$maps[$i],$value);
			++$i;
		}
		}
	}

	// Re-add the strings
	if (!empty($saved_strings)) {
		$i = 0;
		foreach ($explode as $key => $value) {
		while (strpos($value,'YAMLString') !== false) {
			$explode[$key] = preg_replace('/YAMLString/',$saved_strings[$i],$value, 1);
			++$i;
			$value = $explode[$key];
		}
		}
	}

	return $explode;
	}

	/**
	* Builds the PHP array from all the YAML nodes we've gathered
	* @access private
	* @return array
	*/
	function _buildArray() {
	$trunk = array();

	if (!isset($this->_indentSort[0])) {
		return $trunk;
	}

	foreach ($this->_indentSort[0] as $n) {
		if (empty($n->parent)) {
		$this->_nodeArrayizeData($n);
		// Check for references and copy the needed data to complete them.
		$this->_makeReferences($n);
		// Merge our data with the big array we're building
		$trunk = $this->_array_kmerge($trunk,$n->data);
		}
	}

	return $trunk;
	}

	/**
	* Traverses node-space and sets references (& and *) accordingly
	* @access private
	* @return bool
	*/
	function _linkReferences() {
	if (is_array($this->_haveRefs)) {
		foreach ($this->_haveRefs as $node) {
		if (!empty($node->data)) {
			$key = key($node->data);
			// If it's an array, don't check.
			if (is_array($node->data[$key])) {
			foreach ($node->data[$key] as $k => $v) {
				$this->_linkRef($node,$key,$k,$v);
			}
			} else {
			$this->_linkRef($node,$key);
			}
		}
		}
	}
	return true;
	}

	function _linkRef(&$n,$key,$k = NULL,$v = NULL) {
	if (empty($k) && empty($v)) {
		// Look for &refs
		if (preg_match('/^&([^ ]+)/',$n->data[$key],$matches)) {
		// Flag the node so we know it's a reference
		$this->_allNodes[$n->id]->ref = substr($matches[0],1);
		$this->_allNodes[$n->id]->data[$key] =
				substr($n->data[$key],strlen($matches[0])+1);
		// Look for *refs
		} elseif (preg_match('/^\*([^ ]+)/',$n->data[$key],$matches)) {
		$ref = substr($matches[0],1);
		// Flag the node as having a reference
		$this->_allNodes[$n->id]->refKey =  $ref;
		}
	} elseif (!empty($k) && !empty($v)) {
		if (preg_match('/^&([^ ]+)/',$v,$matches)) {
		// Flag the node so we know it's a reference
		$this->_allNodes[$n->id]->ref = substr($matches[0],1);
		$this->_allNodes[$n->id]->data[$key][$k] =
							substr($v,strlen($matches[0])+1);
		// Look for *refs
		} elseif (preg_match('/^\*([^ ]+)/',$v,$matches)) {
		$ref = substr($matches[0],1);
		// Flag the node as having a reference
		$this->_allNodes[$n->id]->refKey =  $ref;
		}
	}
	}

	/**
	* Finds the children of a node and aids in the building of the PHP array
	* @access private
	* @param int $nid The id of the node whose children we're gathering
	* @return array
	*/
	function _gatherChildren($nid) {
	$return = array();
	$node   =& $this->_allNodes[$nid];
	if (is_array ($this->_allParent[$node->id])) {
		foreach ($this->_allParent[$node->id] as $nodeZ) {
		$z =& $this->_allNodes[$nodeZ];
		// We found a child
		$this->_nodeArrayizeData($z);
		// Check for references
		$this->_makeReferences($z);
		// Merge with the big array we're returning
		// The big array being all the data of the children of our parent node
		$return = $this->_array_kmerge($return,$z->data);
		}
	}
	return $return;
	}

	/**
	* Turns a node's data and its children's data into a PHP array
	*
	* @access private
	* @param array $node The node which you want to arrayize
	* @return boolean
	*/
	function _nodeArrayizeData(&$node) {
	if (is_array($node->data) && $node->children == true) {
		// This node has children, so we need to find them
		$childs = $this->_gatherChildren($node->id);
		// We've gathered all our children's data and are ready to use it
		$key = key($node->data);
		$key = empty($key) ? 0 : $key;
		// If it's an array, add to it of course
		if (isset ($node->data[$key])) {
			if (is_array($node->data[$key])) {
			$node->data[$key] = $this->_array_kmerge($node->data[$key],$childs);
			} else {
			$node->data[$key] = $childs;
			}
		} else {
			$node->data[$key] = $childs;
		}
	} elseif (!is_array($node->data) && $node->children == true) {
		// Same as above, find the children of this node
		$childs       = $this->_gatherChildren($node->id);
		$node->data   = array();
		$node->data[] = $childs;
	}

	// We edited $node by reference, so just return true
	return true;
	}

	/**
	* Traverses node-space and copies references to / from this object.
	* @access private
	* @param object $z A node whose references we wish to make real
	* @return bool
	*/
	function _makeReferences(&$z) {
	// It is a reference
	if (isset($z->ref)) {
		$key                = key($z->data);
		// Copy the data to this object for easy retrieval later
		$this->ref[$z->ref] =& $z->data[$key];
	// It has a reference
	} elseif (isset($z->refKey)) {
		if (isset($this->ref[$z->refKey])) {
		$key           = key($z->data);
		// Copy the data from this object to make the node a real reference
		$z->data[$key] =& $this->ref[$z->refKey];
		}
	}
	return true;
	}


	/**
	* Merges arrays and maintains numeric keys.
	*
	* An ever-so-slightly modified version of the array_kmerge() function posted
	* to php.net by mail at nospam dot iaindooley dot com on 2004-04-08.
	*
	* http://us3.php.net/manual/en/function.array-merge.php#41394
	*
	* @access private
	* @param array $arr1
	* @param array $arr2
	* @return array
	*/
	function _array_kmerge($arr1,$arr2) {
	if(!is_array($arr1)) $arr1 = array();
	if(!is_array($arr2)) $arr2 = array();

	$keys  = array_merge(array_keys($arr1),array_keys($arr2));
	$vals  = array_merge(array_values($arr1),array_values($arr2));
	$ret   = array();
	foreach($keys as $key) {
		list($unused,$val) = each($vals);
		if (isset($ret[$key]) and is_int($key)) $ret[] = $val; else $ret[$key] = $val;
	}
	return $ret;
	}
}






