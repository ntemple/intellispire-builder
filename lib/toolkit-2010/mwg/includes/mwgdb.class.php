<?php
/**
* Sabrayla PHP Classes and Functions
*
* Copyright (c)2008 Intellispire and original author(s)
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
* @category   sabrayla
* @package    sabrayla
* @author     Nick Temple <Nick.Temple@intellispire.com>
* @copyright  2002-2010 Intellispire
* @version    SVN: $Id: mwgdb.class.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*/


/**
* MySQL Database class
*/


class MWGdb {

  var $db_rw;
  var $db_ro;
  var $database = '';
  var $host     = '';
  var $user     = '';
  var $password = '';
  var $prefix   = ''; // Table prefix, use #__ in queries (#__ removed if no prefix)
  var $halt     = false;
  var $rs       = NULL;
  var $queries  = 0;  // number of queries
  var $log = array();
  var $timer = false;

  var $errno   = 0;
  var $error  = '';


  /**
  * Insert
  */

  function insert($table, $data, $pkey = 'id') {

    $fields = array();
    $values = array();
    $replace = array();

    foreach ($data as $name => $value) {
      if ($name != $pkey && $name != 'ts' && strpos($name, '_') !== 0) {
        array_push($fields, "`$name`");
        array_push($values, $value);
        array_push($replace, '?');
      }
    }

    $fields = implode(',', $fields);
    $replace = implode(',', $replace);

    $sql = "insert into `#__$table` ($fields) values ($replace)";
    $this->query_rw($sql, $values);

    return mysql_insert_id();
  }

  /**
  * Update
  *
  * id is the auto_inc field in the table, and must be present
  * ts is the timestamp in the table.  If present, and additional
  * check is performed. If the ts is out of date, nothing happens
  * the pattern protects from stale data
  */

  function update($table, $data, $pkey = 'id') {

    $id = 0;
    if (isset($data[$pkey])) {
      $id = $data[$pkey] + 0;
    } 

    // Make sure this is an Update, otherwise do an Insert
    if ($id == 0) {
      return $this->insert($table, $data, $pkey); 
    }    

    $values = array();

    $sql = "update `#__$table` set ";
    foreach ($data as $name => $value) {
      if ($name != $pkey && $name != 'ts') {
        $sql .= "`$name`=?,";
        array_push($values, $value);
      }
    }
    MWGdb::pchop($sql);  # remove last comma

    $sql .= " where $pkey=?";
    array_push($values, $id);

    // Freshness check
    if (! empty($data['ts']) ) {
      $sql .= " and ts=?";
      array_push($values, $data['ts']);
    }
                                             
    $this->query_rw($sql, $values);
    return $id;
  }

  /**
  * Store data in a table
  *
  * The intent is to be able to store data directly from a POST
  *
  * we assume that the data is for this table, we do strip out all
  * hidden params (those starting with underscores)
  *
  * Turns any array into a CSV list
  *
  * @param string $table  the table name to store
  * @param array  $data   assoc array of data
  * @param bool   $verify if true, we'll check the params against the schema (slow)
  * @param string $pkey   primary key of this table
  */
  function before_store($table, $data, $verify = true, $pkey = 'id') {
    static $cols = array();
    $params = array();

    // Gurantee that we only have valid fields
    if ($verify) { // @todo test to see if cacheing worthwhile
      if (! isset($cols[$table])) {
        $cols[$table] = $this->get_results('show columns from #__' . $table);
      }
      $fields = $cols[$table];
      
      foreach ($fields as $field) {
        $field = $field['Field'];
        if(isset($data[$field])) $params[$field] = $data[$field];
      }
      $data = $params; // Re-inject data
    }

    foreach ($data as $name=>$value) {
      if (is_array($value) )
        $value = serialize($value);

      if (strpos($name, '_') !== 0 ) {
        $params[$name] = $value;
      }
    }
    return $data;
  } 
  
  function store($table, $data, $verify = true, $pkey = 'id') {
    $params = $this->before_store($table, $data, $verify, $pkey);
    return $this->update($table, $params, $pkey);
  }
  
   

  /**
  * Execute a raw SQL file
  *
  * Used for creating tables
  * ';' is the delimiter that cannot be escaped
  */
  function execute_sql($sql) {
    $sql_array = explode(';',$sql);
    foreach ($sql_array as $sql) {
      $this->query_rw($sql);
    }
    return true;
  }


  /**
  * Connect to a database
  *
  * You must create a r/w data conncetion.
  *
  * If called a second time, and readwrite is false, then a readonly connection
  * is setup to be used with replicated DB's.
  *
  * If no params are given, they are taken from the object. This is done for
  * compatibility with older programs that don't explicity call connect()
  * TODO: refactor to multiple conect methods
  */
  function _connect($host = null, $database = null, $user = null, $password = null, $pconnect= false, $newlink = false) {

    /* Handle defaults */
    if ($database) $this->database = $database;
    if ($host)     $this->host     = $host;
    if ($user)     $this->user     = $user;
    if ($password) $this->password = $password; 

    /* establish connection, select database */
    if ($pconnect) {
      $handle = @mysql_pconnect($this->host, $this->user, $this->password, $newlink);
    } else{
      $handle = @mysql_connect($this->host, $this->user, $this->password, $newlink);
    }

    if (!$handle) return $this->halt("connection to $database failed.", -1);
    if (!@mysql_select_db($this->database,$handle)) return $this->halt("cannot select database $database", -1);

    return $handle;
  }

  function connect($host = null, $database = null, $user = null, $password = null, $pconnect= false, $newlink = false) {
    $handle = $this->_connect($host, $database, $user, $password, $pconnect, $newlink);
    $this->setDBH($handle);   
    return $handle;    
  }

  function clusterConnect($host = null, $database = null, $user = null, $password = null, $pconnect= false, $newlink = false) {
    $handle = $this->_connect($host, $database, $user, $password, $pconnect, $newlink);
    $this->setClusterDBH($handle);
    return $handle;        
  }

  function setDBH($dbh) {
    $this->db_rw = $dbh;
    $this->db_ro = $dbh;
  }

  function setClusterDBH($dbh) {
    $this->db_ro = $dbh;
  }


  /**
  *  Prepare a select statement using ?
  * 
  *  Can be a list of values, or an array
  */
  function prepare() {
    $args  = func_get_args();

    // Allow _just_ an array to be passed, for easier integration
    if (count($args) == 1 && is_array($args[0]) ) {
      $args = $args[0];
    }

    return  call_user_func_array(array('MWGdb', '_db_prepare'), $args);
  }

  /*
  * (pretend to) discard the query result
  *
  * There are many cases when the db class is no
  * longer needed, however the returned result set is
  * still in use.  To REALLY free the result set, send
  * call free(true);
  *
  */

  function free($force = false) {
    if ($this->rs && $force) {
      @mysql_free_result($this->rs);
    }
    $this->rs = 0;
  }

  /**
  *
  * resolve_query assumes that the $_qs has already been setup
  */
  function _resolve_query($link) {

    // PHP4 chokes on empty queries
    if (trim($this->_qs) == "") return 0;

    // Connect if needed
    if (!$this->connect()) return 0;


    # Handle prefix
    $this->_qs = str_replace('#__', '' . $this->prefix, $this->_qs);

    $this->writelog($this->_qs);

    if ($this->rs) $this->free();    
  
    if ($this->timer) {
      $this->queries++;
      $log['start'] = microtime(true);
      $log['qs'] = $this->_qs;
    }
    
    $this->rs = mysql_query($this->_qs, $link);
    
    if ($this->timer) {
      $log['end'] = microtime(true);  
      $log['qtime'] = $log['end'] - $log['start'];
      $this->timer += $log['qtime'];
      $this->log[] = $log;
    }
    

    $this->row   = 0;
    if (!$this->rs) return $this->halt("ERR:" . $this->_qs);    
    return $this->rs;
  }

  /**
  *
  * Query
  * Extended to allow prepare:
  *
  * $db->query('query string') or ...
  * $db->query->('query string ? ?', $r1, r2); or ...
  * $db->query->('query_string ? ?', $r[]);
  *
  * TODO: determine dynamically whether we can use the RO dataset.
  */

  function query() {
    $args  = func_get_args();
    if (count($args) == 1 && is_array($args[0]) ) $args = $args[0];
    $this->_qs = call_user_func_array(array('MWGdb', '_db_prepare'), $args);

    return $this->_resolve_query($this->db_rw);
  }

  /**
  * force read_only link
  */
  function query_ro() {
    $args  = func_get_args();
    if (count($args) == 1 && is_array($args[0]) ) $args = $args[0];
    $this->_qs = call_user_func_array(array('MWGdb', '_db_prepare'), $args);

    return $this->_resolve_query($this->db_ro);
  }

  /**
  * force read_write link
  */
  function query_rw() {
    $args  = func_get_args();
    if (count($args) == 1 && is_array($args[0]) ) $args = $args[0];
    $this->_qs = call_user_func_array(array('MWGdb', '_db_prepare'), $args);

    return $this->_resolve_query($this->db_rw);
  }


  /**
  * return one value from the query
  */

  function get_value() {
    $rs = $this->query_ro(func_get_args());
    if (!$rs) return false;
    $row = mysql_fetch_row($rs);
    $this->free();
    return $row[0];
  }

  /**
  * return data formatted for Flexy select()
  *
  * example: get_select('select id, name from options');
  */
  function get_select() {
    $rs = $this->query_ro(func_get_args());

    $data = array();
    while($row = @mysql_fetch_row($rs) ) {
      $id   = $row[0];
      $name = $row[1];
      $data[$id] = $name;
    }
    $this->free();
    return $data;
  }

  /**
  * Slurp everything into an associative array
  */
  function get_results() {
    $rs = $this->query_ro(func_get_args());
    $data = array();
    if (!$rs) return NULL;

    while($row = mysql_fetch_assoc($rs) )
      $data[] = $row;
    $this->free();
    return $data;
  }

  /**
  * return result as a single associative array
  */
  function get_row(){
    $rs = $this->query_ro(func_get_args());
    $row = mysql_fetch_assoc($rs);
    $this->free();
    return $row;
  }

  /**
  * return result of a column
  *
  */
  function get_column() {
    $rs = $this->query_ro(func_get_args());

    $data = array();

    if (!$rs) return $data;

    while($row = mysql_fetch_row($rs) ) {
      $data[] = $row[0];
    }
    $this->free();
    return $data;
  }

  /**
  *count result set
  */
  function countresult(){
    $args  = func_get_args();
    if (count($args) == 1 && is_array($args[0]) ) $args = $args[0];
    $this->_qs = call_user_func_array(array('MWGdb', '_db_prepare'), $args);

    return mysql_num_rows($this->_resolve_query($this->db_rw));
  }


  function affected_rows() {
    return mysql_affected_rows($this->db_rw);
  }

  function insert_id() {
    return mysql_insert_id($this->db_rw);
  }



  /**
  * Logging - does nothing by default
  */

  function writelog($msg, $level=0) {
    #    print "$msg\n";
  }

  /**
  * Error handling meant to be overridden
  * in child class
  */

  function halt($msg, $errno = 0 ) {

    if ($errno) {
      $this->errno  = $errno;
      $this->error  = $msg;
    } 

    $this->_errno  = mysql_errno();
    $this->_error  = mysql_error();
    $backtrace = debug_backtrace();

    #    ob_start();
    #    print_r($backtrace);
    #    $this->_trace = ob_get_clean(); 

    $error_msg = "Database Error: $msg\n". $this->error . "(" . $this->errno . ")\n";

    if ($this->halt) {
      //      print_r($this);
      die('Session Halted.');
    }
  }

  function thread_id() {
    return mysql_thread_id ( $this->db_rw );
  }

  function ping() {
    return mysql_ping($this->db_rw);
    /*  	if ($this->db_rw != $this->db_ro) {
    if (! mysql_ping($this->db_ro)) $this->db_ro = $this->db_rw;
    }
    */
  }


  /**
  * List the tables from the current selected database
  */
  function tables() {
    return $this->get_column('show tables');
  }

  /**
  * get the columns from the specified table
  */
  function columns($table, $full = false) {
    $fields = $this->get_results("show columns from $table");
    if ($full) return $fields;

    $result = array();
    foreach ($fields as $field) {
      $result[] = $field['Field'];
    }
    return($result);
  }

  /**
  * Concert MySQL datetime to a UNIX timestamp
  *
  * which can then be used with the
  * XMLRPC_convert_timestamp_to_iso8601($timestamp) function.
  *
  * It comes from: http://www.zend.com/codex.php?id=176&single=1
  */

  static function datetime_to_timestamp($dt) {
    $yr=strval(substr($dt,0,4));
    $mo=strval(substr($dt,5,2));
    $da=strval(substr($dt,8,2));
    $hr=strval(substr($dt,11,2));
    $mi=strval(substr($dt,14,2));
    $se=strval(substr($dt,17,2));
    return mktime($hr,$mi,$se,$mo,$da,$yr);
  }

  /**
  * Convert a timestamp into a date-time string
  * in Y-m-d H:i:s format
  *
  * @access public
  * @param timestamp timestamp the timestamp to convert
  * @return string
  */
  static function timestamp_to_datetime($timestamp='') {
    if (! $timestamp) $timestamp = time();
    $date = date('Y-m-d H:i:s', $timestamp);
    return $date;
  }

  static function pchop(&$string)
  {
    if (is_array($string)) {
      foreach($string as $i => $val)
      {
        $endchar = self::pchop($string[$i]);
      }
    } else {
      $endchar = substr("$string", strlen("$string") - 1, 1);
      $string = substr("$string", 0, -1);
    }
    return $endchar;
  }


  /**
  * string _db_make_qw($query, $arg1, $arg2, ...)
  *
  * @access private
  */
  static function _db_make_qw() {
    $args = func_get_args();
    $tmpl =& $args[0];
    $tmpl = str_replace("%", "%%", $tmpl);
    $tmpl = str_replace("?", "%s", $tmpl);
    foreach ($args as $i=>$v) {
      if (!$i) continue;
      if (is_int($v)) continue;
      $args[$i] = "'".mysql_real_escape_string($v)."'";
    }
    for ($i=$c=count($args)-1; $i<$c+20; $i++)
      $args[$i+1] = "UNKNOWN_PLACEHOLDER_$i";
    return call_user_func_array("sprintf", $args);
  }


  /**
  * Prepare a query
  *
  */
  static function _db_prepare() {
    $args = func_get_args();

    if (count($args) == 1) {
      // only a query, no substition expected nor required
      return $args[0];
    }

    // We need to substitute
    if (is_array($args[1])) {
      // The last argument is an array of replacement values
      $template = array_shift($args);  // get the template
      $args = array_shift($args);      // get the actual replacement values
      array_unshift($args, $template); // put the template on top
    }
    $query = call_user_func_array(array('MWGdb', '_db_make_qw'), $args);
    return $query;
  }  
}


class MWGdbx extends MWGdb {
  
  /**
  * Store data in a table
  * 
  * tags is a special fields that holds a serialized array that is preserved
  * (merged) with existing data
  *
  * @param string $table  the table name to store
  * @param array  $data   assoc array of data
  * @param bool   $verify if true, we'll check the params against the schema (slow)
  * @param string $pkey   primary key of this table
  */
  function store($table, $data, $verify = true, $pkey = 'id') {
    $data = $this->before_store($table, $data, $verify, $pkey);
        
    $id = 0;
    if (isset($data[$pkey])) {
      $id = $data[$pkey] + 0;
    } 

    if ($id != 0) {
      $c = $this->get_row("select id, tags from `#__$table` where $pkey=?", $id);
      if (!$c) return $this->insert($table, $data, $pkey);     

      if (isset($data['tags']) && isset($c['tags'])) {
        // We need to merge the tags
        $tags = unserialize($c['tags']);
        foreach ($data['tags'] as $n => $v) {
          $tags[$n] = $v;
        }
        $data['tags'] = $tags;        
      }         
    }
    
    if (isset($data['tags'])) {
      $data['tags'] = serialize($data['tags']);
    }

    return $this->update($table, $data, $pkey);
       
  }

}

