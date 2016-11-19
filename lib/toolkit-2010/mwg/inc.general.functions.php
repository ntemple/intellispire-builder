<?php
  /**
  * @version    $Id: inc.general.functions.php 21 2013-03-15 19:35:01Z ntemple $
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


  function get_setting($setting_name, $default = null)
  {
    return MWG::getInstance()->get_setting($setting_name, $default);
//    
//    $db = MWG::getDb();
//    $q=new Cdb;
//    
//    /* First check mwg settings */
//    $query="select value from mwg_setting where name='$setting_name'";
//    $q->query($query);
//    if ($q->nf() !=0)  {
//      $q->next_record();
//      return $value;
//    }

//    /* not found, search legacy settings */
//    $query="select value from settings where name='$setting_name'";
//    $q->query($query);
//    if ($q->nf()==0) return $default; // cannot find the setting in table

//    $q->next_record();

//    $value = stripslashes($q->f('value')); // Why is stripslashes needed?
//    return $value;
  }

  function get_signup_setting($setting_name)
  {
    $q=new Cdb;
    $query="select value from signup_settings where name='$setting_name'";
    $q->query($query);
    if ($q->nf()==0) return -1; // cannot find the setting in table

    $q->next_record();

    return stripslashes($q->f("value")); // return setting value
  }
  function set_setting($setting_name, $setting_value)
  {
    $q=new Cdb;
    $setting_value=addslashes(stripslashes($setting_value));
    $query="update settings set value='$setting_value' where name='$setting_name'";
    $q->query($query);
  }
  // end of functions for setting table

  /***********************8
  * Menu generation code
  */
  
  /**
  * Given a category, and optionally a membership level, generate the list of menu_items 
  * that has access
  * 
  * @todo optionally return all possible links, for a teaser method
  * 
  * @param mixed $category
  * @param mixed $membership_id
  * @return array
  */
  function generate_main_menu_list($category = 'main', $membership_id = null) {
    $mwg = MWG::getInstance();
    $db = $mwg->getDb();

    $all_items = $db->get_results('select * from menus where menus.menu_category=? and menus.active=1 order by menus.position asc, menus.id asc', $category);
    if (!$membership_id) return $all_items;

    $items = array();
    foreach ($all_items as $item) {
      $perms = $db->get_value('select count(*) from menu_permissions where menu_item=?', $item['id']);
      if ($perms == 0) { // if we don't restrict permissions, then it's wide open
        $items[] = $item;        
      } else {
        // Does this item have permission?
        $perms = $db->get_value('select count(*) from menu_permissions where menu_item=? and membership_id=?', $item['id'], $membership_id);
        if ($perms) {
          $items[] = $item; // We have permission
        }
      }
    }
    return $items;
  }

  /**
  * Generate the front-end menu
  * 
  */
  function generate_main_menu()
  {
    $items = generate_main_menu_list('main');
    return _render_menu($items);
  }

  /**
  * Genereate a logged-in menu
  * 
  * @param mixed $membership_id
  * @return string
  */
  function generate_members_menu($membership_id)
  {
    $items = generate_main_menu_list('members', $membership_id);
    return _render_menu($items);
  }
  
  /**
  * Defaut rendering for the standard template
  * 
  * @param mixed $items
  * @return string
  */
  
  function _render_menu($items) {
    if (get_setting("verticalmenumain")==1)
      $sep ="<br>\n<br>\n";
    else
      $sep =" | ";

    $menu_links = array();
    foreach ($items as $item) {
      array_push($menu_links, _render_link($item));     
    }

    return implode($sep, $menu_links);

  }

  /**
  * Render the link to an item based on options
  * @todo add nofollow links
  * 
  * @param mixed $item
  */
  function _render_link($item) {
    $target = '';
    if ($item['open_new_window'] == 1) {
      $target = "target='_blank'";
    }            
    return "<a href='{$item['link']}' $target>{$item['name']}</a>";
  }

  /**********************************
  * Error functions ..............
  * 
  */
  function error_halt($error_str, $t)
  {
    $t->set_file("content", "error.html");
    $t->set_var("sitename", SITENAME);
    $t->set_var("details", $error_str);

    if (DEBUG_TYPE=="browser" || DEBUG_TYPE=="be")
    {
      $t->pparse("out", "content");
    }
    else
      if (DEBUG_TYPE=="email" || DEBUG_TYPE=="be")
      {
        mwg_mail(EM_SEND_DB_ERR, SITENAME." Mysql Error",  $error_str, "From: ".SITENAME."<noreply@noreply.com>");
      }
      die("<br>Script execution halted.");
  }
  //end of error functions
  //functions for generating random strings/ session
  function GetRandomString($length) {
    settype($template, "string");

    // you could repeat the alphabet to get more randomness
    $template = "1234567890abcdefghijklmnopqrstuvwxyz";
    $length2=$length-22;
    settype($length, "integer");
    settype($rndstring, "string");
    settype($a, "integer");
    settype($b, "integer");

    for ($a = 0; $a <= $length2; $a++) {
      $b = rand(0, strlen($template) - 1);
      $rndstring .= $template[$b];
    }
    $rndstring=md5(microtime()).$rndstring;

    return $rndstring;

  }
  function new_sess_id()
  {
    return GetRandomString(64);
  }
  //end of functions for generating random strings /sessions
  // functions to replace [firstname], ... in strings
  function email_replace($str, $email, $firstname, $lastname, $password)
  {
    $str=str_replace("[firstname]", $firstname, $str);
    $str=str_replace("[lastname]", $lastname, $str);
    $str=str_replace("[password]", $password, $str);
    $str=str_replace("[email]", $email, $str);
    $str=str_replace("[sitename]", SITENAME, $str);

    return $str;
  }
  // end of functions to replace [firstname], ... in strings
  function email_replace2($str, $member_id)
  {
    $q=new Cdb;
    $q2=new Cdb;
    $query="select * from members where id='$member_id'";
    $q->query($query);
    $q->next_record();
    $query="select * from tags";
    $q2->query($query);
    while ($q2->next_record())
    {
      $str=str_replace("{".$q2->f("title")."}", $q->f($q2->f("field")), $str);
    }

    return $str;
  }
  // functions for member area
  function get_logged_info()
  {
    global $t,$q, $sess_id;
    $qu=new Cdb;

    if (!isset($sess_id)) 
    {
      // there is no member logged so we die here...
      $t->set_file("content","member.area.error.html");
      $t->set_var("querystr", urlencode($_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING']));
      include("inc.bottom.php");

      die();
    }
    $query="select * from members where '$sess_id'=mdid and mdid!=''";
    $q->query($query);
    if ($q->nf()==0)
    {
      //ooops... we did not find the registered user, hack attempt ? 
      @session_destroy(); // we destroy the bad session here...

      $t->set_file("content","member.area.error.html"); // and we display session expired page... 
      include("inc.bottom.php");

      die();
    }

    // ok last we pos at the registered member
    $q->next_record();
    $member_id_gt=$q->f("id");
    $member_membership_id_gt=$q->f("membership_id");
    updateHistory($member_id_gt, $member_membership_id_gt, true);
    updateHistory($member_id_gt, get_setting("default_free"), true);	
  }
  //end of functions for member area
  // function to determine how many unread messages are in the inbox
  function get_unread_inbox()
  {
    global $sess_id;
    $q=new Cdb;
    $query="select count(*) as n from messages where '$sess_id'=MD5(CONCAT('".get_setting("secret_string")."',member_id)) and read_flag=0";
    $q->query($query);
    if ($q->nf()==0)
    {
      //ooops... we did not find the registered user, hack attempt ? 
      session_destroy(); // we destroy the bad session here...

      $t->set_file("content","member.area.error.html"); // and we display session expired page... 
      include("inc.bottom.php");

      die();
    }

    // ok last we pos at the registered member
    $q->next_record();
    return $q->f("n");
  }
  //end of  function to determine how many unread messages are in the inbox
  // function to generate affiliate id
  function get_aff_link($member_id) 
  {
    if (get_setting("choose_aff") == 1){
      $aff_var = get_setting("old_aff");
    }elseif (get_setting("choose_aff") == 2){
      $aff_var = get_setting("affiliate_variable");
    }
    $aff_link=get_setting("site_full_url")."?".$aff_var."=".$member_id;
    return $aff_link;
  }
  //end of function to generate affiliate id
  function encodeHTML($sHTML)
  {
    $sHTML=ereg_replace("&","&amp;",$sHTML);
    $sHTML=ereg_replace("<","&lt;",$sHTML);
    $sHTML=ereg_replace(">","&gt;",$sHTML);
    return $sHTML;
  }
  function FFileRead($name/*filename*/, &$contents/*returned contents of file*/)
  {
    $fd = fopen ($name, "r");
    $contents = fread ($fd, filesize ($name));
    fclose ($fd);
  }
  function FFileWrite($name, $content, $w="w+")
  {
    $filename = $name;
    $somecontent = $content;

    // Let's make sure the file exists and is writable first.
    if (is_writable($filename)) {

      // In our example we're opening $filename in append mode.
      // The file pointer is at the bottom of the file hence
      // that's where $somecontent will go when we fwrite() it.
      if (!$handle = fopen($filename, $w)) {
        echo "Cannot open file ($filename)";
        exit;
      }

      // Write $somecontent to our opened file.
      if (fwrite($handle, $somecontent) === FALSE) {
        echo "Cannot write to file ($filename) please make sure that you chmod 777 templates folder";
        exit;
      }


      fclose($handle);

    } else {
      echo "The file $filename is not writable please make sure that you chmod 777 templates folder";
    }
  }
  function FFileWriteNew($name, $content, $w="w+")
  {
    $filename = $name;
    $somecontent = $content;

    // Let's make sure the file exists and is writable first.
    if (!$handle = fopen($filename, $w)) {
      echo "Cannot open file ($filename)";
      exit;
    }

    // Write $somecontent to our opened file.
    if (fwrite($handle, $somecontent) === FALSE) {
      echo "Cannot write to file ($filename) please make sure that you chmod 777 templates folder";
      exit;
    }


    fclose($handle);

  }
  // function to replace tags in $t
  function replace_tags_t($user_id, &$t)
  {
    $q=new Cdb;
    $q2=new Cdb;

    $qu=new Cdb;

    $query="select * from members where id='$user_id'";
    $q->query($query);
    $q->next_record();
    $member_membership_id=$q->f('membership_id');
    $upgrades_kit='{membershipn} <a href="member.area.sl.php?id={id}">See Details...</a><br>';
    $member_history = explode(",", $q->f("history"));
    foreach ($member_history as $value) {
      $query = "SELECT rank FROM membership WHERE id='".$value."'";
      $qu->query($query);
      $qu->next_record();
      $membership_history[] = $qu->f("rank");
    }
    $show_item = array();
    foreach ($membership_history as $value) {
      if ($value != "") {
        $show_item[] = $value;
      }
    }
    $show_item = array_unique($show_item);
    $query = "select * from membership where id='".$member_membership_id."' order by rank DESC";
    $qu->query($query);
    $qu->next_record();
    $membership_kit_replace.=$qu->f("name");
    $query = "select * from membership where active=1 order by rank DESC";
    $qu->query($query);
    $upgrades_kit_replace='<table>';
    while ($qu->next_record()) {
      if (strpos($qu->f("shown_to"), "|".$member_membership_id."|") !== false) {
        $upgrades_kit_replace.=str_replace('{membershipn}',$qu->f("name"), '<tr><td>{membershipn}</td><td> <a href="member.area.sl.php?id=');
        $upgrades_kit_replace.=str_replace('{id}',$qu->f("id"), '{id}">See Details...</a></td></tr>');
      }
    }
    $upgrades_kit_replace.='</table>';
    $t->set_var('upgrades', $upgrades_kit_replace);

    $t->set_var('membership', $membership_kit_replace);

    $query="select * from tags";
    $q->query($query);
    $query="select * from members where id='$user_id'";
    $q2->query($query);
    if ($q2->nf()==0) return false;// no user found
    $q2->next_record();
    while ($q->next_record())
    {

      $t->set_var($q->f("title"), $q2->f($q->f("field")));
    }
    $b=get_aff_link($user_id);
    $t->set_var("aff_link", $b);
    return true;
  }
  //end of function to replace tags in $t
  //Function replace tags in messages
  function ReplaceTags($stringtoreplace, $member_id, &$replacedstring)
  {
    $q=new CDB;
    $q2=new CDB;

    $query="select * from members where id='$member_id'";
    $q->query($query);
    $q->next_record();
    $query="select * from tags";
    $q2->query($query);
    $replacedstring=$stringtoreplace;
    while ($q2->next_record())
    {
      $replacedstring=str_replace("[[".$q2->f("title")."]]", $q->f($q2->f("field")), $replacedstring);
    }
    $b=get_aff_link($member_id);
    $replacedstring=str_replace("[[aff_link]]", $b, $replacedstring);
  }
  // end of function
  //Function that returns tag list in a string
  function GetTags(&$tags,  $separator="{}")
  {
    $q=new CDB;
    $query="select title from tags";
    $q->query($query);
    $i=1;
    if ($separator=="{}") $tags="{aff_link} ";
    if ($separator=="[[]]") $tags="[[aff_link]] ";
    while ($q->next_record())
    {
      if ($i==1) 
      {
        if ($separator=="{}") $tags.="{".$q->f("title")."}";
        else $tags.="[[".$q->f("title")."]]";
        $i=2;
      }
      else if ($separator=="{}") $tags.=" {".$q->f("title")."}";
        else $tags.=" [[".$q->f("title")."]]";
    }

  }
  //end of function
  function execute_sql($filename, $autoincrementvalue)
  {
    $q=new Cdb;
    FFileRead($filename, $content);
    $a=explode(";", $content);
    $k=0;
    while ($a[$k]!="")
    {
      $query=$a[$k];
      $query=str_replace("{autoincrementstartfrom}",$autoincrementvalue, $query);
      $q->query($query);
      $k++;
    }
  }
  function GetPayButtonsList(&$t)
  {
    $t->set_file("productlist", "admin.membership.insert.pay.buttons.html");
    $q=new CDb;
    $query="select * from products";
    $q->query($query);
    while ($q->next_record())
    {
      $t->set_var("product_unique", $q->f("nid"));
      $t->set_var("product_name", $q->f("display_name"));
      $t->set_var("product_price", $q->f("price"));
      $t->parse("product_list", "productlist", true);
    }
    if ($q->nf()==0) $t->set_var("product_list", "");
    return true;
  }
  //function to get db fields in different formats
  function getdbfields($returntype="", $itemsperrow)
  {
    global $selectfield;
    global $search;
    global $order;
    $q=new Cdb;
    $a=$q->metadata("members", false);
    $a[]=array('table' => 'members', 'name' => 'affiliate_name', 'type' => 'int', 'len' => '10', 'flags' => 'not_null primary_key auto_increment');
    $j=0;
    $k=0;
    foreach ($a as $b)
    {

      $i=0;
      foreach ($b as $c)
      {
        if ($i==1) 
        {
          if ($returntype=="check")
          {
            if ($k%$itemsperrow==0) 
            {
              $return.="<tr>
              ";
              $j=$k;
            }
            $k++;
            $return.='<td><input type="checkbox" name="selectfield[]" value="'.$c.'" '.(in_array($c, $selectfield)? "checked" : "").'>'.$c.'</td>
            ';
            $m=0;
            if ($k%$itemsperrow==0 && $k!=$j)
            {
              $return.="</tr>
              "; $m=1;
            }
          }
          if ($returntype=="select")
          {
            $m=3;
            $return.='<option value="'.$c.'"'.($c == $search ? "selected" : "").'>'.$c.'</option>';
          }
          if ($returntype=="select_x")
          {
            $return.='<option value="'.$c.'"'.($c == $search ? "selected" : "").'>'.$c.'</option>';
          }
          if ($returntype=="select_s")
          {
            $m=3;
            $return.='<option value="'.$c.'"'.($c == $order ? "selected" : "").'>'.$c.'</option>';
          }
          break;
        }
        else $i++;
      }

    }
    if ($m==0) $return.='</tr>';
    return $return;
  }
  //end of function that returns db fields

  function getdbfields2($returntype="", $itemsperrow)
  {
    global $selectfield;
    $q=new Cdb;$q2=new Cdb;
    $a=$q->metadata("members", false);
    $a[]=array('table' => 'members', 'name' => 'affiliate_name', 'type' => 'int', 'len' => '10', 'flags' => 'not_null primary_key auto_increment');
    $j=0;
    $k=0;
    $na=array();
    $number=count($a);
    if (($number%$itemsperrow)!=0) {
      $rows=floor($number/$itemsperrow)+1;
    } else {
      $rows=floor($number/$itemsperrow);
    }
    $cols=$itemsperrow;
    $col=array();
    $col_num=1;
    foreach ($a as $ke=>$va)
    {
      $col[$col_num][]=$ke;
      if (count($col[$col_num])==($rows)) {
        $col_num++;
      }
    }
    foreach ($col[1] as $key => $value) {
      $i=1;
      while ($i<=$itemsperrow) {
        if ($a[$col[$i][$key]]) {
          $na[]=$a[$col[$i][$key]];
        } else {
          $na[]=array('table' => 'members', 'name' => '', 'type' => 'int', 'len' => '10', 'flags' => 'not_null primary_key auto_increment');
        }
        $i++;
      }
    }
    $a=array();
    $a=$na;

    foreach ($a as $b)
    {
      $i=0;
      foreach ($b as $c)
      {
        if ($i==1) 
        {
          if ($returntype=="check")
          {
            if ($k%$itemsperrow==0) 
            {
              $return.="<tr>
              ";
              $j=$k;
            }
            $k++;
            if ($c) {
              $query="SELECT description FROM signup_settings where field='$c'";
              $q2->query($query);
              $q2->next_record();
              $return.='<td><input type="checkbox" name="selectfield[]" value="'.$c.'" '.(in_array($c, $selectfield)? "checked" : "").'><span alt="'.$q2->f('description').'" title="'.$q2->f('description').'">'.$c.'</span></td>';
            }
            if (!$c) $return.='<td>'.$c.'</td>';
            $m=0;
            if ($k%$itemsperrow==0 && $k!=$j)
            {
              $return.="</tr>
              "; $m=1;
            }
          }
          if ($returntype=="select")
          {
            $m=3;
            if ($c) $return.='<option value="'.$c.'">'.$c.'</option>';
          }
          if ($returntype=="select_x")
          {
            if ($c) $return.='<option value="'.$c.'"'.($c == $itemsperrow ? "selected" : "").'>'.$c.'</option>';
          }
          break;
        }
        else $i++;
      }

    }
    if ($m==0) $return.='</tr>';
    return $return;
  }
  //end of function that returns db fields

  function replace_buttons($template, $member_id, &$t)
  {
    $q2=new Cdb;
    $query="select aff from members where id='$member_id'";
    $q2->query($query);
    $q2->next_record();
    if ($q2->nf()!=0)
      $aff_id=$q2->f("aff");
    else 
      $aff_id=$_COOKIE["aff"];
    $query="select * from products";
    $q2->query($query);
    while ($q2->next_record())
    {
      if ( strpos($template, "{".$q2->f("nid")."}") === false)
      { }
      else
      {
        $product_id=$q2->f("id"); 
        echo $q2->f("display_name"); 
        $session=new_sess_id(); 
        $step2=0; 

      }
    }
    $t->set_var("content", $template);
    return true;
  }
  function send_vars($id){
    $q = new CDb();
    $q2 = new CDb();
    $q->query("SELECT * FROM autoresponder_config");

    $data = "";

    while ($q->next_record()){
      if ($q->f("field") == "url"){
        $url = $q->f("value");
      }
      if ($q->f("id") == "3"){
        $data .= $q->f("field")."=".$_POST['first_name']."&"; 
      }
      if ($q->f("id") == "4"){
        $data .= $q->f("field")."=".$_POST['email']."&";
      }
      if ($q->f("field") == "method"){
        if ($q->f("value") == 1)
          $method = "GET";
        else 
          $method = "POST";
      }

    }

    $q->query("SELECT * FROM autoresponder_config WHERE field!='url' AND id!='3' AND id!='4' AND field!='method'");
    $i = 0;
    while ($q->next_record()){
      $i++;
      if ($q->f("field") == "field$i"){
        if ($q->f("value")==""){
          $q->next_record();
        }else{
          $data .= $q->f("value")."=";
          $q->next_record();
          $data .= urlencode($q->f("value"))."&";
        }
      }
    }
    $data = substr($data,0,-1);

    $parsed_url = parse_url("http://".$url);

    sendToHost($parsed_url['host'],$method,$parsed_url['path'],$data);
  }	

  function sendToHost($host,$method,$path,$data,$useragent=0)
  {
    // Supply a default method of GET if the one passed was empty
    if (empty($method)) {
      $method = 'GET';
    }
    $method = strtoupper($method);
    $fp = fsockopen($host, 80);
    if ($method == 'GET') {
      $path .= '?' . $data;
    }

    fputs($fp, "$method $path HTTP/1.1\r\n");

    fputs($fp, "Host: $host\r\n");
    fputs($fp,"Content-type: application/x-www-form-urlencoded\r\n");
    fputs($fp, "Content-length: " . strlen($data) . "\r\n");
    if ($useragent) {
      fputs($fp, "User-Agent: MSIE\r\n");
    }
    fputs($fp, "Connection: close\r\n\r\n");
    if ($method == 'POST') {
      fputs($fp, $data);
    }
    fclose($fp);
  }

  function updateHistory($member_h_id, $membership_h_id, $append = false) {
    $q4=new Cdb;
    if ($append) {
      $query="SELECT * FROM members WHERE id='$member_h_id'";
      $q4->query($query);
      $q4->next_record();
      $history=$q4->f('history');
      $history.=$membership_h_id.",";

      $history_explode=explode(",",$history);

      $history_explode=array_unique($history_explode);

      $ihi=0;
      $history="";
      while ($ihi<count($history_explode)) {
        if ($history_explode[$ihi]!="") {
          $history.=$history_explode[$ihi].",";
        }
        $ihi++;
      }
      $query="UPDATE members SET history="."'$history"."' WHERE id='".$member_h_id."'";
      $q4->query($query);			
    } else {
      $query="SELECT * FROM members WHERE id='$member_h_id'";
      $q4->query($query);
      $q4->next_record();
      $history=$q4->f('history');
      $history_exp=explode(",", $history);
      $ihi=0;
      $history="";
      while ($ihi<count($history_exp)) {
        if ($history_exp[$ihi]!=$membership_h_id && $history_exp[$ihi]!="") {
          $history.=$history_exp[$ihi].",";
        }
        $ihi++;
      }
      $query="UPDATE members SET history="."'$history"."' WHERE id='".$member_h_id."'";
      $q4->query($query);			
    }
  }
  
  function getAffiliate() {
    $req = MWG::getRequest();
    
    $affiliate_var = get_setting("affiliate_variable");
    $affiliate_var2 = get_setting("old_aff");
    $ar_host = parse_url(get_setting("site_full_url"));
    $host = $ar_host["host"];
    $path = $ar_host["path"];
    $host = str_replace("www", "", $host);
    $aff = $req->get($affiliate_var) ? $req->get($affiliate_var) : $req->get($affiliate_var2);
    if ($aff) {
        setcookie("aff", $aff, time()+9999999, $path, $host);
    }
    return $aff;
  }
  
  
