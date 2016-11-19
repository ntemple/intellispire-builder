<?php
/**
* Amazon PHP Classes and Functions
*
* @category   Amazon
* @package    AWS
* @author     Nick Temple <Nick.Temple@intellispire.com>
* @copyright  2002-2008 Intellispire
* @license    MIT: http://www.opensource.org/licenses/mit-license.php
* @version    SVN: $Id: sdb.class.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*
* Requires PHP 5+
*
* Based on:
* A PHP5 class for interfacing with the Amazon SimpleDB API
* Author: Alex Bosworth, License MIT
*/

/**
*
*/
require_once 'aws.class.php';

/**
* This class is the abstraction layer to SimpleDb
*
* Takes ($key, $secret, $url)
* - [str] $key: Your Amazon Web Services Access Key ID
* - [str] $secret: Your Amazon Web Services Secret Access Key
* - [str] $url: OPTIONAL (default: http://sdb.amazonaws.com/)
*
* Example Usage:
*
* <pre>
*   $sd = new SimpleDb("AWS_KEY", "AWS_SECRET");
*
*   $sd->listDomains();
*   $sd->query("domain1");
*
*   $sd->createDomain("domain1");
*   $sd->putAttributes('item1', array('name', array('value1', 'value2'));
*
*   $sd->getAttributes('item1');
*
*   $sd->deleteAttributes('item1', array('name', 'name2'));
*   $sd->deleteDomain('domain1');
* </pre>
*/
class SDB extends AWS {
    
    var $domain = '';
    
    /**
* Constructor
*
* Takes ($key, $secret, $url)
*
* - [str] $key: Your Amazon Web Services  "Access Key ID"
* - [str] $secret: Your Amazon Web Services  "Secret Access Key"
* - [str] $url: OPTIONAL: defaults: http://sdb.amazonaws.com/
*
* @ignore
*/
    public function __construct($key, $secret, $url = "https://sdb.amazonaws.com/")
    {
        $this->endpoint    = $url;
        parent::__construct($key, $secret);
    }
    
    function setDomain($domain)
    {
        $o = $this->domain;
        $this->domain = $domain;
        return $o;
    }
    
    function getDomain()
    {
        return $this->domain;
    }
    
    /**
* Makes a request to the SimpleDb Service
*
* Takes ($params)
*
* - [arr] $params: custom query params to send to sdb
*/
    private function request($action, $params)
    {
        $params['Action']   = $action;
        $params['Version']  = '2007-11-07';
        
        return $this->xmlrequest($params);
    }
    
    /**
* Create a new domain
*
* [str] $name
*/
    public function createDomain($in_name)
    {
        if (empty($in_name)) {
            throw new Exception('invalid name for a domain');
        }
        
        $xml = $this->request('CreateDomain', array('DomainName' => $in_name));
        
        $this->domain = $in_name;
        
        return $xml;
    }
    
    /**
* Create a new domain
*
* [str] $name
*/
    public function deleteDomain($in_name)
    {
        if (empty($in_name)) {
            throw new Exception('invalid name for a domain');
        }
        
        $xml = $this->request('DeleteDomain', array('DomainName' => $in_name));
        return $xml;
    }
    
    /**
* Get a list of domains
*
*/
    public function listDomains($in_limit = 100, $in_token = NULL)
    {
        if (!is_numeric($in_limit) or $in_limit > 100) {
            throw new Exception('invalid limit');
        }
        
        $xml = $this->request('ListDomains', array('MaxNumberOfDomains' => $in_limit));
        return $xml;
    }
    
    /**
* Deletes data from an item
*
* [str] $in_domain - sdb domain item lives in
* [str] $in_item - name of item
*/
    public function getAttributes($in_item)
    {
        
        $xml = $this->request('GetAttributes',array('DomainName' => $this->domain,'ItemName' => $in_item));
        return $xml;
    }
    
    
    /**
* Deletes data from an item
*
* [str] $in_domain - sdb domain item lives in
* [str] $in_item - name of item
* [array] $in_data - name value pairs
*/
    public function deleteAttributes($in_item, $in_data)
    {
        if (empty($in_item)) {
            throw new Exception();
        }
        
        $params = array('DomainName' => $this->domain, 'ItemName' => $in_item);
        
        $i = 0;
        
        foreach($in_data as $name => $value)
        {
            $params['Attribute.' . $i . '.Name'] = $name;
            $params['Attribute.' . $i . '.Value'] = $value;
            
            $i++;
        }
        
        $xml = $this->request('DeleteAttributes', $params);
        return $xml;
    }
    
    /**
* Put data into an item in a domain
*
* [str] $in_domain - the domain to which the item belongs
* [str] $in_item - the unique id of the item
* [str] $in_name - an array of name value pairs to
*/
    public function putAttributes($in_item, $in_data)
    {
        if (empty($in_item) or !count($in_data)) {
            throw new Exception();
        }
        
        $params = array('DomainName' => $this->domain, 'ItemName' => $in_item);
        
        $i = 0;
        foreach($in_data as $name => $values)
        {
            foreach($values as $value)
            {
                $params['Attribute.' . $i . '.Name'] = $name;
                $params['Attribute.' . $i . '.Value'] = $value;
                $params['Attribute.' . $i . '.Replace'] = 'true';
                
                $i++;
            }
        }
        
        if ($i > 100) {
            throw new Exception();
        }
        
        $xml = $this->request('PutAttributes', $params);
        
    }
    
    /**
* Query a domain to find item names
*
* [str] $in_domain - the domain to which the item belongs
* [str] $in_query - query expression
* [int] $in_limit - max number of items to get
* [str] $in_token - next list token
*/
    
    public function query($in_query = '', $in_limit = 250, $in_token = NULL)
    {
        if (!is_numeric($in_limit) or $in_limit > 250) {
            throw new Exception();
        }
        
        $params = array('DomainName' => $this->domain, 'MaxNumberOfItems' => $in_limit);
        
        if (!empty($in_query)) {
            $params['QueryExpression'] = $in_query;
        }
        
        return $this->request('Query', $params);
    }
}

