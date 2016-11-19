<?php
include ("../vars.inc.php");
require_once('/usr/local/lib/php/core/sabrayla.php');

define('PDC_TESTING', false);

class Xdb extends cdb {
  function query($q) {
    print "$q\n";
    parent::query($q);
  }
}

$q=new Xdb;
$q->query('select 1 from members'); // connect

$q2=new Xdb;
function ffilewrite($str)
{
	$fp=fopen("ipn.paypal.log.txt", "a");
	fwrite($fp, date("Y-m-d H:i")."    ".$str."\n");
	fclose($fp);
}

$ipn_good = true;


$values = sql_get_results("select * from purchase_ipn where id > 115  and id <  190  and source='paypal'");

foreach ($values as $_POST)  {
  $NOW = $_POST['ts'];
  list($DATE, $TIME) = explode(' ' , $NOW);

$NOW =  "'" . $NOW  ."'";
$DATE = "'" . $DATE ."'";
$TIME = "'" . $TIME . "'";

print_r(array($NOW, $DATE, $TIME));


foreach ($_POST as $key => $value) {
$value = urlencode(stripslashes($value));
$req .= "&$key=$value";
}
ffilewrite("-----------------------------------------------");
ffilewrite("New IPN request $REMOTE_ADDR");
ffilewrite($req);


###################################################################
# PDC TODO
# When moving over, we need to take all the below information from
# $ipn->ipn[''] instead of POST, so that we can properly process
# StormPay, too
##################################################################

// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$address_street = $_POST['address_street'];
$address_city = $_POST['address_city'];
$address_state = $_POST['address_state'];
$address_zip = $_POST['address_zip'];
$address_country = $_POST['address_country'];
$payer_status = $_POST['payer_status'];
$txn_type = $_POST['txn_type'];
$custom = $_POST['custom'];
$pending_reason=$_POST['pending_reason'];
$id=$custom;
ffilewrite("Item name: $item_name");
ffilewrite("Item number: $item_number");
ffilewrite("Payment amount: $payment_amount");
ffilewrite("TXN ID: $txn_id");
ffilewrite("Receiver email: $receiver_email");
ffilewrite("Payer email: $payer_email");
ffilewrite("TXN type: $txn_type");
ffilewrite("Custom: $custom");
ffilewrite("Processing custom data:");

$a=explode("|", $custom);
$aff_id=$a[0];
$product_id=$a[1];
$camp_id=$a[2];
$tool=$a[3];
$session = $a[4]; // New


ffilewrite("Affiliate ID: $aff_id");
ffilewrite("Product ID: $product_id");
ffilewrite("Campaign ID: $camp_id");
ffilewrite("Tool: $tool");
ffilewrite("Session: $session");
ffilewrite("*** Custom data processed.");
ffilewrite("Posting back to paypal");




ffilewrite("IPN_GOOD: $ipn_good");
if ($ipn_good) {

ffilewrite("Data verification with paypal completed successfully");
ffilewrite("Processing payment ...");

if (PDC_TESTING) {
  if ($session != "") {
    $query = sql_prepare('update purchase_session set ipn_id=?,valid=1 where session=? and valid=0', $ipn->ipn_id, $session);
    ffilewrite("Good Transaction: $query");
    $q->query($query);
  }
} // end testing code

	// check the payment_status is Completed
	if ($pending_reason=="echeck") 
	{
		ffilewrite("This is an uncleared echeck... EXITING...");
                continue;
	}
	if ($txn_id!=get_setting("txn_id"))
	{
		ffilewrite("TXN ID was NOT previously processed");
		save_setting("txn_id", $txn_id);
		if ($item_number=="PDC-PREMIER")
		{
			if ($reason_code=='refund') 
			{
				ffilewrite("This is a refund - downgrading user");
				$query="update members set paid=0 where id='$custom'";
				ffilewrite("PDC - PREMIER - $query");
				$q->query($query);
			}
			else
			{
				$query="update members set paid=1 where id='$custom'";
				ffilewrite("PDC - PREMIER - $query");
				$q->query($query);
			}
			ffilewrite("END****");
			continue;

		}		
		
		if ($item_number=="PDC-SCO")
		{
			if ($reason_code=='refund') 
			{
				ffilewrite("This is a refund - substracting credits");
				$query="update members set credit=credit-125 where id='$custom'";
				ffilewrite("PDC - SCO - $query");
				$q->query($query);
			}
			else
			{
				$query="update members set credit=credit+125 where id='$custom'";
				ffilewrite("PDC - SCO - $query");
				$q->query($query);
				$query="insert into transactions (id, seller_id, aff_id, amount, date_created, tr_type)
				values (NULL, '$custom', 0, 125, $NOW, 'credit')";
				$q2->query($query);
			}
			ffilewrite("END****");
			continue;

		}		

		if ($item_number=="PDC-FEES")
		{
			if ($reason_code=='refund') 
			{
				ffilewrite("This is a refund - fee refund - sending mail");
				mail("ionutgr@gmail.com", "PDC - FEE - REFUND", "ID: $custom " , "From: PDC Script <pdc@paydotcom.com>");
				
			}
			else
			{
				$q2=new Xdb;
				$query="select sum(amount) as s from transactions where seller_id='$custom' and aff_id=0 and tr_type='aff' and paid_status='not_paid' and date_created<'".date("Y-m-d", mktime(0,0,0,date("m"), 1, date("Y")))."' and tr_type='aff'";
				$q2->query($query);
				$q2->next_record();
				$sum_last=$q2->f("s");
				$query="select sum(amount) as s from transactions where seller_id='$custom' and aff_id=0 and tr_type='aff' and paid_status='not_paid' and date_created>='".date("Y-m-d", mktime(0,0,0,date("m"), 1, date("Y")))."' and tr_type='aff'";
				$q2->query($query);
				$q2->next_record();
				$sum=$q2->f("s");
				
				if ($sum_last==$payment_amount)
				{
					ffilewrite(" payment is for last months : $sum_last ");
					$query="update transactions set paid_status='paid' where seller_id='$custom' and aff_id=0 and paid_status='not_paid' and date_created<'".date("Y-m-d", mktime(0,0,0,date("m"), 1, date("Y")))."' and tr_type='aff'";
					ffilewrite("running query for updating to paid: $query");
					$q2->query($query);
					
				}
				else
				if ($sum==$payment_amount)
				{
					ffilewrite(" payment is for all : $sum ");
					$query="update transactions set paid_status='paid' where seller_id='$custom' and aff_id=0 and paid_status='not_paid' and date_created>='".date("Y-m-d", mktime(0,0,0,date("m"), 1, date("Y")))."' and tr_type='aff'";
					ffilewrite("running query for updating to paid: $query");
					$q2->query($query);
				}
				else
				{
					ffilewrite(" payment does not match : $payment_amount - adding to credit ");
					$query="update members set credit=credit+$payment_amount where id='$custom'";
					ffilewrite("running query for updating credit: $query");
					$q2->query($query);
					//mail("ionutgr@gmail.com", "PDC - FEE - NO MATCH", "ID: $custom \n AMOUNT: $payment_amount " , "From: PDC Script <pdc@paydotcom.com>");
				}
				$query="insert into transactions (id, seller_id, aff_id, amount, date_created, tr_type)
				values (NULL, '$custom', 0, $payment_amount, $NOW, 'payment')";
				$q2->query($query);

				
						
			}
			ffilewrite("END****");
			continue;

		}		


		if ($aff_id!="")
		{
			ffilewrite("Aff id = $aff_id - this is an affiliate sale");
			
			$query="select * from members where '$aff_id'=MD5(CONCAT('PDCX-',username))";
			$q->query($query);
			
			$q->next_record();
			
			$aff_id=$q->f("id");
			
			

			


			$fee=0;
			if ($payment_amount>1 && $payment_amount<=10) $fee=1;
			if ($payment_amount>10.01 && $payment_amount<=20) $fee=2;
			if ($payment_amount>20 ) $fee=3;
			

			if ($reason_code=='refund') 
			{
				$fee=0;
				ffilewrite("This is a refund - setting fee as refund");
				if ((0-$payment_amount)>1 && (0-$payment_amount)<=10) $fee=-1;
				if ((0-$payment_amount)>10.01 && (0-$payment_amount)<=20) $fee=-2;
				if ((0-$payment_amount)>20 ) $fee=-3;
				$tr_status="refund";
			}
			
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
			$total_payment=$payment_amount;
			$vendor_amount=$payment_amount;
			
			$query="select * from percent where product_id='$product_id' order by level";
			$q->query($query);
			while ($q->next_record())
			{
				$seller_amount=($total_payment-$fee)*$q->f("value")/100;
				if ($vendor_amount-$seller_amount>0 && $vendor_amount-$seller_amount<$vendor_amount)
				{
					
					$vendor_amount=$vendor_amount-$seller_amount;
					
					
					$query="insert into transactions 
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
							$NOW,
							$TIME,
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
					if ($aff_id==0) break;

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
					$NOW,
					$TIME,
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
					$NOW,
					$TIME,
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
			continue;





			
		}
		else
		{
			ffilewrite("Aff id = $aff_id - this is a vendor sale");
			$tr_status="ok";
			
			$fee=0;
			if ($payment_amount>1 && $payment_amount<=10) $fee=1;
			if ($payment_amount>10.01 && $payment_amount<=20) $fee=2;
			if ($payment_amount>20 ) $fee=3;
			

			if ($reason_code=='refund') 
			{
				$fee=0;
				ffilewrite("This is a refund - setting fee as refund");
				if ((0-$payment_amount)>1 && (0-$payment_amount)<=10) $fee=-1;
				if ((0-$payment_amount)>10.01 && (0-$payment_amount)<=20) $fee=-2;
				if ((0-$payment_amount)>20 ) $fee=-3;
				$tr_status="refund";
			}
			
			ffilewrite("The fee is set to $fee");
			ffilewrite("Checking user credit");

                        // NLT - moved from below
                        $credit=$q->f("credit");
                        $paid_type='normal';


                        // NLT added ipn_secret to select
			$query="select members.credit, products.ipn, products.ipn_secret from members, products where
					products.id='$product_id' and
					members.id=products.member_id";
			ffilewrite("executing query for user credit and IPN URL: $query");
			
			$q->query($query);
			
			$q->next_record();
			$ipn_url=$q->f("ipn");
			$ipn_secret=$q->f("ipn_secret");
			
			
		        // NLT - moved to above IPN query 	
                        // At this point, $q has already ben overwritten!
			// $credit=$q->f("credit");
			// $paid_type='normal';

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
			if ($ipn_url!="")
			{
				ffilewrite("ipn url : $ipn - trying to post to it... ");
                                // NLT Added version
				$post_info=$req."&pdc_fee=$fee&pdc_your_profit=".($payment_amount-$fee)."&pdc_number_of_affiliates_credited=0&pdc_secret=$ipn_secret&pdc_version=1";
				HTTP_Post($ipn_url, $post_info);
				ffilewrite("posted: $ipn_url, $post_info");
			}
			else
			{	
				ffilewrite("ipn url is empty... not posting to it");
			}
			
			ffilewrite("Seller ID = $seller_id");
			
			
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
					$NOW,
					$TIME,
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
					$NOW,
					$TIME,
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
			continue;
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
?>
