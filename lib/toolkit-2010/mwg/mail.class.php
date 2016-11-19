<?php
/**
* @version    $Id: mail.class.php 21 2013-03-15 19:35:01Z ntemple $
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
* PHP mail() compatible routine
*
* Added Features:
* - stops many sources of SPAM
* - use php mail() or SMTP server, without or without authentication
*/

/**
* Send mail through PHPMailer
* 
* @param mixed $subject
* @param mixed $message
* @param array $to     (address, name)
* @param array $from   Optional, defaults to site settings (address, name)
* @param mixed $bcc    Optional. (address, name)
* @return bool
*/
function mwg_nmail($subject, $message, $to, $from = null,  $bcc = null) {
  
  $mail = mwg_mailer();
  $mail->Subject = $subject;
  $mail->Body = $message;
  $mail->AddAddress($to[0], $to[1]);
  if ($from) $mail->setFrom($from[0], $from[1]);
  if ($bcc) $mail->addBCC($bcc[0], $bcc[1]);    
  
  $return  = $mail->Send();  
}

/**
* Safe PHP Mail compatible routine that uses PHPMailer() for SPAM protection
* Just like the original @mail, errors are ignored.
* For full control @see mwg_nmail
* 
* @param mixed $to
* @param mixed $subject
* @param mixed $message
* @param mixed $additional_headers
* @param mixed $additional_paramaters
* @see mwg_nmail
*/

function mwg_mail($to, $subject, $message, $headers = null, $paramaters = null) {

  $to = mwg_mail_parse_address($to);
  $from = null;
  $bcc  = null;

  // headers must be an array
  if ($headers && !is_array($headers)) {
    $headers = explode("\r\n", $headers);
  }

  foreach ($headers as $header) {
    list($name, $value) = explode(':', $header);
    $name = strtolower(trim($name));                                                   
    $value = trim($value);
    switch ($name) {
      case 'from': $from = mwg_mail_parse_address($value); break;
      case 'bcc':  $bcc  = mwg_mail_parse_address($value); break;      
    }
  }

  try {    
    return mwg_nmail($subject, $message, $to, $from, $bcc);
  } catch (Exception $e) {
    // do nothing, this is a "safe" mail
    return false;
  }
}

function mwg_mail_parse_address($a) {
  list($name, $address) = explode('<', $a);
  if ($address) {
    $address = str_replace('>', '', $address); // strip the >
  } else {
    $address = $name; // we didn't have a name
    $name = '';
  }
  return array($address, $name);
}


function mwg_mailer() {
  static $mailer = null;

  // Only configure this once
  if (!$mailer)  {
    require_once('phpmailer/class.phpmailer.php'); 

    $mailer = new PHPMailer(true); // defaults to using php "mail()". Throw exceptions!

    $mailer->setFrom(
    get_setting("email_from_address"), 
    get_setting("email_from_name")
    );

    $mailer_type = get_setting('email_mailer', 'mail'); 
    if ($mailer_type == 'smtp') {
      $mailer->IsSMTP();
      $mailer->Host    = get_setting('email_smtp_host', 'localhost');     
      
      if (get_setting('email_smtp_connection', 'none') == 'ssl') $mailer->SMTPSecure = "ssl";            
          
      $mailer->Port       = get_setting('email_smtp_port', 25);
      $mailer->Username   = get_setting('email_smtp_username', '');
      $mailer->Password   = get_setting('email_smtp_password', '');

      if ($mailer->Password) {
        $mailer->SMTPAuth = true;
      }
    }
  }

  return clone($mailer);

}
