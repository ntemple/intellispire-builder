<?PHP
/**
* Amazon PHP Classes and Functions
*
* Copyright (c)2008 Intellispire 
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
* @category   Amazon
* @package    AWS
* @author     Nick Temple <Nick.Temple@intellispire.com>
* @copyright  2002-2008 Intellispire
* @license    MIT: http://www.opensource.org/licenses/mit-license.php
* @version    SVN: $Id: fps.class.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*
* Requires PHP 5+
*
* Code updated with adaptions from: http://code.google.com/p/php-aws/
*
*/

/**
* The Flexible Service.
*
*/


require_once('aws.class.php');

define('GK_CREDITCARD', 'credit card');
define('GK_ACH', 'ach');
define('GK_ABT', 'abt');
define('GK_PREPAID', 'prepaid');
define('GK_POSTPAID', 'debt');
define('GK_ROLE_CALLER',   'Caller');
define('GK_ROLE_RECIPIENT', 'Recipient');
define('GK_ROLE_SENDER',    'Sender');



class GK {
  private $_role;
  private $_data;

  function toString() {
     $out = '';
     $out = "MyRole =='" . $this->_role . "' orSay 'Role does not match.';\n";
     $out .= $data;
     return $data;
  }

  function __construct($role = GK_ROLE_CALLER) {
    $this->_role = $role;
  }

  
  function setData($data) {
    $this->_data = $data;
  }

}


class FPS extends AWS
{
    
//    var $endpoint     = "https://fps.sandbox.amazonaws.com/";
//    var $endpoint     = "https://fps.amazonaws.com/";

    var $_version    = '2007-01-08';
    
    function FPS($key, $secret, $url = 'https://fps.amazonaws.com')
    {
        $this->endpoint = $url;
        parent::__construct($key, $secret);
    }

    function GetAccountBalance() {
      $xml = $this->go('GetAccountBalance', array());
      return $xml['AccountBalance'];
    }   


    function InstallPaymentInstruction($PaymentInstruction, $TokenType = 'SingleUse', 
                                                            $TokenFriendlyName = NULL,
                                                            $PaymentReason = NULL,
                                                            $CallerReference = NULL) 
    {
      if (!isset('CallerReference')) $CallerReference = uuid();
      
      $params = array(
                      'PaymentInstruction' => $PaymentInstruction,
                      'TokenType'          => $TokenType,
                      'CallerReference'    => $CallerReference,
                     );

      if (isset($TokenFriendlyName)) $params['TokenFriendlyName'] = $TokenFriendlyName;      
      if (isset($PaymentReason)) $params['PaymentReason'] = $PaymentReason;

    }
                                               
 
    
    function go($action, $params)
    {
        
        // Add Actions
        $params['Action'] = $action;
        $params['Version'] = $this->_version;
        
        $xml =  $this->xmlrequest($params);
        $xml = $this-> simplexml2array($xml);
        $this->status = $xml['Status'];
        $this->requestId = $xml['RequestId'];
        return $xml;
    }
    
}



?>
