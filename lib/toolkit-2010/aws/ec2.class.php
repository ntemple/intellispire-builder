<?php
/**
* Amazon PHP Classes and Functions
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
* @category   Amazon
* @package    AWS
* @author     Nick Temple <Nick.Temple@intellispire.com>
* @copyright  2002-2008 Intellispire
* @license    MIT: http://www.opensource.org/licenses/mit-license.php
* @version    SVN: $Id: ec2.class.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*
* Requires PHP 5+
*
* Original code from: http://code.google.com/p/php-aws/
*
*/


// http://code.google.com/p/php-aws/
require_once('aws.class.php');

class EC2 extends AWS
{
    var $endpoint     = "http://ec2.amazonaws.com";
    var $_date       = null;
    var $_error      = null;
    
    function EC2($key = null, $secret = null)
    {
        parent::__construct($key, $secret);
        return true;
    }
    
    function getImages($ownerId = null)
    {
        $params = array("Action" => "DescribeImages");
        if (isset($ownerId)) {
            $params['Owner.1'] = $ownerId;
        }
        $xml = $this->sendRequest($params);
        
        $images = array();
        foreach($xml->imagesSet->item as $item)
        $images[(string) $item->imageId] =
        array("location" => (string) $item->imageLocation,
        "state"    => (string) $item->imageState,
        "owner"    => (string) $item->imageOwnerId,
        "public"   => (string) $item->isPublic);
        return $images;
    }
    
    function getInstances()
    {
        $params = array("Action" => "DescribeInstances");
        $xml = $this->sendRequest($params);
        
        $instances = array();
        foreach($xml->reservationSet->item as $item)
        $instances[(string) $item->instancesSet->item->instanceId] =
        array("imageId" => (string) $item->instancesSet->item->imageId,
        "state"   => (string) $item->instancesSet->item->instanceState->name,
        "dns"     => (string) $item->instancesSet->item->dnsName);
        return $instances;
    }
    
    function runInstances($imageId, $min = 1, $max = 1, $keyName = "gsg-keypair", $securityGroup = NULL, 
                          $InstanceType='m1.small', 
                          $zone = NULL,
                          $userData = NULL
                          )
    {
        $params = array("Action" => "RunInstances",
        "ImageId" => $imageId,
        "MinCount" => $min,
        "MaxCount" => $max,
        "KeyName" => $keyName,
        "InstanceType" => $InstanceType);

        if (! is_null($securityGroup)) {
          if (!is_array($securityGroup)) {
           $securityGroup = explode(",", $securityGroup);
          }
          $i = 1;
          foreach ($securityGroup as $g) {
            $params["SecurityGroup.$i"] = $g;
            $i++;
          }
        } 
           
        if (!is_null($userData)) {
          $params['UserData'] = base64_encode($userData);
        }

        if (!is_null($zone)) {
          $params['Placement.AvailabilityZone'] = $zone;
        }
        
        $xml = $this->sendRequest($params);
        
        $instances = array();
        foreach($xml->instancesSet->item as $item)
        $instances[(string) $item->instanceId] =
        array("imageId" => (string) $item->imageId,
        "state"   => (string) $item->instanceState->name,
        "dns"     => (string) $item->dnsName);
        return $instances;
    }
    
    function getKeys()
    {
        $params = array("Action" => "DescribeKeyPairs");
        $xml = $this->sendRequest($params);
        
        $keys = array();
        foreach($xml->keySet->item as $item)
        $keys[] = array("name" => (string) $item->keyName, "fingerprint" => (string) $item->keyFingerprint);
        return $keys;
    }
    
    function terminateInstances($toKill)
    {
        $params = array("Action" => "TerminateInstances");
        $toKill = explode(",", $toKill);
        $i = 0;
        foreach($toKill as $id)
        $params['InstanceId.' . ++$i] = $id;
        $xml = $this->sendRequest($params);
        
        $instances = array();
        foreach($xml->instancesSet->item as $item)
        $instances[(string) $item->instanceId] =
        array("shutdownState" => (string) $item->shutdownState,
        "previousState" => (string) $item->previousState);
        return $instances;
    }
    
    function sendRequest(&$params)
    {
        $params['Version'] = "2008-02-01";
        $response =  $this->xmlrequest($params);
        print_r($response); 
    }
    
}
?>
