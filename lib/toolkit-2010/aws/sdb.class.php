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

// additional implementation notes
// http://developer.amazonwebservices.com/connect/entry.jspa?externalID=1292&ref=featured

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
    var $requestid;
    var $boxusage;
    var $tboxusage;
    var $queries = 0;
    var $nextToken = NULL;
    
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
       

        $result = $this->xmlrequest($params, false);
           
        $this->queries++;
        if (isset($result['ResponseMetadata'])) {
          $this->requestid = $result['ResponseMetadata']['RequestId'];
          $this->boxusage  = $result['ResponseMetadata']['BoxUsage'];
          $this->tboxusage += $this->boxusage;
          unset($result['ResponseMetadata']);
        }

        // print_r($result);
        return $result;
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
       
        $domains = $this->listDomains();
        if (in_array($in_name, $domains)) 
          return true; // nothing to do
 
        $xml = $this->request('CreateDomain', array('DomainName' => $in_name));
        
        $this->domain = $in_name;
        
        return true;
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
        return true;
    }
    
/**
* Get a list of domains
*
*/

    public function listDomains() {
 
      $domains = $this->_listDomains(100);
      while($this->nextToken) {
         $ndomains = $this->listDomains(100, true);
         if ($ndomains) $domains = array_merge($domains, $ndmomains);
      }
      return $domains;
    }
       

    public function _listDomains($in_limit = 100, $in_token = NULL)
    {
        if (!is_numeric($in_limit) or $in_limit > 100) {
            throw new Exception('invalid limit');
        }

        $params =  array('MaxNumberOfDomains' => $in_limit);
       
        if (!empty($in_token)) {
            if ($in_token === true) $in_token = $this->nextToken;
            if ($in_token == NULL) return NULL; // we're done
            $params['NextToken'] = $in_token;
        }
       
 
        $xml = $this->request('ListDomains', $params);
        $domains = $xml['ListDomainsResult']['DomainName'];
        if (! is_array($domains)) $domains = array($domains); // Normalize - always return an array
       
        return $domains;
    }

   
    
 
    /**
* Deletes data from an item
*
* [str] $in_domain - sdb domain item lives in
* [str] $in_item - name of item
*/
    public function getAttributes($in_item, $sep =',')
    {
        
        $xml = $this->request('GetAttributes',array('DomainName' => $this->domain,'ItemName' => $in_item));
        $r = $xml['GetAttributesResult']['Attribute'];

        $results = array();
        foreach ($r as $a) { 
          $n = $a['Name'];
          $v = $a['Value'];
        
          if (isset($results[$n])) {
            $results[$n] .= $sep . $v;
          } else {
            $results[$n] = $v;
          }
        }
        return $results;
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
           // Normalize
           if (!is_array($values)) {
             $values = array($values);
           }

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
    
    public function limit_query($in_query, $in_limit, $in_token = NULL)
    {
        if (!is_numeric($in_limit) or $in_limit > 250) {
            throw new Exception();
        }
        
        $params = array('DomainName' => $this->domain, 'MaxNumberOfItems' => $in_limit);
        
        if (!empty($in_query)) {
            $params['QueryExpression'] = $in_query;
        }
        
        if (!empty($in_token)) {
            if ($in_token === true) $in_token = $this->nextToken;
            if ($in_token == NULL) return NULL; // we're done
            $params['NextToken'] = $in_token;
        }


        $response = $this->request('Query', $params);

        if (isset( $response['QueryResult']['NextToken'])) {
           $this->nextToken = $response['QueryResult']['NextToken']; 
        } else {
           $this->nextToken = NULL;
        }
        if (isset(  $response['QueryResult']['ItemName'])) {
           $items = $response['QueryResult']['ItemName'];
        } else {
           return NULL;
        }
        
        // If we expect multiple itemes, make sure we are sending an array.
        // If we expect exactly 0 or 1 items, do NOT send an array
        if (! is_array($items) && $in_limit > 1) 
             $items = array($items); 
     
        return $items;
    }

    public function query($q = '') {
      $data = $this->limit_query($q, 250);
      while($this->nextToken) {
         $ndata = $this->limit_query($q, 250, true);
         if ($ndata) $data = array_merge($data, $ndata);
      }
      return $data;
    } 

}

