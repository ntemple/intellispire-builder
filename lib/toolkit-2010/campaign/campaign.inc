<?php
  require_once('OrthoganalArray.inc');

  # Campaign Settings (should be an ini!)
  define('COOKIE', 'MTEX_SESSION');
  define('DOMAIN', '.marketing-technology-exchange.com'); 

  function _value($var, $default = 0) {
    $value = $default;

    if (key_exists($var, $_GET) ) {
      $value = $_GET[$var];
    }
    if (! is_numeric($value) ) {
      $value = $default;
    }
    return $value;
  }


  class Campaign {
    var $CAM_ID;
    var $OATYPE;
    var $USECOOKIE;
    var $DIR;

   function init($CAM_ID, $OATYPE, $DIR= 'levels', $USECOOKIE = 1) {
     $this->CAM_ID = $CAM_ID;
     $this->OATYPE = $OATYPE;
     $this->DIR = $DIR;
     $this->USECOOKIE = $USECOOKIE;

     $this->getSession();
   }

   function track ($action = 0, $track1 = 0) {
       if ($track1 == 0) $track1 = _value('a');
     
       mysql_qw('insert into test_hits (cam_id, session_id, test_id, action, track1) values (?,?,?,?,?)',
        $this->CAM_ID,
        $this->session['id'],
        $this->testcaseid,
        $action,
        $track1);
    }


  function getSession() {
    global $OA;

    $session = array();
    if ( key_exists(COOKIE, $_COOKIE) ) {
      $rs = mysql_qw('select session from test_session where cookie=? limit 1',  $_COOKIE[COOKIE]);
      $session = mysql_fetch_value($rs);
      $session = unserialize($session);
    }

    if ( ! ($session['id'] > 0 ) ) { 
      // Create a new session
      $COOKIEID = md5(time() . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . rand() . (float) microtime()*1000000 );
      setcookie(COOKIE, $COOKIEID, time()+3600*24*360*10, '/', DOMAIN); // Approx 10 years

      mysql_qw("insert into test_session (cam_id, cookie, session) values (?,?,?)",
        $this->CAM_ID,
        $COOKIEID,
        serialize($session)
      );
      $session['id'] = mysql_insert_id();
    }

    if ( ($session[$this->CAM_ID] > 0) && ($this->USECOOKIE > 0) ) {
      $testcaseid = $session[$this->CAM_ID];
    } else {
      // We need to create a testcase
    
      # Determine a testcase for this session
      $alltests = $OA[$this->OATYPE];

      $testcaseid = rand(1, count($alltests));
      $session[$this->CAM_ID] = $testcaseid;

      mysql_qw('update test_session set session=? where id=?', serialize($session), $session['id']);
    }

    # Pull up the testcase and store it in a global
    $this->testcaseid = $testcaseid;
    $this->testcase = $OA[$this->OATYPE][$testcaseid -1]; # In reality #' 0 to N-1
    $this->session = $session;
    return $session;

  }

  # Warning! this is raw PHP.  We need to "fix" Flexy to be more flexible
  # in including dynamic content
  function getContent($param, $part = 0) {
    $level =  $this->testcase[$param];
    $level = $level-1; // adjust for 0 based array
    include($this->DIR . "/$param.$level.$part.html");
  }

}

?>
