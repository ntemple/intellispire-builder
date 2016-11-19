<?php

// Make sure image is sent to the browser immediately
ob_implicit_flush(TRUE);

// keep running after browser closes connection
@ignore_user_abort(true);

define('CP-P3P', "P3P: CP=\"CAO PSA OUR\"");
//define('CP-P3P', 'P3P: CP="OTI DSP COR ADM DEV TAI PSA PSD IVA IVD CON HIS OUR PUBi IND UNI"');
define('EMAIL_OPEN', 'eopen');
define('PAGE_VIEW',  'pview');
define('PAGE_CLICK', 'pclick');
define('SUBSCRIBE',  'sub');
define('SALE',       'sale');

// Make sure image is sent to the browser immediately
ob_implicit_flush(TRUE);

// keep running after browser closes connection
@ignore_user_abort(true);

class tracker {


  function __construct($path = '') {
    $track  = array();
    if ($path) {
      $this->path   = explode('/', $path); 
      $this->action = array_pop($this->path);
    }

    // Get standard stuff, may be overwritten later
    $track['action'] = $this->action;
    $track['uts']    = time();
    $track['gid']    = $this->getid('ev_gsession');
    $track['lid']    = $this->getid('ev_lsession');
    $track['a']      = $_REQUEST['a'];
    $track['b']      = $_REQUEST['b'];
    $track['c']      = $_REQUEST['c'];
    $track['d']      = $_REQUEST['d'];
    $track['e']      = $_REQUEST['e'];
    $track['REMOTE_ADDR']     = $_SERVER['REMOTE_ADDR'];
    $track['HTTP_REFERER']    = $_SERVER['HTTP_REFERER'];
    $track['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
    
    $this->track = $track;

  }

  // Client functions

  function getImageTrackerURL() {
    // NLT TODO WARNING HARDCODED DATA
    $img = 'http://www.intellispire.com/arp3/images/'  . urlencode(microtime()) .'/'. $this->track['lid'] . '/pixel.gif';
    return $img;
  }

  function getImageTracker() {
    $image = $this->getImageTrackerURL();
    return "<img src='$image' height='1' width='1'>";
  }

  // Persistant session stuff -- TODO, integrate
 
  function session_store($array, $cookiename = "FENGSHUI", $key = 'my secret key') {

    if (! $array['store']) return false;

    $cookiename = md5($cookiename);
    $data = serialize($array);
    $data = mcrypt_ecb (MCRYPT_3DES, $key, $data, MCRYPT_ENCRYPT);
    $data = base64_encode($data);  
    $flag = 1;
    $data = $flag . ':' . $data;
    setcookie($cookiename, $data, time()+3600 * 24 * 365 * 10);
    return true;
  }


  function session_get($cookiename = "FENGSHUI", $key = 'my secret key', $clear = false) {

    // decode tracker
    $cookiename = md5($cookiename);

    if (! $clear) {
      $data = $_COOKIE[$cookiename];
    }

    if ($data == '') {
      $data = array(); // Begin with some good data
      $data['sessionid'] = uniqid(md5(rand()), true); 
    } else {
      list($flag, $data) = explode(':', $data);
      if ($flag == 1) {
        $data =  mcrypt_ecb (MCRYPT_3DES, $key, $data, MCRYPT_DECRYPT);
      }
      $data = base64_decode($data);
      $data = unserialize($data);
    }

    // No changes
    $data['store'] = false;

    // Set affiliate data
    if ($_GET['ref'] != '') {
      $this->track['ref'] = $_GET['ref'];
      $data['ref'] = $_GET['ref'];
      $data['store'] = true;
    }
     return $data;
  }



  // *********************************************************************
  // Server Methods

  function dispatch() {

     switch($this->action) {
       case 'signature.gif': $this->track_eopen(); break;
       case 'pixel.gif':     $this->track_pview(); break;
       default: 
          header("HTTP/1.0 404 Not Found");
          print "<HTML><HEAD><TITLE>404 Not Found</TITLE></HEAD><BODY>
                 <H1>Not Found</H1>
                 The requested URL was not found on this server.<P>
                 </BODY></HTML>";
     }
  }

  function track_pview() {
     $this->sendGIF();
     $this->track['action'] = 'pview';
     $this->track['lid'] = array_pop($this->path);
     $this->store();
  }

  function track_eopen() {
    $this->sendGIF();

    $track = array();
    $this->track['action'] = 'eopen';
    $subject       = array_pop($this->path);
    $arp3_msg_id   = array_pop($this->path);
 
    // Decode message subject as far as we can
    if ($subject)  $subject  = urldecode($subject);
    if ($subject)  $subject1 = base64_decode($subject);
    if ($subject1) $subject = subject1;

    $this->track['a']      = $subject; // Subject of message
    $this->track['b']      = $arp3_msg_id; // message_id, which relates to campaign_id (user) in arp3

    $this->store();

#    print_r($track);
  }


  function getid($cookie = 'ev_lsession') {
    if (isset($_COOKIE[$cookie])) {
     $value =  $_COOKIE[$cookie];
    } else {
      $value = uniqid(md5(rand()), true);
    }

    // Refresh cookie 
    $domain = $_SERVER['HTTP_HOST'];
    $domain = explode('.', $domain);
    $tld = array_pop($domain);
    $name = array_pop($domain);
    $domain = '.' . $name . '.' . $tld; 
    setcookie($cookie, $value, time() + 365 * 24 * 60 * 60, '/', $domain); // Set for a year
    return $value;
  }


  function store() {
    require_once('/usr/local/share/phplib/lib/mysql.class.php');
    $db = new mysql_database();
    $db->connect('localhost', 'intellis_arp3', 'intellis_intelli', 'db_intellispire');
    $db->store('arpx_track', $this->track);
#    print_r($db); 
  }
    

  function sendGIF(){
    $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
    header(CP-P3P);
    header('Content-Type: image/gif');
    header('Content-Length: '.strlen($img));
    header('Connection: Close');
    print $img;
    // Browser should drop connection after this
    // Thinks it's got the whole image
  }


}

