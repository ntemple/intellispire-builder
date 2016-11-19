<?php
include ("vars.inc.php");

$q=new cdb;
$q->query('select 1 from members'); // connect
$q2=new cdb;

/*************/
define('_SB_VALID_',1);
require_once('lib/ipn.class.php');

# Business Logic Here
# Warning: If this is changed, and a refund is given, the refund will be for the NEW amount of credits
define ('PDC_BL_EXTRA_CREDITS', '125'); 




class pdc_IPN extends IPN {
 
  // Completed Transaction
  function completed() {
    $this->writelog(10, '+completed()');

    switch($this->ipn['item_number']) {
       case 'PDC-PREMIER':
                $member_id = $this->ipn['custom'];
 
		$query="update members set paid=1 where id='$member_id";
                $this->writelog(10, "PDC - PREMIER - $query");
		$q->query($query);
                break;
       case 'PDC-SCO':
                $member_id = $this->ipn['custom'];
                $credits = PDC_BL_EXTRA_CREDITS;                 

		$query="update members set credit=credit+$credits where id='$member_id'";
                $this->writelog(10,"PDC - SCO - $query");
		$q->query($query);
		$query="insert into transactions (id, seller_id, aff_id, amount, date_created, tr_type, ipn_id)
				values (NULL, '$custom', 0,  $credits, NOW(), 'credit', $this->ipn_id)";
		$q2->query($query);
                break;
       case 'PDC-FEES':
                $member_id = $this->ipn['custom'];
                $payment_amount = $this->ipn['mc_gross']; // How do we handle non-us currencies? Or do we?
/*
		$q2=new Cdb;
		$query="select sum(amount) as s from transactions where seller_id='$member_id' and aff_id=0 and tr_type='aff' and paid_status='not_paid' and date_created<'".date("Y-m-d", mktime(0,0,0,date("m"), 1, date("Y")))."' and tr_type='aff'";
		$q2->query($query);
		$q2->next_record();
		$sum_last=$q2->f("s");

                $query="select sum(amount) as s from transactions where seller_id='$custom' and aff_id=0 and tr_type='aff' and paid_status='not_paid' and date_created>='".date("Y-m-d", mktime(0,0,0,date("m"), 1, date("Y")))."' and tr_type='aff'";
		$q2->query($query);
		$q2->next_record();
		$sum=$q2->f("s");


*/
                # this is just so much easier
                $sum_last = sql_get_value("select sum(amount) as s from transactions where seller_id=? and aff_id=0 and tr_type='aff' and paid_status='not_paid' and date_created<'".date("Y-m-d", mktime(0,0,0,date("m"), 1, date("Y")))."' and tr_type='aff'", $member_id);
                $sum      = sql_get_value("select sum(amount) as s from transactions where seller_id=? and aff_id=0 and tr_type='aff' and paid_status='not_paid' and date_created>='".date("Y-m-d", mktime(0,0,0,date("m"), 1, date("Y")))."' and tr_type='aff'", $member_id);

                /* I don't understand this logic.  What does the payment_amount have to do with when the payment is made?
                   What if they turn out to be the same?  This needs a better system.

                */

			
		if ($sum_last==$payment_amount)
          	{
                  $this->writelog(10, "payment is for last months : $sum_last ");
		  $query="update transactions set paid_status='paid' where seller_id='$member_id' and aff_id=0 and paid_status='not_paid' and date_created<'".date("Y-m-d", mktime(0,0,0,date("m"), 1, date("Y")))."' and tr_type='aff'";
                  $this->writelog(10, "running query for updating to paid: $query");
		  $q2->query($query);					
		} 
                else if ($sum==$payment_amount)
		{
                  $this->writelog(10, "payment is for all : $sum ");
		  $query="update transactions set paid_status='paid' where seller_id='$member_id' and aff_id=0 and paid_status='not_paid' and date_created>='".date("Y-m-d", mktime(0,0,0,date("m"), 1, date("Y")))."' and tr_type='aff'";
                  $this->writelog(10, "running query for updating to paid: $query");
 		  $q2->query($query);
		} else	{
                // I believe that this should be the ONLY case ... simply add the credit

                   $this->writelog(10, payment does not match : $payment_amount - adding to credit ");
   		   $query="update members set credit=credit+$payment_amount where id='$custom'";
                   $this->writelog(10, running query for updating credit: $query");
		   $q2->query($query);
		}
		
		$query="insert into transactions (id, seller_id, aff_id, amount, date_created, tr_type, ipn_id)
		values (NULL, '$member_id', 0, $payment_amount, NOW(), 'payment', $this->ipn_id)";
		$q2->query($query)
               break;

       default: // This is a standard PDC Sale
                $this->do_sale();
     }  
     $this->writelog(10, '-completed()');
  }

  function refunded() {
     $this->writelog(10, '+refunded()');
       case 'PDC-PREMIER':
             $member_id = $this->ipn['custom'];

      	     $this->writelog("This is a refund - downgrading user");                     
             $query="update members set paid=0 where id='" . $this->ipn['custom'] . "'";
             $this->writelog(10, "PDC - PREMIER - $query");
  	     $q->query($query);             
             break;
       case 'PDC-SCO':
             $member_id = $this->ipn['custom'];
             $credits = PDC_BL_EXTRA_CREDITS;                 

             $this->writelog(10,"This is a refund - substracting credits");

             $orig_transaction = sql_get_values('select amount

             $query="update members set credit=credit -$credits where id='$member_id'";
    	     $q->query($query);
             $this->writelog(10,"PDC - SCO - $query");

/* PLEASE CHECK NEW LOGIC HERE
             # NLT: This has an associated item in the transactions table
             # shouldn't we put a reversal in the transactions table, too??
             # Since we have the original IPN, we should be able to look up the original transaction, too
             # and decrement the appropriate amount of credits
             # 
             # Ionut, is there any reason not to do the below query?

  	     $query="insert into transactions (id, seller_id, aff_id, amount, date_created, tr_type, ipn_id)
				values (NULL, '$custom', 0, -$credit, NOW(), 'credit', $this->ipn_id)";
	     $q2->query($query);
*/
             break;
       case 'PDC-FEES:
             $member_id = $this->ipn['custom'];
             $this->writelog(10,This is a refund - fee refund - sending mail");
	     mail("ionutgr@gmail.com", "PDC - FEE - REFUND", "ID: $member" , "From: PDC Script <pdc@paydotcom.com>");
             break;
       default:  // this is a standard PDC Refund      
             $this->do_refund();
             break;
     }
     $this->writelog(10, '-refunded()');
  }


  function do_sale() {
     $this->writelog(10, '+do_sale()');
     $payment_amount = $this->ipn['mc_gross']; // How do we handle non-us currencies? Or do we?
     $fee = calc_fee($payment_amount);

     $a=explode("|", $this->ipn['custom']);

     $aff_id    =$a[0];
     $product_id=$a[1];
     $camp_id   =$a[2];
     $tool      =$a[3];
     
     if ($aff_id != "")  {
	ffilewrite("Aff id = $aff_id - this is an affiliate sale");			
	$query="select * from members where '$aff_id'=MD5(CONCAT('PDCX-',username))";
        $affiliate_record = sql_get_values($query);
        $aff_id = $affiliate_record[id];
     }

     
     $query="select members.id, members.credit, products.ipn, products.ipn_secret from members, products where
					products.id='$product_id' and
					members.id=products.member_id";


     ffilewrite("executing query for user credit and IPN URL: $query");
     $q->query($query);		
     $q->next_record();

     $tr_status = "ok";
     $paid_type = 'normal';
     $ipn_url   = $q->f("ipn");
     $ipn_secret= $q->f("ipn_secret");
     $credit    = $q->f("credit");
     $seller_id = $q->f('id');

     ffilewrite("Seller ID = $seller_id");
			
     // I'm not sure I completely understand this			
     // I believe credit is the amount of money they have on account with PDC.
     // TODO: centralized accounting structure for fees
     if ($credit > $fee) {
	  ffilewrite("Credit ok, $credit, more than fee $fee");
	  $paid_type='credit';
	  $credit=$credit-$fee;
	  $query="update members set members.credit='$credit' where id='$seller_id'";
   	  ffilewrite("executing query for new user credit: $query");
	  $q->query($query);
     }

	
    $query="select * from products where id='$product_id'";
    $q->query($query);
    $q->next_record();
    
			
    // assign posted variables to local variables
    $item_name        = $this->ipn['item_name'];
    $item_number      = $this->ipn['item_number'];
    $payment_status   = $this->ipn['payment_status'];
    $payment_currency = $this->ipn['mc_currency'];
    $txn_id           = $this->ipn['txn_id'];
    $receiver_email   = $this->ipn['receiver_email'];
    $payer_email      = $this->ipn['payer_email'];
    $first_name       = $this->ipn['first_name'];
    $last_name        = $this->ipn['last_name'];
    $address_street   = $this->ipn['address_street'];
    $address_city     = $this->ipn['address_city'];
    $address_state    = $this->ipn['address_state'];
    $address_zip      = $this->ipn['address_zip'];
    $address_country  = $this->ipn['address_country'];
    $payer_status     = $this->ipn['payer_status'];
    $txn_type         = $this->ipn['txn_type'];
    $custom           = $this->ipn['custom'];
    $pending_reason   = $this->ipn['pending_reason'];			

    $total_payment=$payment_amount;
    $vendor_amount=$payment_amount;


    // Handle affiliate comissions
    if ($aff_id != "") {
        // WARNING!!! This does a COMPLETE TABLE SCAN, applying MD5 to every record. 
        // This will take down the server, eventually.
        // We need to pre-populate a column with this value, and index it!
        $aff_id = sql_get_value("select id from members where MD5(CONCAT('PDCX-',username)=?", $aff_id);

	$query="select * from percent where product_id='$product_id' order by level";
	$q->query($query);
	while ($q->next_record()) {
	   $seller_amount=($total_payment-$fee)*$q->f("value")/100;
	   if ($vendor_amount-$seller_amount > 0 && $vendor_amount-$seller_amount < $vendor_amount)
	   {
	      $vendor_amount = $vendor_amount - $seller_amount;
				
	       $query="insert into t+6ransactions 
						(id, seller_id, aff_id, amount, tr_status, date_created, time_created, 
						product_id, first_name, last_name, email, address, city, state, zip, 
						country, payer_status, item_name, item_number, pay_proc, paid_status, 
						aff_opinion, tr_type)
						values
							(NULL,
							'$seller_id',
							'$aff_id',
							'$seller_amount',
							'$tr_status',
							NOW(),
							NOW(),
							'$product_id',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'$item_name',
							'$item_number',
							'paypal',
							'not_paid',
							'none',
							'aff')";

					ffilewrite("Running mysql query as vendor sale : $query");
					$q2->query($query);
					$query="select aff from members where id='$aff_id'";
					$q2->query($query);
					$q2->next_record();
					$aff_id=$q->f("aff");

				}
			}





    }



			$query="insert into transactions 
				(id, seller_id, aff_id, amount, tr_status, date_created, time_created, 
				product_id, first_name, last_name, email, address, city, state, zip, 
				country, payer_status, item_name, item_number, pay_proc, paid_status, 
				aff_opinion, tr_type)
				values
					(NULL,
					'$seller_id',
					'0',
					'$payment_amount',
					'$tr_status',
					NOW(),
					NOW(),
					'$product_id',
					'$first_name',
					'$last_name',
					'$payer_email',
					'$address_street',
					'$address_city',
					'$address_state',
					'$address_zip',
					'$address_country',
					'$payer_status',
					'$item_name',
					'$item_number',
					'paypal',
					'not_paid',
					'none',
					'sale')";
			ffilewrite("Running mysql query as vendor sale : $query");
			$q->query($query);
			$query="insert into transactions 
				(id, seller_id, aff_id, amount, tr_status, date_created, time_created, 
				product_id, first_name, last_name, email, address, city, state, zip, 
				country, payer_status, item_name, item_number, pay_proc, paid_status, 
				aff_opinion, tr_type, paid_type)
				values
					(NULL,
					'$seller_id',
					'0',
					'$fee',
					'$tr_status',
					NOW(),
					NOW(),
					'$product_id',
					'$first_name',
					'$last_name',
					'$payer_email',
					'$address_street',
					'$address_city',
					'$address_state',
					'$address_zip',
					'$address_country',
					'$payer_status',
					'Vendor Fee for Product $item_name',
					'$item_number',
					'paypal',
					'not_paid',
					'none',
					'aff',
					'$paid_type')";				
			ffilewrite("Running mysql query as vendor fee : $query");
			$q->query($query);
			
			$query="update products set sold=sold+1 where id='$product_id'";
			
			ffilewrite("Running mysql query for increasing sold : $query");
			$q->query($query);


    if ($ipn_url!="") {
       // TODO: if this times out, then a) we've potentially lost our database connection and
       // b) they will never have received the POST
       // this *must* be asynchronous

       $post_info = $this->ipn;
       $post_info['pdc_fee'] = $fee;
       $post_info['pdc_your_profit'] = $payment_amount - $fee;
       $post_info['pdc_number_of_affiliates_credited'] = 0;
       $post_info['pdc_version']                       = 2;
       $post_info['pdc_secret'] = $q->f('ipn_secret');
       HTTP_Post($ipn_url, $post_info);
    }



     $this->writelog(10, '-do_sale()');
  }


  function do_refund() {
     $this->writelog(10, '+do_refund()');


			if ($reason_code=='refund') 
			{
				$fee=0;
				ffilewrite("This is a refund - setting fee as refund");
				if ((0-$payment_amount)>1 && (0-$payment_amount)<=10) $fee=-1;
				if ((0-$payment_amount)>10.01 && (0-$payment_amount)<=20) $fee=-2;
				if ((0-$payment_amount)>20 ) $fee=-3;
				$tr_status="refund";
			}
			if ($reason_code=='refund') 
			{
				$fee=0;
				ffilewrite("This is a refund - setting fee as refund");
				if ((0-$payment_amount)>1 && (0-$payment_amount)<=10) $fee=-1;
				if ((0-$payment_amount)>10.01 && (0-$payment_amount)<=20) $fee=-2;
				if ((0-$payment_amount)>20 ) $fee=-3;
				$tr_status="refund";
			}



     $this->writelog(10, '-do_refund()');
  }




}


/*************/
$ipnInstance = new pdc_IPN();
$ipnInstance->process($_POST);



function ffilewrite($str)
{
  global $ipnInstance;
  $ipnInstance->writelog(20, $str);
}

function calc_fee($payment_amount) {

  $fee=0;
  if ($payment_amount>1 && $payment_amount<=10) $fee=1;
  if ($payment_amount>10.01 && $payment_amount<=20) $fee=2;
  if ($payment_amount>20 ) $fee=3;
  return $fee;
}


		{
			ffilewrite("Aff id = $aff_id - this is an affiliate sale");
			
			
			

			
			
			ffilewrite("The fee is set to $fee");
			ffilewrite("Checking user credit");
			$query="select members.credit from members, products where
					products.id='$product_id' and
					members.id=products.member_id";
			ffilewrite("executing query for user credit: $query");
			
			$q->query($query);
			
			$q->next_record();
			$credit=$q->f("credit");
			$paid_type='normal';

			if ($credit>$fee)
			{
				ffilewrite("Credit ok, $credit, more than fee $fee");
				$paid_type='credit';
				$credit=$credit-$fee;
				$query="update members, products set members.credit='$credit' where
						products.id='$product_id' and
						members.id=products.member_id";
				ffilewrite("executing query for new user credit: $query");
				$q->query($query);
			}
			else
			{
				ffilewrite("$credit, less than fee $fee");
			}
			
			$query="select * from products where id='$product_id'";
			$q->query($query);
			$q->next_record();
			$seller_id=$q->f("member_id");
			ffilewrite("Vendor ID = $seller_id");


			$query="insert into transactions 

				(id, seller_id, aff_id, amount, tr_status, date_created, time_created, 
				product_id, first_name, last_name, email, address, city, state, zip, 
				country, payer_status, item_name, item_number, pay_proc, paid_status, 
				aff_opinion, tr_type)
				values
					(NULL,
					'$seller_id',
					'0',
					'$payment_amount',
					'$tr_status',
					NOW(),
					NOW(),
					'$product_id',
					'$first_name',
					'$last_name',
					'$payer_email',
					'$address_street',
					'$address_city',
					'$address_state',
					'$address_zip',
					'$address_country',
					'$payer_status',
					'$item_name',
					'$item_number',
					'paypal',
					'not_paid',
					'none',
					'sale')";
			ffilewrite("Running mysql query as vendor sale : $query");
			$q->query($query);
			$query="insert into transactions 
				(id, seller_id, aff_id, amount, tr_status, date_created, time_created, 
				product_id, first_name, last_name, email, address, city, state, zip, 
				country, payer_status, item_name, item_number, pay_proc, paid_status, 
				aff_opinion, tr_type, paid_type)
				values
					(NULL,
					'$seller_id',
					'0',
					'$fee',
					'$tr_status',
					NOW(),
					NOW(),
					'$product_id',
					'$first_name',
					'$last_name',
					'$payer_email',
					'$address_street',
					'$address_city',
					'$address_state',
					'$address_zip',
					'$address_country',
					'$payer_status',
					'Vendor Fee for Product $item_name',
					'$item_number',
					'paypal',
					'not_paid',
					'none',
					'aff',
					'$paid_type')";				
			ffilewrite("Running mysql query as vendor fee : $query");
			$q->query($query);
			
			$query="update products set sold=sold+1 where id='$product_id'";
			
			ffilewrite("Running mysql query for increasing sold : $query");
			$q->query($query);
			ffilewrite("IPN SCRIPT END **** ");
			die();





			
		}
		else
		{
		}
	}
	else
	{
		save_setting("txn_id", $txn_id);
		ffilewrite("TXN ID was previously processed");
	}
	// check that txn_id has not been previously processed
	// check that receiver_email is your Primary PayPal email
	// check that payment_amount/payment_currency are correct
	// process payment
}
}
}
?>
