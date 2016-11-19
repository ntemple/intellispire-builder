<?php

require_once('ipn.class.php');

// NOTE: If this changes, the refund logic needs to change to lookup the fees rather than calculate them
define('PDC_EXTRA', 0.25);

class PDC_IPN extends IPN {

  function calc_fee($payment_amount) {
      $fee = 0;
      if ($payment_amount>1 && $payment_amount<=10) $fee=1;
      if ($payment_amount>10.01 && $payment_amount<=20) $fee=2;
      if ($payment_amount>20 ) $fee=3;
      return $fee;
  }
 
  // Completed Transaction
  function completed() {
    $this->writelog(10, '+completed()');

    switch($this->ipn['item_number']) {
       case 'PDC-PREMIER':
                $this->writelog(10, '+pdc_premier');
                $member_id = $this->ipn['custom'];
                sql_execute('update members set paid=1 where id=?', $member_id);
                $data['members_id']  = 0; // We don't add this to a members account, instead we add it to Mikes
                $data['ipn_id']      = $this->ipn_id;
                $data['mc_gross']      = $this->ipn['mc_gross'];
                $data['mc_currency'] = $this->ipn['mc_currency'];
                $data['notes']       = 'PDC Premier - ' . $member_id;
                sabrayla_store('purchase_pdc', $data);
                $this->writelog(10, '-pdc_premier');
                break;
       case 'PDC-SCO':
                $this->writelog(10, '+PDC-SCO');
                $member_id = $this->ipn['custom'];


                /* ORIGINAL IMPLEMENTATION */
                $custom = $member_id;
                $ipn_id = $this->ipn_id;

                ffilewrite("LC: PDC - SCO - $query");
                sql_execute("update members set credit=credit+125 where id='$custom'");
                $query="insert into transactions (id, seller_id, aff_id, amount, date_created, tr_type, ipn_id)
                                           values (NULL, '$custom', 0, 125, NOW(), 'credit', $ipn_id)";
                sql_execute($query);


                /* ORIGINAL IMPLEMENTATION */

                $data['ipn_id']      = $this->ipn_id;
                $data['members_id']  = $member_id;     
                $data['mc_gross']      = $this->ipn['mc_gross'];
                $data['mc_currency'] = $this->ipn['mc_currency'];
                $data['notes']       = 'PDC SCO - ' . $member_id;
                sabrayla_store('purchase_pdc', $data);

                // Add Extra Credits
                $data['mc_gross']      = $this->ipn['mc_gross'] * PDC_EXTRA;
                $data['mc_currency'] = 'PDC'; // We use "PDC" as the currency
                $data['notes']       = 'PDC SCO (Extra)- ' . $member_id;
                sabrayla_store('purchase_pdc', $data);
                $this->writelog(10, '-PDC-SCO');
                break;
       default:  // This is a standard sale
                $this->writelog(10, "+New Sale");

               /* We create the following records:
                  == the actual sale, which is a summary
                  == a record for the fee in the users account, under purchase_pdc
                  == a record for the actual amount under purchase_ledger
                  == one record for each affiliate sale
               */

               // shortcuts
               $session = $this->ipn['pdc_session'];
               $aff_id = $this->ipn['pdc_affid'];
               $product_id = $this->ipn['pdc_productid'];

               if ($session == "") return $this->error("BOGUS - no session");
               if ($this->ipn['pdc_productid'] != $this->ipn['item_number']) return $this->error('BOGUS: product mismatch (1)');
 

               $session_data = sql_get_row('select * from purchase_session where session=?', $session);
               fwrite($this->log, "+session $session.\n");
               fwrite($this->log, sabrayla_sprint_r($session_data)); 
               fwrite($this->log, "-session $session.\n");

               if ($this->ipn['mc_gross'] + 0  != $session_data['amount'] +0 )  
                            return $this->error('BOGUS: amounts do not match ' .
                                   $this->ipn['mc_gross'] . " " . $session_data['amount']);

               if ($this->ipn['item_number'] != $session_data['product_id'] ) return $this->error('BOGUS: product id mismatch (2)');

/****************** 

Loosen up criteria

               $field = $this->ipn['pdc_source'] . "_email";
               
               $query = sql_prepare("select $field from products where id=?", $this->ipn['item_number']);
               $this->writelog(10, $query);

               $email = sql_get_value($query);

// TODO: This must be a case insensitive comparison
// TODO: we sometimes don't seem to get the email addy's we sent

               if ($this->ipn['receiver_email'] != $email) 
                               return $this->error("BOGUS: payee mismatch [$email] [". $this->ipn['receiver_email'] . "]");
               // from original code? Why is an echeck no good? 
               // since the transaction is marked pending, I don't think we get here, anyway 
               
               // All echecks
               // if ($this->ipn['pending_reason'] == 'echeck') return $this->error('LC: This is an uncleared echeck... EXITING...');
***********/

               // If we get to here, everything is good! 
               $this->writelog(10, "Good Transaction: Processing");

               $sale = array();

               // Get seller and affiliate id's
               $seller_id  = sql_get_value('select member_id from products where products.id=?', $product_id+0);
               if ($aff_id != "") {
                  $aff_id = sql_get_value('select id from members where affname=?', $aff_id);
                  $sale['aff_id'] = $aff_id;
               } else {
                  $aff_id = 0;
               }
               
               $fee = $this->calc_fee($this->ipn['mc_gross']);
               $net = $this->ipn['mc_gross'] - $fee;
               $pdc_profit = $net; 
                
               $sale['session_id']       = $session_data['id'];
               $sale['seller_id']        = $seller_id;
               $sale['txn_id']           = $this->ipn['txn_id'];
               $sale['mc_gross']         = $this->ipn['mc_gross'];
               $sale['mc_currency']      = $this->ipn['mc_currency'];
               $sale['pdc_fee']          = $fee;
               $sale['other_fee']        = $this->ipn['mc_fee'];
               $sale['sale_status']      = 'ok';
               $sale['product_id']       = $this->ipn['item_number'];
               $sale['first_name']       = $this->ipn['first_name'];
               $sale['last_name']        = $this->ipn['last_name'];
               $sale['payer_email']      = $this->ipn['payer_email'];   
               $sale['address_street']   = $this->ipn['address_street']; 
               $sale['address_city']     = $this->ipn['address_city'];   
               $sale['address_state']    = $this->ipn['address_state'];   
               $sale['address_zip']      = $this->ipn['address_zip'];     
               $sale['address_country']  = $this->ipn['address_country'];
               $sale['ipn_id']           = $this->ipn_id;

               $sales_id = sabrayla_store('purchase_sales', $sale);

               
               /** START LEGACY **/
               $trans = array();
               $trans['seller_id']        = $seller_id;
               $trans['aff_id']           = 0;
               $trans['amount']           = $this->ipn['mc_gross'];
               $trans['tr_status']        = 'ok';
#              $trans['date_created']     = 'NOW()';
#              $trans['time_created']     = 'NOW()';
               $trans['product_id']       = $this->ipn['item_number'];
               $trans['first_name']       = $this->ipn['first_name'];
               $trans['last_name']        = $this->ipn['last_name'];
               $trans['email']            = $this->ipn['payer_email'];
               $trans['address']          = $this->ipn['address_street'];
               $trans['city']             = $this->ipn['address_city'];
               $trans['state']            = $this->ipn['address_state'];
               $trans['zip']              = $this->ipn['address_zip'];
               $trans['country']          = $this->ipn['address_country'];
               $trans['payer_status']     = $this->ipn['payer_status'];
               $trans['item_name']        = $this->ipn['item_name'];
               $trans['item_number']      = $this->ipn['item_number'];
               $trans['pay_proc']         = $this->ipn['pdc_source'];
               $trans['tr_type']          = 'sale';
               $trans['ipn_id']           = $this->ipn_id;
               $trans['level1_aff_id']    = $aff_id;

               $trans_id = sabrayla_store('transactions', $trans);
               sql_execute('update transactions set date_created=NOW(), time_created=NOW() where id=?', $trans_id);          
 
               // Transaction SALE 
              
               /* UNTESTED NEW CODE */ 
               # Note: using uneeded $sale variable overwrites the $sale array. above
               # Causing complete failure in updating the product counts, sending the ipn,  etc below.
               # Including the entire pdc ipn!
/*
               # Determine who made the sale
               if ($aff_id ==0) { 
                  $main_seller = $seller_id;
               } else {
                  $main_seller = $aff_id;
               } 
	     
               # Tripple check, what is this doing?  How is it determining which hit to turn into a sale? Does it matter?
               # Here it's always 
               # DOC: camp.sale: 0 = hit, 1 = sale, 2 = refund
               #            this is function completed(), so it's always a sale.  refunds are handled in function refunded()
                
               # Known bug: if the sales comes through the day after the original hit, it won't be found.  Why is 
               # hit_date important, if we are grabbing a random hit to convert into a sale, anyway?

               sql_execute("update camp set sale=1 where member_id=? and product_id=?  and sale=0 and hit_date='NOW()' limit 1", 
                               $main_seller,$trans['product_id']);
*/
              
/* ORIGINAL
Thinking through the refund portion of this - addition to problems mentioned above.
first, it needs to be in refunded(), not here.
Second, we'd want to change a sale (sale=1) into a refund (sale=2), NOT change a "hit" (sale=0) into a refund, as this code does.
Otherwise, out stats are completely screwed up.

Finally, the chance that the refund is coming in on the same day as the original sale is slim to none, so using the NOW() in the process makes no sense - in the nominal case, no transactions will ever be found to reverse. 


                           if ($aff_id==0)
                           {
                                   if ($trans['amount']<0) $sale=2; else $sale=1;
                                   sql_execute("update camp set sale='?' where member_id='?' and product_id='?'  and sale=0 and hit_date='NOW()' limit 1", $sale ,$trans['seller_id'],$trans['product_id']);
                                }
                                else
                                {
                                   if ($trans['amount']<0) $sale=2; else $sale=1;
                                   sql_execute("update camp set sale='?' where member_id='?' and product_id='?'  and sale=0 and hit_date='NOW()' limit 1", $sale ,$aff_id,$trans['product_id']);
                                }


*/

 
               /* END UNTESTED NEW CODE */



               // Transaction FEE
               $trans['tr_type']          = 'aff'; // ???? same as legacy, but???
               $trans['amount']           = $fee;
               $trans['item_name']        = "Vendor Fee for Product " . $this->ipn['item_name'];
               $trans_id = sabrayla_store('transactions', $trans);
               
               sql_execute('update transactions set date_created=NOW(), time_created=NOW() where id=?', $trans_id);


               // Prepare for affiliates
               $trans['tr_type']          = 'aff';
               $trans['aff_id']           = $aff_id; 
               $trans['item_name']        = $this->ipn['item_name'];   
               
               /** END LEGACY **/

               // Create a default transaction
               $default = array(); 
               $default['members_id']      = $seller_id;
               $default['sales_id']        = $sales_id;
               $default['ipn_id']          = $this->ipn_id;
               $default['mc_currency']     = $this->ipn['mc_currency'];

               // Charge the fee to the users account
               $purchase_fee = $default;   // PHP should make a copy
               $purchase_fee['mc_gross']      = - $fee;      // a debit
               $purchase_fee['notes']         = "PDC Fee";
               sabrayla_store('purchase_pdc', $purchase_fee);

               // Credit the merchant in the ledger (complete amount, or amount - fee?)
               $purchase_ledger = $default;
               $purchase_ledger['mc_gross'] =  $this->ipn['mc_gross']; // - $fee;
               
               sabrayla_store('purchase_ledger', $purchase_ledger);

               // Now, for the affiliates
               // This logic is incorrect: it is using the master "tree" rather than a products tree
               // but there is nothing I can do about it right now.
               
               $query="select level, value, cr_type from percent where product_id='$product_id' order by level";
               $levels = sql_get_results($query);

               // IO - I want to know if there were any affiliate transactions or not - for emailing
               // $aff_flag=0; # not needed, if no affiliate, then aff_if is 0

               foreach ($levels as $level) {
                      // IO - Mike request for flat amount
                      if ($level['cr_type']==1)
                        $amount = $level['value'];
                      else
                        $amount = $level['value'] / 100 * $net;

                      # Sanity checks, are there more
                      if ($aff_id == 0) break;                 # Don't credit PDC (we are at the top)
                      if ($amount < 0.01) break;               # should there be a higher minimum affiliate comission?
                      if ($pdc_profit - $amount < 0.0) break;  # Don't let profit go negative

                      // IO - If I got here means there were affiliates so I am mod flag
                      // Note: moved down, after check for aff_id = 0, otherwise flag would always be "true"
                      $aff_flag=1;

                      # Store affiliate information so we can use it in IPN, later
                      $level['mc_gross']  = $amount;
                      $level['aff_id']    = $aff_id;

                      # Create ledger transaction
                      $purchase_ledger = $default;
                      $purchase_ledger['members_id'] = $aff_id;
                      $purchase_ledger['mc_gross']   = $amount;
                      $purchase_ledger['tr_type']    = 'affiliate';
                 
                      sabrayla_store('purchase_ledger', $purchase_ledger);
                      $pdc_profit = $pdc_profit - $amount;

                      /** LEGACY **/
                      $trans['amount'] = $amount;
                      $trans['aff_id'] = $aff_id;
                      $trans_id = sabrayla_store('transactions', $trans);
                      sql_execute('update transactions set date_created=NOW(), time_created=NOW() where id=?', $trans_id);
                      /** LEGACY **/


		      // IO - Emailing here
                      $this->send_email($aff_id, "affiliate",0, $amount);


                      # Look up the next affiliate 
                      # This is the part that is wrong, we need to look up the parent for this
                      # individual product (or account), not from the "master"
				// IO - changed to prod_aff b/c we have a new affiliate line here 
                      $aff_id = sql_get_value('select prod_aff from members where id=?', $aff_id);
                      
               }
               
			   
               # Now that we know the total profit, we need to update the actual sales record
               sql_execute('update purchase_sales set pdc_profit=?, net_profit=?, sale_date=NOW() where id=?', 
                            $pdc_profit, // watch rounding errors
                            $pdc_profit -  $sale['other_fee'],
                            $sales_id);

               // Update number sold
               sql_execute('update products set sold=sold+1 where id=?', $sale['product_id']);


               // Execute IPN 
               $this->schedule_pdc_ipn($this->ipn_id, $sales_id, $sale['session_id'], $sale['product_id']);

			   // NOW, FINALLY Let the "Thank You" Page all the way through
               $query = sql_prepare('update purchase_session set ipn_id=?,valid=1 where session=? and valid=0', 
                                     $this->ipn_id, $session);
									 
               $this->writelog(10, $query);
               sql_execute($query);
			   		   
   	       // I am updating the buyer extra info here
			   
               $query="update buyer_info set ipn_id='".$this->ipn_id."' where session='$session'";
	       sql_execute($query);
		
               // IO - I am emailing them here 
               $this->send_email($sale['aff_id'], "vendor", $pdc_profit, 0);	   

               # This trace MUST be at the bottom of the function.  That way, we can tell if
               # the function gets all the way through, or if something dies before it
               # gets here (like it was in the mail)				

               $this->writelog(10, '-New Sale');
               break; 
      
       } /* switch */
    } /* completed */

    /** 
     * refunded transactions
     */
    
    function refunded() {    
     $this->writelog(10, "+refunded");     
 
     switch($this->ipn['item_number']) {
       case 'PDC-PREMIER':
                $this->writelog(10, '+pdc_premier - REFUND');
                $member_id = $this->ipn['custom'];
                sql_execute('update members set paid=0 where id=?', $member_id);

                $data['members_id']  = 0; // We don't add this to a members account, instead we add it to PDC
                $data['ipn_id']      = $this->ipn_id;
                $data['mc_gross']      = $this->ipn['mc_gross'];
                $data['mc_currency'] = $this->ipn['mc_currency'];
                $data['notes']       = 'PDC Premier - REFUND ' . $member_id;
                sabrayla_store('purchase_pdc', $data);
                $this->writelog(10, '-pdc_premier REFUND');
                break;
       case 'PDC-SCO':
                $this->writelog(10, '+PDC-SCO');
                $member_id = $this->ipn['custom'];


                /* ORIGINAL IMPLEMENTATION */
                ffilewrite("LC: This is a refund - substracting credits");
                $query="update members set credit=credit-125 where id='$member_id'";
                ffilewrite("PDC - SCO - $query");
                sql_execute($query);
                /* ORIGINAL IMPLEMENTATION */


                $data['ipn_id']      = $this->ipn_id;
                $data['members_id']  = $member_id;     
                $data['mc_gross']      = $this->ipn['mc_gross'];
                $data['mc_currency'] = $this->ipn['mc_currency'];
                $data['notes']       = 'PDC SCO - ' . $member_id;
                sabrayla_store('purchase_pdc', $data);

                // Subtract Extra Credits
                $data['mc_gross']      = $this->ipn['mc_gross'] * PDC_EXTRA;
                $data['mc_currency'] = 'PDC';
                $data['notes']       = 'PDC SCO (Extra)- ' . $member_id;
                sabrayla_store('purchase_pdc', $data);
                $this->writelog(10, '-PDC-SCO');
                break;

       default: // This is a refunded transaction
               $this->writelog(10, "+Refund" );
               $this->writelog(100, "=== ORIGINAL IPN ===");
               $this->writelog(100, sabrayla_sprint_r($this->parent_ipn));
              
               if($this->parent_ipn['id'] == 0) return $this->error('REFUND: could not find parent IPN');

               /* REVERT LEGACY TRANSACTIONS */
               $rows = sql_get_results('select * from transactions where ipn_id=?', $this->parent_ipn['id']);
               foreach ($rows as $row) {
                 $orig_id = $row['id'];
                 unset($row['id']);
                 $row['ipn_id'] = $this->ipn_id;
                 $row['paid_type'] = 'refund';
                 $row['amount']    = -$row['amount'];
                 $this_id = sabrayla_store('transactions', $row);
                 sql_execute('update transactions set date_created=NOW(), time_created=NOW() where id=?', $this_id);
               }
               /* REVERT LEGACY TRANSACTIONS */

               $sale = sql_get_row('select * from purchase_sales where ipn_id=?', $this->parent_ipn['id']);
               $this->writelog(100, sabrayla_sprint_r($sale) );
              
               if ($sale['id'] == 0) return $this->error('REFUND: Could not find sale'); 

               if ($sale['mc_gross'] + $this->ipn['mc_gross'] != 0) {
                  return $this->error('REFUND: This is a partial refund, no action taken');
                  // TODO: we could do a partial fee refund, and change records, etc
               }

               // Add appropriate credits to balance out
               $fee_credits =  sql_get_results('select * from purchase_pdc where sales_id=?', $sale['id']);
               foreach ($fee_credits as $credit) {
                  unset($credit['id']);
                  $credit['mc_gross'] = - $credit['mc_gross'];
                  $credit['notes']    = "[REFUND] " . $credit['notes'];
                  $credit['ipn_id']   = $this->ipn_id;
                  sabrayla_store('purchase_pdc', $credit);
               }  

               $ledger      =  sql_get_results('select * from purchase_ledger where sales_id=?', $sale['id']);
               foreach ($ledger as $credit) {
                  unset($credit['id']);
                  unset($credit['date_paid']);
                  $credit['mc_gross'] = - $credit['mc_gross'];
                  $credit['ipn_id']   = $this->ipn_id;
                  sabrayla_store('purchase_ledger', $credit); 
               } 

               // Update summary
               sql_execute('update purchase_sales set refund_date=NOW(), sale_status=? where id=?', 'refund', $sale['id']); 

               $this->schedule_pdc_ipn($this->ipn_id, $sale['id'], $sale['session_id'], $sale['product_id']);              


               // set status to refunded, refunded date to NOW()
               // Lookup all fees from purchase_pdc associated with this sale
               // add a corresponding credit referencing THIS IPN, and the original sale

               // Lookup all credits in the ledger table associated with this sale
               // add a debit for the corresponding amount, referencing this sale and this IPN
               $this->writelog(10, "-Refund");
               break;

      } /* switch */
    } /* refunded */


    function schedule_pdc_ipn($ipn_id, $sale_id, $session_id, $product_id) {
       $this->writelog(10, "+schedule_pdc_ipn($ipn_id, $sale_id, $session_id, $product_id)");
  

       $query= sql_prepare("select members.id, products.ipn, products.ipn_secret from members, products
                                    where products.id=?
                                    and   members.id=products.member_id", $product_id);

       $product_data = sql_get_row($query);
       $ipn_url = $product_data['ipn'];

       if (! $ipn_url) { // No IPN, don't do anything
           $this->writelog(10, "Not Posting .. nothing to do");
           return false; 
       }

       $data['ipn_id']     = $ipn_id;
       $data['sale_id']    = $sale_id;
       $data['session_id'] = $session_id;
       $data['pdc_secret'] = $product_data['ipn_secret'];
       $data['ipn_url']    = fix_location($ipn_url, true);
       $pdc_ipn_id = sabrayla_store('pdc_ipn', $data);

       $this->writelog(10, "READY TO POST: $ipn_url ($pdc_ipn_id) ");
       return do_pdc_ipn($pdc_ipn_id);
   }


   /* Notes: I don't like sending email here because, if it fails, it could break the script, while slowing
      down IPN's. Better to make it asynchronous soon, which shouldn't be too hard - add notification_sent
      to each sale, and just loop through that via a crontab, setting to 1 all that get sent
   */

   function send_email($aff_id, $whom, $vendor_profit, $affiliate_profit)
   {
                // don't load into production, yet
                // if (!PDC_TESTING) { return; }

		$vendor = sql_get_row('select members.username, members.email, members.name from members, products where members.id=products.member_id and products.id=?', $this->ipn['pdc_productid']);
                    
                // Load Template and munge data
                $data = $this->ipn; // data to replace in template
                $data['vendors_name']      = $vendor['name'];
                $data['vendors_username']  = $vendor['username'];
                $data['vendors_email']     = $vendor['email'];
               

                if ($aff_id > 0) {
                   $affiliate = sql_get_row('select members.username, members.email, members.name from members where members.id=?', $aff_id);
                   $data['affiliate_name']      = $affiliate['name'];
                   $data['affiliate_username']  = $affiliate['username'];
                   $data['affiliate_email']     = $affilate['email'];
                   $data['affiliate_amount']    = $affiliate_profit;
                }


                $data['pdc_fee']    = number_format($this->calc_fee($this->ipn['mc_gross']), 2, '.', '');
#               $data['profit']     = number_format($data['mc_gross'] - $data['pdc_fee'], 2, '.', '');
  
                $data['buyer_name'] = $data['first_name'] . " " . $data['last_name'];

                # map the correct IPN fields to the arbitrary ones used
                # in the templates.  
     
                $map['product_id']     = 'pdc_productid';
                $map['product_name']   = 'item_name';
                $map['product_amount'] = 'mc_gross';
                $map['pay_proc']       = 'pdc_source';
                $map['buyer_address']  = 'address_street';
                $map['buyer_city']     = 'address_city';
                $map['buyer_zip']      = 'address_zip';
                $map['buyer_country']  = 'address_country';
                $map['buyer_email']    = 'payer_email';
                 
                foreach ($map as $templatename => $realkey) {
                     $data[$templatename] = $data[$realkey]; // copy over to make template happy
                }
	
		switch ($whom)
		{
			case "vendor":
                            if ($aff_id == 0) { // No affiliate
				$subject=get_setting("vendor_sale_only_subject");
				$body=get_setting("vendor_sale_only_body");
                             } else { // we have an affiliate
				$subject=get_setting("affiliate_sale_vendor_subject");
				$body=get_setting("affiliate_sale_vendor_body");
                                $temp = $data['product_amount'] - $data['pdc_fee'] - $vendor_profit;
                                $data['affiliate_amount'] = number_format($temp, 2, '.', '');
                             }
                             $data['profit']    = number_format($vendor_profit, 2, '.', '');
                             $to = $vendor;
                             break;
			case "affiliate":
                             $subject=get_setting("affiliate_sale_aff_subject");
                             $body=get_setting("affiliate_sale_aff_body");
                             $data['profit']     = number_format($affiliate_profit, 2, '.', '');
                             $to = $affiliate;                                
			break;
		}


                $data['profit'] = number_format($data['profit'], 2, '.', '');
                if ($subject == "") {
                   error_log("Warning: No email template for: $whom - email not sent");
                   return;
                }
              
                $subject = tpl_persist($subject, $data);
                $body    = tpl_persist($body, $data);
                $mailto  = $to['email'];
 
                # DON'T EVER SEND LIVE EMAIL FROM TESTING! 
                if (! PDC_TESTING) {
                  @mail($mailto, $subject, $body, "From: PayDotCom <service@paydotcom.com>");
                }

               if (PDC_TESTING) {
                 # Send all emails so we can see what people are getting
                 $vars =  sabrayla_sprint_r($data); 
                 $body = "$body\n\n\n==DEBUG==\n\nTESTING TO: $mailto\n\n$x\n\n$vars";

                 @mail("ionut@gmail.com", $subject, $body, "From: PayDotCom <service@paydotcom.com>");
                 @mail("nickt@nicktemple.com", $subject, $body, "From: PayDotCom <service@paydotcom.com>");   		
               }

   }

} /* class PDC_IPN */    


function do_pdc_ipn($id) {

    $ipn_row = sql_get_row('select * from pdc_ipn where id=?', $id);
    $ipn_url = $ipn_row['ipn_url'];

    // Sanity checks
    if ($ipn_row['status'] != 'pending') return false; // done, or nothing to do
    if ($ipn_url == '') return;
    if ($ipn_row['count'] > 5) return; // Too many retries

    // TODO: optimize into single query
    $session_data = sql_get_row('select * from purchase_session where id=?', $ipn_row['session_id']);
    $ipn_data     = sql_get_row('select * from purchase_ipn     where id=?', $ipn_row['ipn_id']);
    $sale_data    = sql_get_row('select * from purchase_sales   where id=?', $ipn_row['sale_id']);

    $ipn_url = $ipn_url . $session_data['req'];


    # Here we need to be careful make sure we unset everything we DO NOT want to sent
    unset($ipn_data['raw']);
    unset($ipn_data['ts']);
    unset($ipn_data['id']);
    unset($ipn_data['custom']);
   
    // Add additional data from the session
    $ipn_data['pdc_session'] = $session_data['session'];
    $ipn_data['pdc_profit']  = $sale_data['pdc_profit'];
    $ipn_data['pdc_fee']     = $sale_data['pdc_fee'];
    $ipn_data['pdc_secret']  = $ipn_row['pdc_secret'];

    if (PDC_TESTING) {
      error_log("POSTING: $ipn_url");
      error_log(sabrayla_sprint_r($ipn_data));
    }

    $postdata = "pdc_version=" . urlencode('1.4');
    foreach ($ipn_data as $key => $value) {
      if (is_null($value)) continue;
      $postdata .= "&$key=" . urlencode($value);
    }

    $ch=curl_init();

    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_URL, $ipn_url);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $postdata);

    //Start ob to prevent curl_exec from displaying stuff.
    ob_start();
    curl_exec($ch);
    $result=ob_get_contents();
    ob_end_clean();
    curl_close($ch);

    // TODO: We need to determine if this was successfully completed

    // Count
    sql_execute('update pdc_ipn set count=count+1,status=? where id=?', 'done', $id);

}


?>
