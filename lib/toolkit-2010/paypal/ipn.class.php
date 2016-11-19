<?php
require_once('/usr/local/lib/php/core/sabrayla.php');

class IPN {

     var $plugin;
     var $ipn;
     var $status = '';
     var $msg    = '';
     var $log;

     function process(&$ipn) {
        // Log
        $raw_log = fopen(PDC_LOG_DIR . '/all_ipn.txt', "a+");
        $this->raw = serialize($ipn);
        fwrite($raw_log, $this->raw);
        fwrite($raw_log, "\n=\n");
        fclose($raw_log);

        $this->log = fopen(PDC_LOG_DIR . '/rawlog.txt', "a+");
        fwrite($this->log, "== New Transaction ==\n");

        // Sanity check
        if (count($ipn) < 3) {  return $this->error('Not IPN data'); }


        /* Determine source of IPN */
        $plugins[] = new ipn_plugin_paypal();
        $plugins[] = new ipn_plugin_stormpay();

        foreach ($plugins as $p) {
           if ($p->isOurs($ipn)) { $this->plugin = $p;  } 
        }
              
        if ($this->plugin == '') {
          return $this->error('cannot find appropriate plugin for IPN notification');
        }

        // Call plugin to determine if IPN is valid
        if (!$this->plugin->validate($ipn)) {
           return $this->error('invalid IPN notification - could not verify with gateway (possible fraud)');
        }

        // normalize and store
        $this->ipn = $this->plugin->normalize($ipn);

        if ($this->ipn['txn_id'] == '') {
           $this->ipn['txn_id']  = md5(time() . $this->raw ); // Create a transaction ID
           $this->writelog(100, 'WARNING: No Tranasction ID');
        }


        if (!$this->store()) {
           // returns false if this is a duplicate transaction
           return $this->error('duplicate transaction');
        }


if (PDC_TESTING) {
        fwrite($this->log, sabrayla_sprint_r($ipn));
        $temp = $this->ipn;
        unset($temp['raw']);
        fwrite($this->log, "** Normalized Transaction **\n");
        fwrite($this->log, sabrayla_sprint_r($temp));
}

        // route based on txn_type and payment_status

        $this->router();
        
        if ($this->status == 'ERR') return false;

        fwrite($this->log, "== End Transaction - COMPLETE ==\n");
        return true;
     }


     function router() {
        $txn_type = $this->ipn['txn_type'];
        $this->writelog(10, "ROUTER: " . $this->ipn['txn_type']);
        switch ($txn_type) {
          case 'subscr_signup' : return $this->subscription_signup(); break;
          case 'subscr_cancel' : return $this->subscription_cancel(); break;
          case 'subscr_modify' : return $this->subscription_modify(); break;
          case 'subscr_failed' : return $this->subscription_failed(); break;
          case 'subscr_eot'    : return $this->subscription_eot(); break;
#         case 'subscr_payment': return $this->web_accept();                 // $this->subscription_payment(); break;
#         case 'web_accept'    : $this->web_accept(); break;
#         case 'cart'          : $this->cart(); break;
          default              : return $this->web_accept();
        }
     }
/*
     function web_accept() {
        $status = $this->ipn['payment_status'];
        switch($status) {
           case 'Completed'        : return $this->completed();         break; 
           case 'Refunded'         : return $this->refunded();          break;
           case 'Canceled-Reversal': return $this->canceled_reversal(); break;
           case 'Denied'           : return $this->denied();            break;
           case 'Failed'           : return $this->failed();            break; 
           case 'Pending'          : return $this->pending();           break;
           case 'Reversed'         : return $this->reversed();          break;
           case 'Processed'        : return $this->processed();         break;
           default                 : return $this->error('no router for web_accept (default) ' . $status);         
        }

     }
*/

    // Simplification of the default methods: Completed (transaction OK, refunded (do a refund), failed (do nothing).
    function web_accept() {
        $status = $this->ipn['payment_status'];
        $this->writelog(10, "Web-Accept: $status");
        switch($status) {
           case 'Completed'        : return $this->completed();  break;
           case 'Pending'          : return $this->completed();  break;
           case 'Refunded'         : return $this->refunded();   break;
#          case 'Canceled-Reversal': return $this->failed();     break;
#          case 'Denied'           : return $this->failed();     break;
#          case 'Failed'           : return $this->failed();     break;
           case 'Reversed'         : return $this->refunded();   break;
#          case 'Processed'        : return $this->completed();  break;
           default                 : return $this->error('no router for web_accept (default) ' . $status);
        }

     }

     function store() {
         $this->writelog(10, '+store()');
         $query = sql_prepare('select count(*) as c from ' . IPN_TABLE . ' where txn_id=?', $this->ipn['txn_id']);
         $this->writelog(100, "CHECKING: $query");
         $count = sql_get_value($query);
         if ($count != 0) { 
             return $this->error('Duplicate IPN Received: ' . $this->ipn['txn_id']);
         }                      
         $this->ipn_id = sabrayla_store(IPN_TABLE, $this->ipn);

         // Lookup Parent Transaction, if available
         if ($this->ipn['parent_txn_id']) {
           $this->writelog(100, "Looking up parent: " . $this->ipn['parent_txn_id']);
           $this->parent_ipn = sql_get_row('select * from ' . IPN_TABLE . ' where txn_id=?', $this->ipn['parent_txn_id']);
         }
         $this->writelog(10, '-store()');
         
         return true;
     }

     // These functions should be overridden 
     function subscription_signup()    { $this->writelog(10, '*subscription_signup');    }
     function subscription_cancel()    { $this->writelog(10, '*subscription_cancel');    }
     function subscription_modify()    { $this->writelog(10, '*subscription_modify');    }
     function subscription_failed()    { $this->writelog(10, '*subscription_failed');    }
     function subscription_eot()       { $this->writelog(10, '*subcription_eot');        }
     function subscription_payment()   { $this->writelog(10, '*subscription_payment');   }

     function cart()                   { $this->writelog(10, '*cart [Not Used]');        }
     function send_money()             { $this->writelog(10, '*send_money [Not Used]');  }
     function masspay()                { $this->writelog(10, '*masspay [Not Used]');     }
     function new_case()               { $this->writelog(10, '*new_case [Not Used]');    }

     function completed()              { $this->writelog(10, '*completed');              }
     function refunded()               { $this->writelog(10, '*refunded');               }
     function canceled_reversal()      { $this->writelog(10, '*canceled_reversal');      }
     function denied()                 { $this->writelog(10, '*denied');                 }
     function failed()                 { $this->writelog(10, '*failed');                 } 
     function pending()                { $this->writelog(10, '*pending');                }
     function reversed()               { $this->writelog(10, '*reversed');               }
     function processed()              { $this->writelog(10, '*processed');              }



     function error($msg) {
       $this->status = 'ERR';
       $msg = "[" . $this->ipn_id . "|" . $this->ipn['txn_id'] . "] " . $msg;
       $this->msg = $msg;
       fwrite($this->log, "$msg\n");
       fwrite($this->log, "=\n$raw\n=\n");
       fwrite($this->log, "== End Transaction ==\n");
       return false;
     }
    
     function writelog($level, $msg = "") {
       if ($msg == "") $msg = $level;
       fwrite($this->log, "[" . date("Y-m-d H:i"). "] " . $msg . "\n");
     }
}

class ipn_plugin {

    function isOurs(&$ipn) { return false; }


    /**
    * normalize PayPal IPN
    *
    * PayPal is the "standard", so there is no conversion here
    * we just need to get the fields we understand from the database
    */
    function _get_map() {
        $map = array();

        $fields = sql_get_results('show columns from ' . IPN_TABLE);
        foreach ($fields as $field) {
          $field = $field['Field'];
          $map[$field] = $field;
        }
        return $map;
    } 


    function normalize(&$ipn) {
      $map = $this->_get_map();
      $data = array();

      foreach ($map as $name => $value) {
        if(isset($ipn[$value])) $data[$name] = $ipn[$value];
      }
      $data['raw']    = serialize($ipn);
      $data['pdc_source'] = $this->plugin_name;

      # CUSTOM FOR PDC #

      $custom = $data['custom']; // after initial normalization
      $a=explode("|", $custom);
      if (count($a) > 1) {
        $data['pdc_affid']      = $a[0]; // aff_id
        $data['pdc_productid']  = $a[1]; // product_id
        $data['pdc_campid']     = $a[2]; // $camp_id 
        $data['pdc_tool']       = $a[3]; // $tool
        $data['pdc_session']    = $a[4]; // $session
      } else {
        $data['pdc_productid']  = $custom;
      }
     
      return $data;
    }
       

}


class ipn_plugin_paypal extends ipn_plugin {
  var $plugin_name = 'paypal';
 
  function isOurs(&$ipn) {
    // simplistic approach, should probably verify IP or something
    if ($ipn['txn_id'] != '') return true;
    if ($ipn['txn_type'] != '') return true; // we don't always get a transaction type
    return false;
  }

/*
  NOT NEEDED
  # HMMM, is this right???
  function normalize(&$ipn) {
     $data = parent::normalize($ipn);
#     if ($data['payment_gross'] != 0) {
#         $data['mc_gross'] = $data['payment_gross']; // Handle subscription payment 
#     }

#     if ( ($data['subscr_id'] != '') && $data['txn_id'] == '') {
#         $data['txn_id'] = $data['subscr_id'];
#     }
     return $data;
  }
*/

# NLT FIXME
# For some reason, this doesn't seem to be working. I'd prefer to use CURL, but .....
  function validate2($data2) {
    ipn_log(10, "+validate()"); 

    $data = $_POST; // Use the raw POST data

    $req = 'cmd=_notify-validate';
    foreach ($_POST as $key => $value) {
      $value = urlencode(stripslashes($value));
      $req .= "&$key=$value";
    }
    $postdata = $req;

ffilewrite($req);

    $ch=curl_init();

    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_URL, PDC_PP_URL);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);

    //Start ob to prevent curl_exec from displaying stuff.
    ob_start();
    curl_exec($ch);
    $result=ob_get_contents();
    ob_end_clean();
    curl_close($ch);

    ipn_log(10, "-validate(): $result"); 
ffilewrite($result);
 
    if(eregi("VERIFIED",$result)) return true;
    return false;
 } /* validate */ 


 function validate($data) {
    $req = 'cmd=_notify-validate';

    foreach ($_POST as $key => $value) {
      $value = urlencode(stripslashes($value));
      $req .= "&$key=$value";
    }
    ffilewrite($req);

    $good = false;

    // post back to PayPal system to validate
    $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
    $fp = fsockopen (PDC_PP_ADDRESS, 80, $errno, $errstr, 30);

    if (!$fp) {
      ffilewrite("***!!! GOT HTTP ERROR - IPN SCRIPT END: $errno $errstr");
      return false; 
    } 
    ffilewrite("Got response from paypal");
    fputs ($fp, $header . $req);
    while (!feof($fp)) {
       $res = fgets ($fp, 1024);
       if (strcmp ($res, "VERIFIED") == 0) {
            $good = true;
       } 
    }
    fclose($fp);
    ffilewrite("IPN IS: $good ($errno, $errstr)");
    return $good;
  }
  
} // End Class

class ipn_plugin_stormpay extends ipn_plugin {

   var $plugin_name = 'stormpay';

   function isOurs(&$ipn) {
      // simplistic approach
      if ($ipn['transaction_id'] != '') return true;
      return false;
   }

    function validate(&$ipn) {
       ipn_log(10, "+validate");
       // TODO: verify that this actually came from StormPay
       header("HTTP/1.1 202 Accepted");
       ipn_log(10, "-validate"); 
       return true;
    }


   function _get_map() {
     # TODO: I'm not sure how to get the parent_txn_id from StormPay
     # TODO: we need the shipping variables, too
     $map = array(
       'payment_status' => 'status',
       'payer_status'   => 'payer_status',
       'txn_id'         => 'transaction_id',
       'parent_txn_id'  => 'transaction_ref',  // This one may be confusing, it could be one of many values
       'payment_date'   => 'transaction_date',
       'verify_sign'    => 'secret_code',
       'txn_type'       => 'transaction_type',
       'payment_gross'  => 'amount',
       'payment_fee'    => 'transaction_fee',
       'item_name'      => 'product_name',
       'receiver_id'    => 'payee_id',
       'payer_id'       => 'payer_id',
       'receiver_email' => 'payee_email',
       'payer_email'    => 'payer_email',
       'first_name'     => 'payer_name',
#       'business'       => 'user_id',
       'custom'         => 'user2',  # is this correct? or are there more fields?
       'item_name'      => 'product_name',
       'item_number'    => 'user1',
       'mc_gross'       => 'amount',
       'tax'            => 'tax',
       'address_street' => 'shipping_address1',
       'address_city'   => 'shipping_city',
       'address_state'  => 'shipping_state',
       'address_zip'    => 'shipping_zip',
       'address_country'=> 'address_country',
       'quantity'       => 'quantity'
     );

 
     return $map;
   }

   function normalize(&$ipn) {
     $data = parent::normalize($ipn);
    
     // Extract first and last names 
     $names = explode(' ', $ipn->payer_name);
     if (count($names) > 1) {
       $data['first_name'] = array_unshift($names);
       $data['last_name']  = array_pop($names);
     }
    
     // Assume $USD
     $data['mc_currency'] = 'USD';

     // For non-subscription payments
     $data['txn_type'] = 'web_accept';
     $data['payment_type'] = 'instant'; //??
        

     switch ($ipn['status']) {
        case 'SUCCESS'   : $data['payment_status'] = 'Completed'; break;
        case 'REFUND'    : 
        case 'CANCEL'    : 
        case 'CHARGEBACK': $data['reason_code'] = 'refund'; 
                           $data['payment_status'] = 'Refunded';
                           break;
        case 'TEST'      :  // StormPay TEST's are basically worthless, as they don't have a transaction id
                            // (so we probably won't get here, anyway)
        default          : $data['payment_status'] = 'Failed'; break;
     }          


     return $data;
   }
  

}


function ipn_log($level, $data) {
  ffilewrite($data);
}

?>
