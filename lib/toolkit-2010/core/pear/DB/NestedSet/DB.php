<?php 

// +----------------------------------------------------------------------+
// | PEAR :: DB_NestedSet_DB                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Daniel Khan <dk@webcluster.at>                              |
// +----------------------------------------------------------------------+

// $Id: DB.php 21 2013-03-15 19:35:01Z ntemple $

require_once 'DB.php';
// {{{ DB_NestedSet_DB:: class
/**
 * Wrapper class for PEAR::DB
 * 
 * @author Daniel Khan <dk@webcluster.at> 
 * @package DB_NestedSet
 * @version $Revision: 21 $
 * @access public 
 */
// }}}
class DB_NestedSet_DB extends DB_NestedSet {
    // {{{ properties
    /**
     * 
     * @var object Db object
     */
    var $db; 
    // }}}
    // {{{ constructor
    /**
     * Constructor
     * 
     * @param mixed $dsn DSN as PEAR dsn URI or dsn Array
     * @param array $params Database column fields which should be returned
     */
    function & DB_NestedSet_DB($dsn, $params = array()) {
        $this->_debugMessage('DB_NestedSet_DB($dsn, $params = array())');
        $this->DB_NestedSet($params);
        $this->db = & $this->_db_Connect($dsn);
        $this->db->setFetchMode(DB_FETCHMODE_ASSOC);
    } 
    // }}}
    // {{{ destructor
    /**
     * Destructor
     */
    function _DB_NestedSet_DB() {
        $this->_debugMessage('_DB_NestedSet_DB()');
        $this->_DB_NestedSet();
        $this->_db_Disconnect();
    } 
    // }}}
    // {{{ _db_Connect()
    /**
     * Connects to the db
     * 
     * @return object DB The database object
     * @access private 
     */
    function & _db_Connect($dsn) {
        $this->_debugMessage('_db_Connect($dsn)');
        if (DB::isConnection($this->db)) {
            return $this->db;
        } 
        if (DB::isConnection($dsn)) {
            return $dsn;
        } 
        $db = & DB::connect($dsn);
        $this->_testFatalAbort($db, __FILE__, __LINE__);
        return $db;
    } 
    // }}}
    // {{{ _numRows()
    function _numRows($res) {
        return $res->numRows();
    } 
    // }}}
    // {{{ _isDBError()
    function _isDBError($err) {
        if (!DB::isError($err)) {
            return false;
        } 
        return true;
    } 
    // }}}
    // {{{ _quote()
    function _quote($str) {
        if (method_exists($this->db, 'quoteSmart')) {
            return $this->db->quoteSmart($str);
        } 
        return $this->db->quote($str);
    } 
    // }}}
    // {{{ _quoteIdentifier()
    function _quoteIdentifier($str) {
        if (method_exists($this->db, 'quoteIdentifier')) {
            return $this->db->quoteIdentifier($str);
        } 
        return $this->_quote($str);
    } 
    // }}}
    // {{{ _dropSequence()
    function _dropSequence($sequence) {
        return $this->db->dropSequence($this->sequence_table);
    } 
    // }}}
    // {{{ _nextId()
    function _nextId($sequence) {
        return $this->db->nextId($sequence);
    } 
    // }}}
    // {{{ _getAll()
    function _getAll($sql) {
        return $this->db->getAll($sql, null, DB_FETCHMODE_ASSOC);
    } 
    // }}}
    // {{{ _db_Disconnect()
    /**
     * Disconnects from db
     * 
     * @return void 
     * @access private 
     */
    function _db_Disconnect() {
        $this->_debugMessage('_db_Disconnect()');
        if (is_object($this->db)) {
            @$this->db->disconnect();
        } 

        return true;
    } 
    // }}}
} 

?>
