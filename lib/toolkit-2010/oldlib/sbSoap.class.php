<?php
/* SVN FILE: $Id: sbSoap.class.php 21 2013-03-15 19:35:01Z ntemple $*/
/**
 * Sabrayla SOAP clients based on nusoap
 *
 * Long description for file
 *
 * PHP versions 5


 * Copyright 2007, Nick Temple, Intellispire
 *                 1355 Bardstown Rd. #230
 *                 Louisville, KY 40204
 *                 http://www.intellispire.com
 *                 Nick.Temple@intellispire.com
 *
 * @copyright           Copyright 2006-2007, Nick Temple, Intellispire
 * @link                http://www.intellispire.com
 * @package
 * @subpackage
 * @since
 * @version             $Revision: 21 $
 * @modifiedby          $LastChangedBy: ntemple $
 * @lastmodified        $Date: 2013-03-15 15:35:01 -0400 (Fri, 15 Mar 2013) $
 * @license             Redistribution is strictly prohibited.
 */

require_once('include/nusoap.php');
require_once('lib/sbutil.class.php');

class sbSoapServer extends soap_server
{
  
  var $proxyclient; // pro  

  function getClientProxy($classname = 'soapProxy', $wsdl = "") {
     $out  = "class $classname \n{\n";
     $out .= "
  protected \$soap; // Soap client

  function __construct(\$wsdl = \"$wsdl\")
  {
     \$this->soap = new sbSoapClient(\$wsdl);
  }\n
";
     $out .= $this->proxyclient;
     $out .= "\n}\n";
     return $out;
  }

  function register_me($funcname, $rettype = 'xsd:int')
  {
    $ns = & $this->wsdl->schemaTargetNamespace;

    $func = new ReflectionFunction($funcname);
    $docs = $func->getDocComment();

    $fparams = $func->getParameters();
    $params = array();
    foreach ($fparams as $param) {
      $params[$param->name] = 'xsd:string';
    }
    $return = array('return' => $rettype);


    $this->register($funcname,$params,$return,$ns, false, false, false, $docs);
    $this->proxyclient .= $this->build_proxy($funcname);
  }

  function build_proxy($funcname)
  {
    $out = "  function $funcname(";

    $func = new ReflectionFunction($funcname);
    $docs = $func->getDocComment();

    $fparams = $func->getParameters();
    $params = '';
    foreach ($fparams as $param) {
      $name = $param->name;
      $out .= "\$$name,";
      $params .= "    \$parameters['$name'] = \$$name;\n";
    }
    $c = sbutil::pchop($out);
    if ($c != ',') $out .= $c; // if we didn't get a comma, then put it back

    $out .= ")\n  {";
    $out .= "
    \$soap = \$this->soap;
    \$parameters = array();

$params
    \$result =  \$soap->safe_call('$funcname', \$parameters);
    return \$result;

  }\n
";

    return "\n$docs\n$out";

  }


  public function safe_call($method, $parameters) {
 
    $result = $soap->call($method, $parameters);
    if($error = $soap->getError()) { 
      sbutil::debug($error); // should we print a stack trace?
      throw new Exception($error);
    }
    return $result;
 }

}


class sbSoapClient extends nusoap_client {


}


