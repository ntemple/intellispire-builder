<?php
/**
 * @version    $Id: AuthnetARB.class.php 21 2013-03-15 19:35:01Z ntemple $
 * @package    MWG
 * @copyright  Copyright (C) 2010 Intellispire, LLC. All rights reserved.
 * @license    GNU/GPL v2.0, see LICENSE.txt
 *
 * Marketing Website Generator is free software. 
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */


/**
 * Class for processing recurring payments via ARB API Authorize.net 
 * Original code by John Conde: http://www.merchant-account-services.org/blog/
 * with a few changes by Alexis Bellido: http://www.ventanazul.com/webzine/articles/authorize-recurring-billing-php-class
 */
class AuthnetARB {
  var $login;
  var $transkey;
  var $test;
  var $params   = array();
  var $success   = false;
  var $error    = true;
  var $url;
  var $xml;
  var $response;
  var $resultCode;
  var $code;
  var $text;
  var $subscrId;
  function AuthnetARB($login, $transkey, $test) {
    $this->login = $login;
    $this->transkey = $transkey;
    $this->test = $test;
    $subdomain = ($this->test) ? 'api' : 'api';
    $this->url = "https://" . $subdomain . ".authorize.net/xml/v1/request.api";
  }
  function getString() {
    if (!$this->params) {
      return (string) $this;
    }
    $output  = "";
    $output .= '<table summary="Authnet Results" id="authnet">' . "\n";
    $output .= '<tr>' . "\n\t\t" . '<th colspan="2"><b>Outgoing Parameters</b></th>' . "\n" . '</tr>' . "\n";
    foreach ($this->params as $key => $value) {
      $output .= "\t" . '<tr>' . "\n\t\t" . '<td><b>' . $key . '</b></td>';
      $output .= '<td>' . $value . '</td>' . "\n" . '</tr>' . "\n";
    }
    $output .= '</table>' . "\n";
    return $output;
  }
  function process($retries = 3) {
    $count = 0;
    while ($count < $retries)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $this->response = curl_exec($ch);
        $this->parseResults();
        if ($this->resultCode === "Ok") {
          $this->success = true;
          $this->error   = false;
          break;
        } else {
          $this->success = false;
          $this->error   = true;
          break;
        }
        $count++;
    }
    curl_close($ch);
  }
  function createAccount() {
    $this->xml = "<?xml version='1.0' encoding='utf-8'?>
                  <ARBCreateSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
                      <merchantAuthentication>
                          <name>" . $this->login . "</name>
                          <transactionKey>" . $this->transkey . "</transactionKey>
                      </merchantAuthentication>
                      <refId>" . $this->params['refID'] ."</refId>
                      <subscription>
                          <name>". $this->params['subscrName'] ."</name>
                          <paymentSchedule>
                              <interval>
                                  <length>". $this->params['interval_length'] ."</length>
                                  <unit>". $this->params['interval_unit'] ."</unit>
                              </interval>
                              <startDate>" . $this->params['startDate'] . "</startDate>
                              <totalOccurrences>". $this->params['totalOccurrences'] . "</totalOccurrences>
                              <trialOccurrences>". $this->params['trialOccurrences'] . "</trialOccurrences>
                          </paymentSchedule>
                          <amount>". $this->params['amount'] ."</amount>
                          <trialAmount>" . $this->params['trialAmount'] . "</trialAmount>
                          <payment>
                              <creditCard>
                                  <cardNumber>" . $this->params['cardNumber'] . "</cardNumber>
                                  <expirationDate>" . $this->params['expirationDate'] . "</expirationDate>
                              </creditCard>
                          </payment>
                          <order>
                              <invoiceNumber>". $this->params['invoiceNumber'] . "</invoiceNumber>
                              <description>" ."<![CDATA[".  $this->params['description']. "]]>" . "</description>
                          </order>
                          <customer>
                              <email>". $this->params['email'] . "</email>".
                              "<faxNumber>" . $this->params['fax'] . "</faxNumber>
                          </customer>
                          <billTo>
                              <firstName>". $this->params['firstName'] . "</firstName>
                              <lastName>" . $this->params['lastName'] . "</lastName>
                              <company>". $this->params['company'] . "</company>
                              <address>" . $this->params['address'] . "</address>
                              <city>" . $this->params['city'] . "</city>
                              <state>" . $this->params['state'] . "</state>
                              <zip>" . $this->params['zip'] . "</zip>
                              <country>" . $this->params['country'] . "</country>
                          </billTo>
                          <shipTo>
                              <firstName>". $this->params['ship_first_name'] . "</firstName>
                              <lastName>" . $this->params['ship_last_name'] . "</lastName>
                              <company>". $this->params['ship_company'] . "</company>
							  <address>" . $this->params['ship_address'] . "</address>
                              <city>" . $this->params['ship_city'] . "</city>
                              <state>" . $this->params['ship_state'] . "</state>
                              <zip>" . $this->params['ship_zip'] . "</zip>
                              <country>" . $this->params['ship_country'] . "</country>
                          </shipTo>
                      </subscription>
                  </ARBCreateSubscriptionRequest>";
				 
    $this->process();
  }
  function updateAccount() {
    $this->xml = "<?xml version='1.0' encoding='utf-8'?>
                  <ARBUpdateSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
                      <merchantAuthentication>
                          <name>" . $this->login . "</name>
                          <transactionKey>" . $this->transkey . "</transactionKey>
                      </merchantAuthentication>
                      <refId>" . $this->params['refID'] ."</refId>
					  <subscriptionId>" . $this->params['subscrId'] . "</subscriptionId>
                     <subscription>
                          <payment>
                              <creditCard>
                                  <cardNumber>" . $this->params['cardNumber'] . "</cardNumber>
                                  <expirationDate>" . $this->params['expirationDate'] . "</expirationDate>
                              </creditCard>
                          </payment>
                          <customer>
                              <id>". $this->params['id'] . "</id>
                          </customer>
                      </subscription>
                  </ARBUpdateSubscriptionRequest>";
    $this->process();
  }
  function deleteAccount() {
    $this->xml = "<?xml version='1.0' encoding='utf-8'?>
                  <ARBCancelSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
                      <merchantAuthentication>
                          <name>" . $this->login . "</name>
                          <transactionKey>" . $this->transkey . "</transactionKey>
                      </merchantAuthentication>
                      <refId>" . $this->params['refID'] ."</refId>
                      <subscriptionId>" . $this->params['subscrId'] . "</subscriptionId>
                  </ARBCancelSubscriptionRequest>";
    $this->process();
  }
  function parseResults() {
    $this->resultCode = $this->substring_between($this->response,'<resultCode>','</resultCode>');
    $this->code = $this->substring_between($this->response,'<code>','</code>');
    $this->text = $this->substring_between($this->response,'<text>','</text>');
    $this->subscrId = $this->substring_between($this->response,'<subscriptionId>','</subscriptionId>');
  }
  function substring_between($haystack,$start,$end) {
     if (strpos($haystack,$start) === false || strpos($haystack,$end) === false) {
        return false;
     } else {
        $start_position = strpos($haystack,$start)+strlen($start);
        $end_position = strpos($haystack,$end);
        return substr($haystack,$start_position,$end_position-$start_position);
     }
  }
  function setParameter($field = "", $value = null) {
    $field = (is_string($field)) ? trim($field) : $field;
    $value = (is_string($value)) ? trim($value) : $value;
    if (!is_string($field)) {
      die("setParameter() arg 1 must be a string or integer: " . gettype($field) . " given.");
    }
    if (empty($field)) {
      die("setParameter() requires a parameter field to be named.");
    }
    $this->params[$field] = $value;
  }
  function isSuccessful() {
    return $this->success;
  }
  function isError() {
    return $this->error;
  }
  function getResponse() {
    return $this->text;
  }
  function getRawResponse() {
    return $this->response;
  }
  function getResultCode() {
    return $this->resultCode;
  }
  function getSubscriberID() {
    return $this->subscrId;
  }
}
?>
