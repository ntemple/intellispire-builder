<?php
  /**                                    
  * @version    $Id: inc.payment.functions.php 21 2013-03-15 19:35:01Z ntemple $
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
  /* Changelog: 1.0.1: Remove Stormpay */

  // functions for payments
  function get_pay_buttons($member_id, $product_id, $aff_id, $session, $step2, $button_id)
  {
    $q=new Cdb;
    $q2=new CDB;
    $q3=new Cdb;
    $q_memberinfo=new CDB;
    $query="select * from products where id='$product_id'";
    $q->query($query);
    $q->next_record();
    if ($q->f("cb_but")!=0)
    {
      $query="select * from buybuttons where id='".$q->f("cb_but")."' and product_id='$product_id'";
      $q2->query($query);
      $q2->next_record();
      if ($q2->f("url")==1)
      {
        $cb_button=$q2->f("image");
      }
      else $cb_button='images/buybuttons/'.$q2->f("image");
    }
    else $cb_button='images/clickbank-logo.gif';
    if ($q->f("pp_but")!=0)
    {
      $query="select * from buybuttons where id='".$q->f("pp_but")."' and product_id='$product_id'";
      $q2->query($query);
      $q2->next_record();
      if ($q2->f("url")==1)
      {
        $pp_button=$q2->f("image");
      }
      else $pp_button='images/buybuttons/'.$q2->f("image");
    }
    else $pp_button='https://www.paypal.com/en_US/i/btn/x-click-but5.gif';
    if ($q->f("2co_but")!=0)
    {
      $query="select * from buybuttons where id='".$q->f("2co_but")."' and product_id='$product_id'";
      $q2->query($query);
      $q2->next_record();
      if ($q2->f("url")==1)
      {
        $b2co_button=$q2->f("image");
      }
      else $b2co_button='images/buybuttons/'.$q2->f("image");
    }
    else $b2co_button='images/2checkout_logo.gif';
    if ($q->f("auth_but")!=0)
    {
      $query="select * from buybuttons where id='".$q->f("auth_but")."' and product_id='$product_id'";
      $q2->query($query);
      $q2->next_record();
      if ($q2->f("url")==1)
      {
        $bauth_button=$q2->f("image");
      }
      else $bauth_button='images/buybuttons/'.$q2->f("image");
    }
    else $bauth_button='images/auth.gif';
    if (get_setting("ask_country_on_product")==1 || $q->f("signup")==1 || (get_setting("free_signup")!=1 && get_setting("enable_oto_paid_signup")==1 && ($q->f("nid")=="OTO1" || $q->f("nid")=="OTO2" || $q->f("nid")=="OTO_BCK" || $q->f("nid")=="OTO2_BCK"))) {
      $shiping_country_list_paypal.="<br><select name=\"country\" onchange=\"
      split_array=(document.paypal_form".$button_id.".country.value).split(',');
      if (split_array[0]!='') { 
      document.paypal_form0.shipping.value=(split_array[0]);
      split_custom=(document.paypal_form".$button_id.".custom.value).split('|');
      document.paypal_form0.custom.value=(split_custom[0]+'|'+split_array[1])
      } else { 
      document.paypal_form0.shipping.value='';
      split_custom=(document.paypal_form".$button_id.".custom.value).split('|');
      document.paypal_form0.custom.value=(split_custom[0]+'|23dfgh345645w')
      alert('".get_setting("no_shipping")."') 
      return false;
      }
      \" id=\"country\" style=\"width: 15em;\">";
      $shiping_country_list_paypal.="<option value=\",6h7ednh74rjd7e\">Choose country</option>";
      $query="SELECT fee FROM products where id='$product_id'";
      $q3->query($query);
      $q3->next_record();
      $fees_arr = array();
      $fees1_arr = explode('|',$q3->f('fee'));
      foreach ($fees1_arr as $key=>$value){
        $pos = strpos($value,';');
        $fee = substr($value,0,$pos);
        $country_id = substr($value,$pos+1);
        $fees_arr [$country_id] = $fee;
      }
      $query="SELECT * FROM countries WHERE  id!='0' order by country asc";
      $q2=new Cdb;
      $q2->query($query);
      while ($q2->next_record()) {
        $country_id_fee=$q2->f('id');
        $shiping_country_list_paypal.="<option value=\"".$fees_arr [$country_id_fee].",".$q2->f('country_id')."\">".$q2->f('country')."</option>";
      }
      $shiping_country_list_paypal.="</select>";
    } else {
      $shiping_country_list_paypal="";
    }
    if ($q->f("physical")==1) {
      $q2->query("SELECT fee FROM products WHERE id='$product_id'");
      $q2->next_record();
      $fees_arr = array();
      $fees1_arr = explode('|',$q2->f('fee'));
      foreach ($fees1_arr as $key=>$value){
        $pos = strpos($value,';');
        $fee = substr($value,0,$pos);
        $country_id = substr($value,$pos+1);
        $fees_arr [$country_id] = $fee;
      }
      $query="SELECT country FROM members WHERE id='$member_id'";
      $q2->query($query);
      $q2->next_record();
      $country_code="|".$q2->f("country");
      if (get_setting("ask_country_on_product")!=1 && $q2->nf()>0) {$shipping_fee=$fees_arr [$country_id];} else {$shipping_fee="";}
      $ask_shipping_adress=2;
      if (get_setting("ask_country_on_product")==1 || $q->f("signup")==1 || (get_setting("free_signup")!=1 && get_setting("enable_oto_paid_signup")==1 && ($q->f("nid")=="OTO1" || $q->f("nid")=="OTO2" || $q->f("nid")=="OTO_BCK" || $q->f("nid")=="OTO2_BCK"))) {
        $show_shipping_paypal="<br>Shipping cost: <INPUT id=shipping type=\"texterea\" value=\"{shipping_fee}\" size=\"4\" readonly=\"true\" name=shipping> ".get_setting("paypal_currency");
      } else {
        $show_shipping_paypal="<INPUT id=shipping type=\"hidden\" value=\"{shipping_fee}\" size=\"4\" readonly=\"true\" name=shipping>";
      }
      $on_click_paypal="onclick=\"if (document.paypal_form".$button_id.".shipping.value=='') {alert('".get_setting("no_shipping")."'); return false}\"";
    } else {
      $shiping_country_list_paypal="";
      $show_shipping="";
      $shipping_fee="";
      $ask_shipping_adress=1;
    }
    $prodpaypal=$q->f("paypal");
    $item_number=$q->f("nid");
    $price=$q->f("price");
    $display_name=$q->f("display_name");
    $flag_recurring=false;
    if ($q->f("recurring")==1)
    {
      $flag_recurring=true;
      $recurring_times=$q->f("times");
      $recurring_period=$q->f("period");
      $recurring_type=$q->f("type");
    }
    $trial_flag = false;
    if($q->f("trial") == 1){
      $trial_flag = true;
      $trial_amount=$q->f("trial_amount");
      $trial_period=$q->f("trial_period");
      $trial_period_type=$q->f("trial_period_type");
    }
    // checking if the session exists in the database
    $query="select * from session where session_id='$session'";
    $q->query($query);
    if ($q->nf()==0)
    {
      if ($step2==1)
      {
        die("FATAL ERROR: Attempt to go directly to 2nd step payment");
      }
      else
      {
        //this is a new session that must be inserted into the database
        $cb_time = time();
        $q->query("SELECT signup,nid FROM products WHERE id = '$product_id'");
        $q->next_record();
        $atsignup_ss = $q->f('signup');
        $nid_ss=$q->f('nid');
        if (get_setting("enable_oto_paid_signup") && ($nid_ss=="OTO1" || $nid_ss=="OTO2" || $nid_ss=="OTO_BCK" || $nid_ss=="OTO2_BCK"))
        {
          $query="insert into session (id, session_id, product_id, member_id, paid, paid_step2, affiliate_id, stamp, secret_pay_id,ip) values (NULL, '$session', '$product_id', '$member_id', 0,0,'".$_COOKIE["aff"]."', '".$cb_time."', MD5('".get_setting('secret_string').getenv('REMOTE_ADDR')."'),'".getenv('REMOTE_ADDR')."')";
          $q->query($query);
        }elseif ($atsignup_ss){
          $query="insert into session (id, session_id, product_id, member_id, paid, paid_step2, affiliate_id, stamp, secret_pay_id,ip) values (NULL, '$session', '$product_id', '$member_id', 0,0,'".$_COOKIE["aff"]."', '".$cb_time."', MD5('".get_setting('secret_string').getenv('REMOTE_ADDR')."'),'".getenv('REMOTE_ADDR')."')";
          $q->query($query);
        }else{
          $query="insert into session (id, session_id, product_id, member_id, paid, paid_step2, affiliate_id, stamp, secret_pay_id,ip) values (NULL, '$session', '$product_id', '$member_id', 0,0,'".$_COOKIE["aff"]."', '".$cb_time."', MD5('".get_setting('secret_string').getenv('REMOTE_ADDR')."'),'".getenv('REMOTE_ADDR')."')";
          $q->query($query);
        }
      }
    }
    if ($step2==1) $st2="&step2=1";
    else $st2="";
    // block for variables init
    $custom=$session; // generating new IPN session
    $return_url_paypal=get_setting("site_full_url")."pay.return.php?s=".$custom.$st2;
    $notify_url_paypal=get_setting("site_full_url")."pay.ipn.paypal.php";
    $q->query("SELECT nid,signup FROM products WHERE id='$product_id'");
    $q->next_record();
    if($q->f("signup")==1){
      $cancel_return=(strrpos(get_setting('site_full_url'),'/') == strlen(get_setting('site_full_url'))-1 ? get_setting('site_full_url') : get_setting('site_full_url').'/').'index.php';
    }elseif($q->f("nid")=='OTO1')
      $cancel_return=(strrpos(get_setting('site_full_url'),'/') == strlen(get_setting('site_full_url'))-1 ? get_setting('site_full_url') : get_setting('site_full_url').'/').'oto.php';
    elseif($q->f("nid")=='OTO_BCK')
      $cancel_return=(strrpos(get_setting('site_full_url'),'/') == strlen(get_setting('site_full_url'))-1 ? get_setting('site_full_url') : get_setting('site_full_url').'/').'oto_bck.php';	
    elseif($q->f("nid")=='OTO2')
      $cancel_return=(strrpos(get_setting('site_full_url'),'/') == strlen(get_setting('site_full_url'))-1 ? get_setting('site_full_url') : get_setting('site_full_url').'/').'continue.php';	
    elseif($q->f("nid")=='OTO2_BCK')
      $cancel_return=(strrpos(get_setting('site_full_url'),'/') == strlen(get_setting('site_full_url'))-1 ? get_setting('site_full_url') : get_setting('site_full_url').'/').'oto2_bck.php';	
    else $cancel_return=(strrpos(get_setting('site_full_url'),'/') == strlen(get_setting('site_full_url'))-1 ? get_setting('site_full_url') : get_setting('site_full_url').'/').'member.area.in.php';	
    // paypal emails will be default as webmaster's
    $paypal_email=get_setting("paypal_email");
    $aff_flag=false;
    $query="select * from members where id='$aff_id'";
    $q->query($query);
    $q->next_record();
    $aff_membership_id=$q->f("membership_id");
    $aff_s_date=$q->f("s_date");
    $aff_paypal_email=$q->f("paypal_email");
    $jv = $q->f("jv");
    // END NEW
    if ($step2!=1)
    {
      if ($aff_id)
      {
        if (get_setting("splitoption")==2){
          $query="select * from levels where product_id='$product_id' and membership_id='$aff_membership_id' and level=1 order by id desc limit 0,1";
        }else {
          $query="select * from levels where product_id='$product_id' and membership_id='$aff_membership_id' and level=1 order by id asc limit 0,1";
        }
        $q->query($query);
        $q->next_record();

        if ($q->f("paytype")=="percent_split" || $q->f("paytype")=="full_amount_split")

        {
          if (($jv==1 && $q->f("jv1")==0) || ($jv==2 && $q->f("jv2")==0) || ($jv==0 && $q->f("value")==0) || ($q->f("highcom")==1 && $q->f("highval")==0))
          {
            $step2=1;
          }
        }
      }
    }
    if ($step2==1)
    {
      // here goes the code for step 2 payment split.
      if (get_setting("splitoption")==2 && $aff_paypal_email)
      {
        $paypal_email=$aff_paypal_email;
        $p_paypal=$paypal_email;
      }
      else
      {
        $p_paypal=get_setting("paypal_email");
      }
      if (get_setting("accept_paypal") && trim($p_paypal)!="")
      {
        FFileRead("templates/pay.paypal.html", $pay_paypal_t);
      }
      if (get_setting("splitoption")==2){
        $query="select * from levels where product_id='$product_id' and membership_id='$aff_membership_id' and level=1 order by id asc limit 0,1";
      }else {
        $query="select * from levels where product_id='$product_id' and membership_id='$aff_membership_id' and level=1 order by id desc limit 0,1";

      }
      $q->query($query);
      $q->next_record();
      if ($jv == 1){
        $jv_amount = $q->f("jv1");
      }elseif ($jv == 2){
        $jv_amount = $q->f("jv2");
      }else {
        if ($q->f("highcom")==1)
        {
          $b=time();
          $c=($aff_s_date+$q->f("highdays")*88400);

          if ($c > $b)
          {
            $jv_amount=$q->f("highval");
          }
          else
          {
            $jv_amount = $q->f("value");
          }
        }
        else
        {

          $jv_amount = $q->f("value");
        }
      }
      if ($q->f("paytype")=="full_amount_split")
      {
        $price=$jv_amount;
      }
      else
      {
        $price=($price*$jv_amount/100);
      }
      $display_name.=" Step 2 Payment ";
    }
    else
    {
      // else here will be the code for the new pay button
      // step 1 - checking for affiliate
      if ($aff_id!="")
      {
        $query="select membership_id, paypal_email from members where id='".$aff_id."'";
        $q->query($query);
        if ($q->nf()!=0)
        {
          if (get_setting("splitoption")==2)
          {
            $q->next_record();
            if ($q->f("paypal_email")=="")
            {
              $aff_flag=false;
            }
            else
            {
              $aff_flag=true;
            }
          }
          else
          {
            $q->next_record();
            $aff_membership_id=$q->f("membership_id");
            $aff_flag=true;

            if ((get_setting("accept_paypal") && $q->f("paypal_email")!="") )
            {
              if (get_setting("accept_paypal") && $q->f("paypal_email")!="")
                $aff_paypal_email=$q->f("paypal_email");
            }
            else
            {
              $aff_flag=false;
              $pay_paypal_t="";
            }
          }
        }
        else {$aff_flag=false;}
      }
      else {$aff_flag=false;}
      // end of searching for affiliate
      // splitting 2 cases : with affiliate and without affiliate ::
      if ($aff_flag)
      {
        $p=0;
        if (get_setting("splitoption")==2)
        {
          $p_paypal=get_setting("paypal_email");
          if (get_setting("accept_paypal") && trim($p_paypal)!="") FFileRead("templates/pay.paypal.html", $pay_paypal_t);
        }
        else
        {
          $query="select membership_id, paypal_email from members where id='".$aff_id."'";
          $q->query($query);
          if ($q->nf()!=0)
          {
            $q->next_record();
            $p_paypal=$q->f("paypal_email");

            if (get_setting("accept_paypal") && trim($p_paypal)!="") FFileRead("templates/pay.paypal.html", $pay_paypal_t);
          }
          else
          {
            if (get_setting("accept_paypal")) FFileRead("templates/pay.paypal.html", $pay_paypal_t);
          }
        }
        //case 1 with affiliate:
        $query="select * from products where id='$product_id'";
        $q->query($query);

        if ($q->nf()==0)
        {
          die("Fatal database logic error: The product you have on this page doesn't exist.");
        }
        //go for affiliate set
        $q->next_record();
        $product_id=$q->f("id");
        $price=$q->f("price");
        $display_name=$q->f("display_name");
        // loading settings to see if split or not
        if (get_setting("splitoption")==2){
          $query="select * from levels where product_id='$product_id' and membership_id='$aff_membership_id' and level=1 order by id desc limit 0,1";
        }else {
          $query="select * from levels where product_id='$product_id' and membership_id='$aff_membership_id' and level=1 order by id asc limit 0,1";
        }

        $q->query($query);

        if ($q->nf()!=0)
        {
          // affiliate identified OK , building pay forms
          $q->next_record();
          $flag_split=false;
          if ($q->f("paytype")=="percent_split" || $q->f("paytype")=="full_amount_split" )
          {
            if ($jv == 1){
              $jv_amount = $q->f("jv1");
            }elseif ($jv == 2){
              $jv_amount = $q->f("jv2");
            }else {
              if ($q->f("highcom")==1)
              {
                $b=time();
                $c=($aff_s_date+$q->f("highdays")*88400);
                if ($c > $b)
                {
                  $jv_amount=$q->f("highval");
                }
                else
                {
                  $jv_amount = $q->f("value");
                }
              }
              else
              {
                $jv_amount = $q->f("value");
              }
            }

            $flag_split=true;
            if ($q->f("paytype")=="full_amount_split")
            {
              $price=$jv_amount;
            }
            else
            {
              $price=($price*$jv_amount/100);
            }
            // aff emails become pay form emails, overwriting default setting
            if (get_setting("splitoption")==2)	{
              $paypal_email=get_setting("paypal_email");
            }
            else
            {
              $paypal_email=$aff_paypal_email;
            }
          }
        }
        else
        {
          //case 2 without affiliate:
          //we need to get the full product price here
          $p_paypal=get_setting("paypal_email");
          if (get_setting("accept_paypal") && trim($p_paypal)!="") FFileRead("templates/pay.paypal.html", $pay_paypal_t);
          $query="select * from products where id='$product_id'";
          $q->query($query);
          if ($q->nf()==0)
          {
            die("Fatal database logic error: No product exists. Please review your admin settings and correct this error... ");
          }
          $q->next_record();
          $product_id=$q->f("id");
          $price=$q->f("price");
          $display_name=$q->f("display_name");
        }
      }
      else
      {
        //case 2 without affiliate:
        //we need to get the full product price here
        $p_paypal=get_setting("paypal_email");
        if (get_setting("accept_paypal") && trim($p_paypal)!="") FFileRead("templates/pay.paypal.html", $pay_paypal_t);
        $query="select * from products where id='$product_id'";
        $q->query($query);
        if ($q->nf()==0)
        {
          die("Fatal database logic error: The product you have on this page doesn't exist. If this is the OTO page check if the product that you want to sell as OTO has the unique id OTO1(same for OTO2)<a href=\"member.area.profile.php\">Click here</a> to go to members area ");
        }
        $q->next_record();
        $product_id=$q->f("id");
        $price=$q->f("price");
        $display_name=$q->f("display_name");
      }
    }
    // converting number for 2 decimals : XXXXXX.XX
    $price=number_format($price,2,".", "");
    if ($prodpaypal!=0)
    {
      $pay_buttons=$pay_paypal_t."<br>";
      if ($flag_recurring==true)
      {
        FFileRead("templates/paypal.recurring.html", $recurring_template);
        $subscription=$price;
        $period=$recurring_period;
        $times=$recurring_times;
        $type=$recurring_type;
        $recurring_template=str_replace("{subscription}",$subscription, $recurring_template);
        $recurring_template=str_replace("{period}",$period, $recurring_template);
        $recurring_template=str_replace("{type}",$type, $recurring_template);
        if ($times != 1 ){
          $recurring_template=str_replace("{times}",$times, $recurring_template);
        }else {
          $recurring_template=str_replace("{times}","0", $recurring_template);
        }
        $pay_buttons=str_replace("{amount}","",$pay_buttons);
        $pay_buttons=str_replace("{subtype}","-subscriptions",$pay_buttons);
      }
      else
      {
        $pay_buttons=str_replace("{amount}",'<INPUT type=hidden value="{amount}" name=amount>',$pay_buttons);
        $pay_buttons=str_replace("{subtype}","",$pay_buttons);
      }
      if ($trial_flag){
        FFileRead("templates/paypal.recurring.trial.html", $trial);
        $trial=str_replace("{trial_amount}", $trial_amount, $trial);
        $trial=str_replace("{trial_period}", $trial_period, $trial);
        $trial=str_replace("{trial_period_type}", $trial_period_type, $trial);
        $trial_template1=str_replace("{trial}",$trial,$trial_template1);
      }else{
        $recurring_template1=str_replace("{trial}","",$recurring_template1);
      }
      $pay_buttons=str_replace("{recurring_paypal}", $recurring_template, $pay_buttons);
      $pay_buttons=str_replace("{trial}", $trial, $pay_buttons);
      if ($times==1){
        $pay_buttons=str_replace("{src}", "0", $pay_buttons);
      }else{
        $pay_buttons=str_replace("{src}", "1", $pay_buttons);
      }
      $pay_buttons=str_replace("{paypal_email}", $paypal_email, $pay_buttons);
      $pay_buttons=str_replace("{on_click_paypal}", $on_click_paypal, $pay_buttons);
      $pay_buttons=str_replace("{id}", $item_number, $pay_buttons);
      $pay_buttons=str_replace("{button_id}", $button_id, $pay_buttons);
      $pay_buttons=str_replace("{show_shipping_paypal}", $show_shipping_paypal, $pay_buttons);
      $pay_buttons=str_replace("{shipping_fee}", $shipping_fee, $pay_buttons);
      $pay_buttons=str_replace("{ask_shipping_adress}", $ask_shipping_adress, $pay_buttons);
      $pay_buttons=str_replace("{shiping_country_list_paypal}", "$shiping_country_list_paypal", $pay_buttons);
      $pay_buttons=str_replace("{country_code}", "$country_code", $pay_buttons);
      $pay_buttons=str_replace("{paypal_test}", "https://www.paypal.com/cgi-bin/webscr", $pay_buttons);
      $pay_buttons=str_replace("{paypal_return_url}", $return_url_paypal, $pay_buttons);
      $pay_buttons=str_replace("{mc_currency}", get_setting("paypal_currency"), $pay_buttons);

      $pay_buttons=str_replace("{pp_button}", $pp_button, $pay_buttons);
      $pay_buttons=str_replace("{paypal_notify_url}", $notify_url_paypal, $pay_buttons);
      $pay_buttons=str_replace("{cancel_return}", $cancel_return, $pay_buttons);
      $pay_buttons=str_replace("{name}", $display_name, $pay_buttons);
      $pay_buttons=str_replace("{amount}", $price, $pay_buttons);
      $pay_buttons=str_replace("{item_number}", $item_number, $pay_buttons);
      $pay_buttons=str_replace("{custom}", $custom, $pay_buttons);
    }
    else $pay_buttons='';
    $query="SELECT * FROM products WHERE id='$product_id'";
    $q->query($query);
    $q->next_record();
    if (get_setting('accept_clickbank') == 1 && $q->f('clickbank')){
      $query="select signup,nid_clickbank from products where id='".$product_id."'";
      $q2->query($query);
      $q2->next_record();
      $atsignup=$q2->f("signup");
      if (get_setting('cb_invisible')){

        $q3->query("SELECT clickbank_id FROM members WHERE id='".$_COOKIE['aff']."'");
        $q3->next_record();
        if ($_GET['hop']=='' && $q3->f('clickbank_id')) {
          $hoplink = "<iframe src='http://".$q3->f('clickbank_id').".".get_setting('vendor_id').".hop.clickbank.net/?pid=".$q2->f('nid_clickbank')."' style='display:none' id='cb'></iframe>";
          $hoplink = base64_encode($hoplink);	
          $pay_buttons .= "<script>document.write(Base64.decode('$hoplink'));</script>";
        }
        $pay_buttons .= "<img style='cursor:pointer;' src='$cb_button' onclick=\"window.location='http://www.clickbank.net/sell.cgi?link=".get_setting('vendor_id')."/".$q2->f('nid_clickbank')."/&seed=".md5(((get_setting("free_signup")!=1 && $atsignup!=0) ? $_COOKIE['PHPSESSID'] : (get_setting("enable_oto_paid_signup") == 1 && get_setting("free_signup")!=1) ? $_COOKIE['PHPSESSID'] : $member_id)."_$product_id")."'\"/>";
      }
      if (get_setting('cb_popup'))
        $pay_buttons .= "<img style='cursor:pointer;' src='$cb_button' onclick=\"window.open('clickbank_aff_popup.php?item=".$q2->f('nid_clickbank')."&time=".$cb_time."&seed=".md5(((get_setting("free_signup")!=1 && $atsignup!=0) ? $_COOKIE['PHPSESSID'] : (get_setting("enable_oto_paid_signup") == 1 && get_setting("free_signup")!=1) ? $_COOKIE['PHPSESSID'] : $member_id)."_$product_id")."','',' width=1px, height=1px, menubar=no, status=no');window.self.focus();\"/>";
    }
    $query="SELECT * FROM products WHERE id='$product_id'";
    $q->query($query);
    $q->next_record();
    if (get_setting('accept_2co') == 1 && $q->f('2co')){
      $query="select signup,nid_2co from products where id='".$product_id."'";
      $q2->query($query);
      $q2->next_record();
      $atsignup=$q2->f("signup");
      $pay_buttons .= "<br><br><br>
      <form action='https://www.2checkout.com/2co/buyer/purchase' method='post'>
      <input name='sid' value='".get_setting("sid")."' type='hidden'>".
      (get_setting('2co_demo') ? " <input name=\"demo\" value='Y' type='hidden'>": "").

      "<input name=\"quantity\" value='1' type='hidden'>
      <input name='custom' value='$custom' type='hidden'>
      <input name='seed' value='".md5(((get_setting("free_signup")!=1 && $atsignup!=0) ? $_COOKIE['PHPSESSID'] : (get_setting("enable_oto_paid_signup") == 1 && get_setting("free_signup")!=1) ? $_COOKIE['PHPSESSID'] : $member_id)."_$product_id")."' type='hidden'>
      <input name='product_id' value='".$q2->f("nid_2co")."' type='hidden'>
      <input type='image' src=\"$b2co_button\"/>
      </form>
      ";
    }
    $query="SELECT * FROM products WHERE id='$product_id'";
    $q->query($query);
    $q->next_record();
    if (get_setting('accept_auth')==1 && $q->f('auth'))
    {
      if (get_setting("use_aim")){
        if( $_SERVER['HTTPS']=='') header("location: https://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING']);
        if ( $q->f("signup")){
          FFileRead("templates/auth.aim.signup_button.html", $pay_authnet_t ) ;
          $pay_buttons .= $pay_authnet_t."<br>";
          $pay_buttons = str_replace("{auth_button}", $bauth_button, $pay_buttons);
          $pay_buttons = str_replace("{custom}", $session, $pay_buttons);
        }elseif (($q->f('nid') == 'OTO1' || $q->f('nid') == 'OTO2' || $q->f('nid') == 'OTO_BCK' || $q->f('nid') == 'OTO2_BCK') && get_setting('auth_one_click')){
          FFileRead("templates/pay.authnet_one_click.html", $pay_authnet_t ) ;
          $pay_buttons .= $pay_authnet_t."<br>";
          $pay_buttons = str_replace("{member_id}", $member_id, $pay_buttons);
          $pay_buttons = str_replace("{product_id}", $product_id, $pay_buttons);
          $pay_buttons = str_replace("{auth_button}", $bauth_button, $pay_buttons);
          $pay_buttons = str_replace("{ip_address}", $_SERVER['REMOTE_ADDR'], $pay_buttons);
          $pay_buttons = str_replace("{custom}", $custom, $pay_buttons);
          $pay_buttons = str_replace("{button_id}", $button_id+1, $pay_buttons);		
        }else{
          FFileRead("templates/pay.authnet_normal.html", $pay_authnet_t ) ;
          $pay_buttons .= $pay_authnet_t."<br>";
          $pay_buttons = str_replace("{member_id}", $member_id, $pay_buttons);
          $pay_buttons = str_replace("{product_id}", $product_id, $pay_buttons);
          $pay_buttons = str_replace("{auth_button}", $bauth_button, $pay_buttons);
          $pay_buttons = str_replace("{ip_address}", $_SERVER['REMOTE_ADDR'], $pay_buttons);
          $pay_buttons = str_replace("{custom}", $session, $pay_buttons);	
          $pay_buttons = str_replace("{button_id}", $button_id+1, $pay_buttons);		
        }
      }else{
        FFileRead("templates/pay.authnet.html", $pay_authnet_t ) ;
        $x_tran_key = get_setting('auth_key');
        $loginid = get_setting('auth_login');
        $pay_buttons .= $pay_authnet_t."<br>";
        $query = "select * from members where id='$member_id'" ;
        $q_memberinfo->query($query) ;
        $q_memberinfo->next_record();
        $anet_first_name = $q_memberinfo->f("first_name");
        $anet_last_name  = $q_memberinfo->f("last_name");
        $anet_memb_email = $q_memberinfo->f("email");
        $siteowner_email = get_setting("emailing_from_email") ;
        // emailing_from_email is field contents to find, in settings table, then get value
        $anet_tstamp = time();
        // Seed random number for security and better randomness.
        srand(time());
        $anet_sequence = rand(1, 1000);
        //	 Trim $ sign if it exists
        if (substr($price, 0,1) == "$")
        {
          $price = substr($price,1);
        }
        $anet_fingerprint = CalculateFP ($loginid, $x_tran_key, $price, $anet_sequence, $anet_tstamp, $currency = "") ;
        $anet_seqtxid = rand(1, 1000);
        $anet_txn_id = CalculateFP ($loginid, $x_tran_key, $price, $anet_seqtxid, $anet_tstamp, $currency = "") ;
        $pay_buttons = str_replace("{anet_login}", $loginid, $pay_buttons);
        $pay_buttons = str_replace("{on_click}", $on_click, $pay_buttons);
        $pay_buttons = str_replace("{anet_sequence}", $anet_sequence, $pay_buttons);
        $pay_buttons = str_replace("{anet_tstamp}", $anet_tstamp, $pay_buttons);
        $pay_buttons = str_replace("{anet_fingerprint}", $anet_fingerprint, $pay_buttons);
        $pay_buttons = str_replace("{url}", get_setting("site_full_url"), $pay_buttons);
        $pay_buttons = str_replace("{first_name}", $anet_first_name, $pay_buttons);
        $pay_buttons = str_replace("{last_name}", $anet_last_name, $pay_buttons);
        $pay_buttons = str_replace("{anet_x_email}", $anet_memb_email, $pay_buttons);
        $pay_buttons = str_replace("{anet_x_merchemail}", $siteowner_email, $pay_buttons);
        $pay_buttons = str_replace("{anet_txn_id}", $anet_txn_id, $pay_buttons);
        $pay_buttons = str_replace("{seed}", md5(((get_setting("free_signup")!=1 && $atsignup!=0) ? $custom : (get_setting("enable_oto_paid_signup") == 1 && get_setting("free_signup")!=1) ? $custom : $member_id)."_$product_id"), $pay_buttons);
        $pay_buttons = str_replace("{custom}", $custom, $pay_buttons);
        $pay_buttons = str_replace("{price}", $price, $pay_buttons);
        $pay_buttons = str_replace("{ip}", getenv('REMOTE_ADDR'), $pay_buttons);
        $pay_buttons = str_replace("{auth_button}", $bauth_button, $pay_buttons);
        if (get_setting('auth_test')) $pay_buttons = str_replace("{test}", '<input type="hidden" name="x_test_request" value="TRUE">', $pay_buttons);
        else $pay_buttons = str_replace("{test}", '', $pay_buttons);
        $pay_buttons = str_replace("{anet_amount}", '', $pay_buttons);
      }
    }
    return $pay_buttons;
  }
  function cbValid()
  {
    $key = get_setting('secret_key');
    $rcpt=$_REQUEST['cbreceipt'];
    $time=$_REQUEST['time'];
    $item=$_REQUEST['item'];
    $cbpop=$_REQUEST['cbpop'];
    $xxpop=sha1("$key|$rcpt|$time|$item");
    $xxpop=strtoupper(substr($xxpop,0,8));
    if ($cbpop==$xxpop) return 1;
    else return 0;
  }
  function process_sale($amount, $user_id, $product_id, $aff_flag, $product_id, $buyer_id, $s_session)
  {
    $q=new CDb;
    $q->query("SELECT trial_amount, trial FROM products WHERE id='$product_id'");
    $q->next_record();
    global $mc_amount1;
    if ($q->f('trial') && ($mc_amount1 == $q->f('trial_amount')) && $mc_amount1 == '0.00'){
    }else{
      if ($amount<=0) die ("amount $amount invalid");
    }
    if ($user_id<0) die ("user id $user_id invalid");
    //check if user exists in db
    if ($aff_flag==0)
    {
      $query="select id, aff from members where id='$user_id'";
      $q->query($query);
      if ($q->nf()==0)
      {
        //no user found for this tr. :: inserting a general transaction::
        $query="insert into a_tr (id, member_id, group_id, amount, status, comments, dt, product_id, buyer_id, session) values (NULL, 0, 0, '$amount', 2, 'No affiliate found for this transaction', NOW(), '$product_id', '$buyer_id', '$s_session')";
        $q->query($query); return;
      }
      else
      {
        $q->next_record();
        $aff_id=$q->f("aff");
      }
    }
    else $aff_id=$user_id;
    $query="select membership_id, jv from members where id='$aff_id'";
    $q->query($query);
    $q->next_record();
    $aff_membership_id=$q->f("membership_id");
    $jv=$q->f("jv");
    // assign a group id for the transaction ::
    $query="select group_id from a_tr order by group_id desc limit 0,1";
    $q->query($query);
    $q->next_record();
    $next_gr_id=$q->f("group_id")+1;
    //credit affiliates
    $query="select * from levels where product_id='$product_id'  order by level ASC";
    $q->query($query);
    $max_level=$q->nf();
    $q2=new Cdb;
    $level=1;
    $k=0;
    while($q->next_record())
    {
      if($q->f("membership_id")==$aff_membership_id && $q->f("level")==$level){
        $k++;
        $value=$q->f("value");
        if ($jv==1) $value=$q->f("jv1");
        if ($jv==2) $value=$q->f("jv2");
        if($q->f("highcom")==1){
          $q2->query("select s_date from members where id='$aff_id'");
          $q2->next_record();
          if($q2->f("s_date")>time()-$q->f("highdays")*24*3600)
            $value=$q->f("highval");	
        }
        if($amount==0) $value=0;
        if ($q->f("paytype")=="percent" || $q->f("paytype")=="full_amount")
        {
          if ($q->f("paytype")=="percent")
          {
            $am=($amount*$value/100);
          }
          else $am=$value;
          if($buyer_id>0){
            $q2->query("select first_name,last_name from members where id='$buyer_id'");
            $q2->next_record();
            $buyinfo="Buyer Id:".$buyer_id." Buyer name:".$q2->f("first_name")." ".$q2->f("last_name");
          }else $buyinfo='';
          $query="insert into a_tr (id, member_id, group_id, amount, status, comments, dt, product_id, buyer_id, session)
          values (NULL, '$aff_id', '$next_gr_id', '".$am."', 0, 'This sale is from level ".$q->f("level")." affiliate.".$buyinfo."', NOW(), '$product_id', '$buyer_id', '$s_session')";
          $q2->query($query);
        }
        else
        {
          $query="insert into a_tr (id, member_id, group_id, amount, status, comments, dt, product_id, buyer_id, session)
          values (NULL, 0, 0, '$amount', 2, 'This was a split, affiliate already paid directly', NOW(), '$product_id', '$buyer_id', '$s_session')";
          $q2->query($query);
        }

        $query="select paypal_email, aff, jv from members where id='$aff_id'";
        $q2->query($query);
        $q2->next_record();
        if ($q2->f("aff")==0) { return;}
        $aff_id=$q2->f("aff");
        $q4=new CDb;
        $query="select membership_id, jv,suspended from members where id='$aff_id'";
        $q4->query($query);
        $q4->next_record();
        $aff_membership_id=$q4->f("membership_id");
        $jv=$q4->f("jv");
        $suspended = $q4->f("suspended");
        $level=2;
        if ($k==$max_level) return;
      }
    }
  }
  function hmac ($key, $data)
  {
    return (bin2hex (mhash(MHASH_MD5, $data, $key)));
  }
  // Since the hmac function relies on PHP function that is not always available
  // we will do it here.
  function bbhmac ($key, $data)
  {
    // RFC 2104 HMAC implementation for php.
    // Creates an md5 HMAC.
    // Eliminates the need to install mhash to compute a HMAC
    // Hacked by Lance Rushing
    $b = 64; // byte length for md5
    if (strlen($key) > $b) {
      $key = pack("H*",md5($key));
    }
    $key  = str_pad($key, $b, chr(0x00));
    $ipad = str_pad('', $b, chr(0x36));
    $opad = str_pad('', $b, chr(0x5c));
    $k_ipad = $key ^ $ipad ;
    $k_opad = $key ^ $opad;
    return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
  }
  // Calculate and return fingerprint
  // Use when you need control on the HTML output
  function CalculateFP ($loginid, $x_tran_key, $amount, $sequence, $tstamp, $currency = "")
  {
    return (bbhmac ($x_tran_key, $loginid . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . $currency));
  }
  // Inserts the hidden variables in the HTML FORM required for SIM
  // Invokes hmac function to calculate fingerprint.
  function InsertFP ($loginid, $x_tran_key, $amount, $sequence, $currency = "")
  {
    $tstamp = time ();
    $fingerprint = bbhmac ($x_tran_key, $loginid . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . $currency);
    echo ('<input type="hidden" name="x_fp_sequence" value="' . $sequence . '">' );
    echo ('<input type="hidden" name="x_fp_timestamp" value="' . $tstamp . '">' );
    echo ('<input type="hidden" name="x_fp_hash" value="' . $fingerprint . '">' );
    return (0);
  }
