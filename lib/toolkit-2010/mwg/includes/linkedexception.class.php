<?php
/* Author : Romain Boisnard */
/* Liscenced under the LGPL GNU Lesser General Public Liscence, report the actual liscence for details.

/* LinkedException */
// Java-like exception with a cause

class LinkedException extends Exception {
    private $cause;
   
    function __construct($_message = null, $_code = 0, Exception $_cause = null) {
        parent::__construct($_message, $_code);
        $this->cause = $_cause;
    }
   
    public function getCause() {
        return $this->cause;
    }
   
    public function getStackTrace() {
        if ($this->cause !== null) {
            $arr = array();
            $trace = $this->getTrace();
            array_push($arr, $trace[0]);
            unset($trace);
            if (get_class($this->cause) == "LinkedException") {
                foreach ($this->cause->getStackTrace() as $key => $trace) {
                    array_push($arr, $trace);
                }
            }
            else {
                foreach ($this->cause->getTrace() as $key => $trace) {
                    array_push($arr, $trace);
                }
            }
            return $arr;
        }
        else {
            return $this->getTrace();
        }
    }
   
    public function showStackTrace() {
        $htmldoc = "<p style=\"font-family: monospace; border: solid 1px #000000\"><span style=\"font-weight: bold; color: #000000;\">An exception was thrown :<br/></span>";
        $htmldoc.= "Exception code : $this->code<br/>";
        $htmldoc.= "Exception message : $this->message<br/>";
        $htmldoc.= "<span style=\"color: #0000FF;\">";
        $i = 0;
        foreach ($this->getStackTrace() as $key => $trace) {
            $htmldoc.= $this->showTrace($trace, $i);
            $i++;
        }
        $htmldoc.= "#$i {main}<br/>";
        unset($i);
        $htmldoc.= "</span></p>";
        return $htmldoc;
    }
   
    private function showTrace($_trace, $_i) {
        $htmldoc = "#$_i ";
        if (array_key_exists("file",$_trace)) {
            $htmldoc.= $_trace["file"];
        }
        if (array_key_exists("line",$_trace)) {
            $htmldoc.= "(".$_trace["line"]."): ";
        }
        if (array_key_exists("class",$_trace) && array_key_exists("type",$_trace)) {
            $htmldoc.= $_trace["class"].$_trace["type"];
        }
        if (array_key_exists("function",$_trace)) {
            $htmldoc.= $_trace["function"]."(";
            if (array_key_exists("args",$_trace)) {
                if (count($_trace["args"]) > 0) {
                    $args = $_trace["args"];
                    $type = gettype($args[0]);
                    $value = $args[0];
                    unset($args);
                    if ($type == "boolean") {
                        if ($value) {
                            $htmldoc.= "true";
                        }
                        else {
                            $htmldoc.= "false";
                        }
                    }
                    elseif ($type == "integer" || $type == "double") {
                        if (settype($value, "string")) {
                            if (strlen($value) <= 20) {
                                $htmldoc.= $value;
                            }
                            else {
                                $htmldoc.= substr($value,0,17)."...";
                            }
                        }
                        else {
                            if ($type == "integer" ) {
                                $htmldoc.= "? integer ?";
                            }
                            else {
                                $htmldoc.= "? double or float ?";
                            }
                        }
                    }
                    elseif ($type == "string") {
                        if (strlen($value) <= 18) {
                            $htmldoc.= "'$value'";
                        }
                        else {
                            $htmldoc.= "'".substr($value,0,15)."...'";
                        }
                    }
                    elseif ($type == "array") {
                        $htmldoc.= "Array";
                    }
                    elseif ($type == "object") {
                        $htmldoc.= "Object";
                    }
                    elseif ($type == "resource") {
                        $htmldoc.= "Resource";
                    }
                    elseif ($type == "NULL") {
                        $htmldoc.= "null";
                    }
                    elseif ($type == "unknown type") {
                        $htmldoc.= "? unknown type ?";
                    }
                    unset($type);
                    unset($value);
                }
                if (count($_trace["args"]) > 1) {
                    $htmldoc.= ",...";
                }
            }           
            $htmldoc.= ")<br/>";
        }
        return $htmldoc;
    }
}
