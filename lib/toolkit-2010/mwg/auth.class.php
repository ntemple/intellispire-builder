<?php
/**
 * @version    $Id: auth.class.php 21 2013-03-15 19:35:01Z ntemple $
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


  class Authorize{
    // required data
    var $Login;
    var $TransactionKey;
    var $Amount;
    var $CardNumber;
    var $ExpiredDate;
    var $relay_url;
    // user data
    var $FirstName;
    var $LastName;
    var $Company;
    var $Address;
    var $City;
    var $State;
    var $Zip;
    var $Country;
    var $Phone;
    var $Fax;
    var $UserId;
    var $Email;
    var $UserEmail = 'true';
	
	var $ShipFirstName;
	var $ShipLastName;
	var $ShipCompany;
	var $ShipAddress;
	var $ShipCity;
	var $ShipState;
	var $ShipZIP;
	var $ShipCountry;
	var $CustomerIp;
    // invoce data
    var $TransactionId;
    var $InvoiceId;
    var $Description;
    // protocol data
    var $Version = '3.1';
    
    //var $Type = 'AUTH_CAPTURE';
    var $DelimData = 'true';
    var $RelayResponse = 'false';
    var $TestMode = false;
    var $LastData;
    var $LastResponse;
    var $Error = '';
    var $ErrorNo = 0;
	var $resp_key = array(
		"Response Code",
		"Response Subcode",
		"Response Reason Code",
		"Response Reason Text",
		"Approval Code",
		"AVS Result Code",
		"Transaction ID",
		"Invoice Number (x_invoice_num)",
		"Description (x_description)",
		"Amount (x_amount)",
		"Method (x_method)",
		"Transaction Type (x_type)",
		"Customer ID (x_cust_id)",
		"Cardholder First Name (x_first_name)",
		"Cardholder Last Name (x_last_name)",
		"Company (x_company)",
		"Billing Address (x_address)",
		"City (x_city)",
		"State (x_state)",
		"ZIP (x_zip)",
		"Country (x_country)",
		"Phone (x_phone)",
		"Fax (x_fax)",
		"E-Mail Address (x_email)",
		"Ship to First Name (x_ship_to_first_name)",
		"Ship to Last Name (x_ship_to_last_name)",
		"Ship to Company (x_ship_to_company)",
		"Ship to Address (x_ship_to_address)",
		"Ship to City (x_ship_to_city)",
		"Ship to State (x_ship_to_state)",
		"Ship to ZIP (x_ship_to_zip)",
		"Ship to Country (x_ship_to_country)",
		"Tax Amount (x_tax)",
		"Duty Amount (x_duty)",
		"Freight Amount (x_freight)",
		"Tax Exempt Flag (x_tax_exempt)",
		"PO Number (x_po_num)",
		"MD5 Hash",
		"Card Code Response",
		"Reserved (40)",
		"Reserved (41)",
		"Reserved (42)",
		"Reserved (43)",
		"Reserved (44)",
		"Reserved (45)",
		"Reserved (46)",
		"Reserved (47)",
		"Reserved (48)",
		"Reserved (49)",
		"Reserved (50)",
		"Reserved (51)",
		"Reserved (52)",
		"Reserved (53)",
		"Reserved (54)",
		"Reserved (55)",
		"Reserved (56)",
		"Reserved (57)",
		"Reserved (58)",
		"Reserved (59)",
		"Reserved (60)",
		"Reserved (61)",
		"Reserved (62)",
		"Reserved (63)",
		"Reserved (64)",
		"Reserved (65)",
		"Reserved (66)",
		"Reserved (67)",
		"Reserved (68)",
);
  function ffilewrite2($str){
	$fp=fopen(PDC_LOG_DIR . "/auth.error.log.txt", "a+");
	fwrite($fp, date("Y-m-d H:i")."    ".$str."\n");
	fclose($fp);
	}
      function Pay(){
      $result = false;
      $this->Error = '';
      $this->ErrorNo = 0;
      $data = $this->PayData("AUTH_CAPTURE");
      $this->LastData = $data;
      $fp = fsockopen('ssl://'.($this->TestMode ? 'test.': 'secure.').'authorize.net', 443, $errno, $errstr, 30);
      if($fp){
      	
        $query_string = '';
        foreach($data as $key=>$value){
            if(!empty($query_string)) $query_string .= '&';
            $query_string .= $key.'='.urlencode($value);
        }
        $header = "POST /gateway/transact.dll HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".strlen($query_string)."\r\n\r\n";
        fputs ($fp, $header.$query_string);
        $response = '';
        while (!feof($fp)){
            $response = @fgets($fp);
        }
        fclose($fp);
        $response = explode(",", $response);
     	foreach($this->resp_key as $key=>$value){
			$resp_str.=$value." => ".$response[$key]." || ";	
		}
		$resp_str=substr($resp_str,0,strlen($resp_str)-4);
		$resp_str.="\n";
        $this->LastResponse = $response;
		
        switch($response[0]){
           case 1: // approved
           	 $result = true;
			 $this->TransactionId = $response[6];
             break;
           case 2: // declined
             $this->Error = $response[3];
             $this->ErrorNo = $response[2];
             break;
           default: // error
             $this->Error = $response[3];
             $this->ErrorNo = $response[2];
             break;
        }
      }
      return $result;
    }  
    
    function Refund(){
      $result = false;
      $this->Error = '';
      $this->ErrorNo = 0;
      $data = $this->PayData("CREDIT");
      $this->LastData = $data;
      $fp = fsockopen('ssl://'.($this->TestMode ? 'test.': '').'authorize.net', 443, $errno, $errstr, 30);
      if($fp){
      	
        $query_string = '';
        foreach($data as $key=>$value){
            if(!empty($query_string)) $query_string .= '&';
            $query_string .= $key.'='.urlencode($value);
        }
        $header = "POST /gateway/transact.dll HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".strlen($query_string)."\r\n\r\n";
        fputs ($fp, $header.$query_string);
        $response = '';
        while (!feof($fp)){
            $response = @fgets($fp);
        }
        fclose($fp);
       
        $response = explode(",", $response);
    	foreach($this->resp_key as $key=>$value){
			$resp_str.=$value." => ".$response[$key]." || ";	
		}
		$resp_str=substr($resp_str,0,strlen($resp_str)-4);
		$resp_str.="\n";
        $this->LastResponse = $response;
        switch($response[0]){
           case 1: // approved
           $result = true;
		   $this->TransactionId = $response[6];
           break;
           case 2: // declined
             $this->Error = $response[3];
             $this->ErrorNo = $response[2];
             break;
           default: // error
             $this->Error = $response[3];
             $this->ErrorNo = $response[2];
             break;
        }
      }
      return $result;
    }
    function PayData($type){
      $data = array();
      $data['x_version'] = $this->Version;
      $data['x_type'] = $type;
      $data['x_delim_data'] = $this->DelimData;
      $data['x_relay_response'] = $this->RelayResponse;
      $data['x_login'] = $this->Login;
      $data['x_tran_key'] = $this->TransactionKey;
      $data['x_amount'] = $this->Amount;
      $data['x_card_num'] = $this->CardNumber;
      $data['x_exp_date'] = $this->ExpiredDate;
      if($this->FirstName) $data['x_first_name'] = $this->FirstName;
      if($this->LastName) $data['x_last_name'] = $this->LastName;
      if($this->Company) $data['x_company'] = $this->Company;
      if($this->Address) $data['x_address'] = $this->Address;
      if($this->City) $data['x_city'] = $this->City;
      if($this->State) $data['x_state'] = $this->State;
      if($this->Zip) $data['x_zip'] = $this->Zip;
      if($this->Country) $data['x_country'] = $this->Country;
      if($this->Phone) $data['x_phone'] = $this->Phone;
      if($this->UserId) $data['x_cust_id'] = $this->UserId;
      if($this->Email) {
        $data['x_email'] = $this->Email;
        $data['x_email_customer'] = $this->UserEmail;
      }
      if($this->TransactionId) $data['x_trans_id'] = $this->TransactionId;
      if($this->InvoiceId) $data['x_invoice_num'] = $this->InvoiceId;
      if($this->Description) $data['x_description'] = $this->Description;
      if($this->CustomerIp) $data['x_customer_ip'] = $this->CustomerIp;
//shipping
      if($this->ShipFirstName) $data['x_ship_to_first_name'] = $this->ShipFirstName;
      if($this->ShipLastName) $data['x_ship_to_last_name'] = $this->ShipLastName;
      if($this->ShipCompany) $data['x_ship_to_company'] = $this->ShipCompany;
      if($this->ShipAddress) $data['x_ship_to_address'] = $this->ShipAddress;
      if($this->ShipCity) $data['x_ship_to_city'] = $this->ShipCity;
      if($this->ShipState) $data['x_ship_to_state'] = $this->ShipState;
      if($this->ShipZIP) $data['x_ship_to_zip'] = $this->ShipZIP;
      if($this->ShipCountry) $data['x_ship_to_country'] = $this->ShipCountry;
	  
      return $data;
    }
  }
?>
