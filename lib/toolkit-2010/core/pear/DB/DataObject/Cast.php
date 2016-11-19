<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors:  Alan Knowles <alan@akbkhome.com>                           |
// +----------------------------------------------------------------------+
//
// $Id: Cast.php 21 2013-03-15 19:35:01Z ntemple $
//
//  Prototype Castable Object.. for DataObject queries
//

/**
*  
* @abstract Storage for Data that may be cast into a variety of formats.
* 
* Common usages:
*   // blobs
*   $data = DB_DataObject_Cast::blob($somefile);
*   $data = DB_DataObject_Cast::string($somefile);
*   $dataObject->someblobfield = $data
*
*   // dates?
*   $d1 = new DB_DataObject_Cast::date('12/12/2000');
*   $d2 = new DB_DataObject_Cast::date(2000,12,30);
*   $d3 = new DB_DataObject_Cast::date($d1->year, $d1->month+30, $d1->day+30);
*   
*   // time, datetime.. ?????????
*
*   // raw sql????
*    $data = DB_DataObject_Cast::sql('cast("123123",datetime)');
*    $data = DB_DataObject_Cast::sql('NULL');
*
*   // int's/string etc. are proably pretty pointless..!!!!
*
*   
*   inside DB_DataObject, 
*   if (is_a($v,'db_dataobject_class')) {
*           $value .= $v->toString(DB_DATAOBJECT_INT,'mysql');
*   }
*
*
*
*
* @version    $Id: Cast.php 21 2013-03-15 19:35:01Z ntemple $
*/ 
class DB_DataObject_Cast {
        
    /**
    * Type of data Stored in the object..
    *
    * @var string       (date|blob|.....?)
    * @access public        
    */
    var $type;
        
    /**
    * Data For date representation
    *
    * @var int  day/month/year
    * @access public
    */
    var $day;
    var $month;
    var $year;

    
    /**
    * Generic Data..
    *
    * @var string
    * @access public
    */

    var $value;



    /**
    * Blob consructor
    *
    * create a Cast object from some raw data.. (binary)
    * 
    * 
    * @param   string (with binary data!)
    *
    * @return   object DB_DataObject_Cast
    * @access   public 
    */
  
    function blob($value) {
        $r = new DB_DataObject_Cast;
        $r->type = __FUNCTION__;
        $r->value = $value;
        return $r;
    }


    /**
    * String consructor (actually use if for ints and everything else!!!
    *
    * create a Cast object from some string (not binary)
    * 
    * 
    * @param   string (with binary data!)
    *
    * @return   object DB_DataObject_Cast
    * @access   public 
    */
  
    function string($value) {
        $r = new DB_DataObject_Cast;
        $r->type = __FUNCTION__;
        $r->value = $value;
        return $r;
    }
    
    /**
    * SQL constructor (for raw SQL insert)
    *
    * create a Cast object from some sql
    * 
    * @param   string (with binary data!)
    *
    * @return   object DB_DataObject_Cast
    * @access   public 
    */
  
    function sql($value) {
        $r = new DB_DataObject_Cast;
        $r->type = __FUNCTION__;
        $r->value = $value;
        return $r;
    }


    /**
    * Date Constructor
    *
    * create a Cast object from some string (not binary)
    * 
    * 
    * @param   vargs... accepts
    *       dd/mm
    *       dd/mm/yyyy
    *       yyyy-mm
    *       yyyy-mm-dd
    *       array(yyyy,dd)
    *       array(yyyy,dd,mm)
    *
    *
    *
    * @return   object DB_DataObject_Cast
    * @access   public 
    */
  
    function date() {  
        $args = func_get_args();
        switch(count($args)) {
            case 0: // no args = today!
               $bits =  explode('-',date('Y-m-d'));
                break;
            case 1: // one arg = a string 
            
                if (strpos($args[0],'/') !== false) {
                    $bits = array_reverse(explode('/',$args[0]));
                } else {
                    $bits = explode('-',$args[0]);
                }
            default: // 2 or more..
                $bits = $args;
        }
        if (count($bits) == 1) { // if YYYY set day = 1st..
            $bits[] = 1;
        }
        
        if (count($bits) == 2) { // if YYYY-DD set day = 1st..
            $bits[] = 1;
        }
        
        // if year < 1970 we cant use system tools to check it...
        // so we make a few best gueses....
        // basically do date calculations for the year 2000!!!
        // fix me if anyone has more time...
        if (($bits[0] < 1975) || ($bits[0] > 2030)) {
            $oldyear = $bits[0];
            $bits = explode('-',date('Y-m-d',mktime(1,1,1,$bits[1],$bits[2],2000)));
            $bits[0] = ($bits[0] - 2000) + $oldyear;
        } else {
            // now mktime
            $bits = explode('-',date('Y-m-d',mktime(1,1,1,$bits[1],$bits[2],$bits[0])));
        }
        $r = new DB_DataObject_Cast;
        $r->type = __FUNCTION__;
        list($r->year,$r->month,$r->day) = $bits;
        return $r;
    }
    
    /**
    * get the string to use in the SQL statement for this...
    *
    * 
    * @param   int      $to Type (DB_DATAOBJECT_*
    * @param   string  $db    (eg. mysql|mssql.....)
    * 
    *
    * @return   string 
    * @access   public
    */
  
    function toString($to=false,$db='mysql') {
        // if $this->type is not set, we are in serious trouble!!!!
        // values for to:
        $method = 'toStringFrom'.$this->type;
        return $this->$method($to,$db);
    }
    
    /**
    * get the string to use in the SQL statement from a blob of binary data 
    *   ** Suppots only blob->postgres::bytea
    *
    * @param   int      $to Type (DB_DATAOBJECT_*
    * @param   string  $db    (eg. mysql|mssql.....)
    * 
    *
    * @return   string 
    * @access   public
    */
    function toStringFromBlob($to,$db) {
        // first weed out invalid casts..
        // in blobs can only be cast to blobs.!
        
        // perhaps we should support TEXT fields???
        
        if (!($to & DB_DATAOBJECT_BLOB)) {
            return PEAR::raiseError('Invalid Cast from a DB_DataObject_Cast::blob to something other than a blob!');
        }
        
        switch ($db) {
            case 'pgsql':
                return "'".pg_escape_bytea($this->value)."'::bytea";
            
            default:
                return PEAR::raiseError("DB_DataObject_Cast cant handle blobs for Database:$db Yet");
        }
    
    }
    
    /**
    * get the string to use in the SQL statement for a blob from a string!
    *   ** Suppots only string->postgres::bytea
    * 
    *
    * @param   int      $to Type (DB_DATAOBJECT_*
    * @param   string  $db    (eg. mysql|mssql.....)
    * 
    *
    * @return   string 
    * @access   public
    */
    function toStringFromString($to,$db) {
        // first weed out invalid casts..
        // in blobs can only be cast to blobs.!
        
        // perhaps we should support TEXT fields???
        // 
        
        if (!($to & DB_DATAOBJECT_BLOB)) {
            return PEAR::raiseError('Invalid Cast from a DB_DataObject_Cast::string to something other than a blob!'.
                ' (why not just use native features)');
        }
        
        switch ($db) {
            case 'pgsql':
                return "'".pg_escape_string($this->value)."'::bytea";
            
            default:
                return PEAR::raiseError("DB_DataObject_Cast cant handle blobs for Database:$db Yet");
        }
    
    }
    
    
    /**
    * get the string to use in the SQL statement for a date
    *   
    * 
    *
    * @param   int      $to Type (DB_DATAOBJECT_*
    * @param   string  $db    (eg. mysql|mssql.....)
    * 
    *
    * @return   string 
    * @access   public
    */
    function toStringFromDate($to,$db) {
        // first weed out invalid casts..
        // in blobs can only be cast to blobs.!
         // perhaps we should support TEXT fields???
        // 
        
        if (($to !== false) && !($to & DB_DATAOBJECT_DATE)) {
            return PEAR::raiseError('Invalid Cast from a DB_DataObject_Cast::string to something other than a date!'.
                ' (why not just use native features)');
        }
        return "'{$this->year}-{$this->month}-{$this->day}'";
    }
    
   
    
    /**
    * get the string to use in the SQL statement for a raw sql statement.
    *
    * @param   int      $to Type (DB_DATAOBJECT_*
    * @param   string  $db    (eg. mysql|mssql.....)
    * 
    *
    * @return   string 
    * @access   public
    */
    function toStringFromSql($to,$db) {
        return $this->value; 
    }
    
    
    
    
}

?>