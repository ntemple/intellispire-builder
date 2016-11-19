<?php
/**
 * This file contains the code for the SQS client.
 *
 * Copyright 2006-2007 Intellispire.
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0 
 *
 * Unless required by applicable law or agreed to in writing, 
 * software distributed under the License is distributed on an 
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, 
 * either express or implied. See the License for the specific 
 * language governing permissions and limitations under the License.  
 *
 * @category   Web Services
 * @package    SQS 
 * @author     Nick Temple <Nick.Temple@intellispire.com>  Original Author
 * @copyright  2006 Nick Temple
 * @license    http://www.intellispire.com/license.html 
 * @link       http://www.intellispire.com/
 */

/**
 * The Mechanical Turk
 *
 * All functions return the result or TRUE on success,
 * NULL or false on failure.
 *
 * You can check the exact status using $SQS->statuscode, which should be "Success".
 * for successful transactions.
 * The last requestid and errormsg are also stored.
 *
 * TODO This is very similar to SQS, and the code should be merged.
 */


require_once('aws.class.php');

define('TURK_ENDPOINT',             'http://mechanicalturk.sandbox.amazonaws.com');
// define('TURK_ENDPOINT',             'http://mechanicalturk.amazonaws.com');
define('WEEK', (60*60*24*7) );

class MTurk extends AWS {

  var $noparams = array(); // Null array

  // Results
  var $statuscode;
  var $requestid;
  var $erromsg;
  
  function MTurk($a, $s, $ep = TURK_ENDPOINT) {
    $this->endpoint   = "$ep/onca/xml?";
    parent::__construct($a, $s);
  }

  function GetAccountBalance() {
    $result = $this->_call('GetAccountBalance', array());
    return $result['AMOUNT'][0];
  }

  function Help($about, $type = 'Operation') {
    $result = $this->_call('Help', $this->noparams);
    return $result;
  }

  function CreateHITTemplate($QualificationRequirement, $Title, $Description, $Reward, $Keywords = '', $AssignmentDurationInSeconds = 3600, $AutoApprovalDelayInSeconds = 2592000) {
            
            if (is_array($QualificationRequirement) ) {
              $params = $QualificationRequirement;
            } else {
               $params = array();
            }

            $params['Title']       = $Title;
            $params['Description'] = $Description;
            $params['Reward.1.Amount']      = $Reward;
            $params['Reward.1.CurrencyCode']      = 'USD';
            $params['Keywords']    = $Keywords;
            $params['AssignmentDurationInSeconds'] = $AssignmentDurationInSeconds;
            $params['AutoApprovalDelayInSeconds']  = $AutoApprovalDelayInSeconds;
            return $params;
  }

  function CreateQuestion($filename, $params) {
     extract($params);
     ob_start();
     require($filename);
     $question = ob_get_clean();
     return $question;
  }
    

  function CreateHIT($params, $Question, $RequestorAnnotation, $MaxAssignments=1, $LifetimeInSeconds = WEEK) {
                     
           $params['RequestorAnnotation'] = $RequestorAnnotation;
           $params['MaxAssignments'] = $MaxAssignments;
           $params['LifetimeInSeconds'] = $LifetimeInSeconds;
           $params['Question']          = $Question;

           return $this->_call('CreateHIT', $params);
  }


  function GetReviewableHITs($PageSize = 10, $PageNumber = 1, $direction = 'Descending') {
    $params = array();
    $params['SortProperty']  = 'Expiration'; //   Title  | Reward  | Expiration  | CreationTime  
    $params['SortDirection'] = $direction;   //  'Ascending | Descending';
    $params['PageSize']      = $PageSize;
    $params['PageNumber']    = $PageNumber;
    return $this->_call('GetReviewableHITs', $params);
  }


  function GetAssignmentsForHIT($HITId, $PageSize = 10, $PageNumber = 1, $direction = 'Descending') {
    $params = array();
    $params['HITId'] = $HITId;
    $params['SortProperty']  = 'SubmitTime'; //    AcceptTime  | SubmitTime  | Answer  | AssignmentStatus  
    $params['SortDirection'] = $direction;   //  'Ascending | Descending';
    $params['PageSize']      = $PageSize;
    $params['PageNumber']    = $PageNumber;
    return $this->_call('GetAssignmentsForHIT', $params);
  }

  // immediatly delete HIT
  function DisableHIT($HITId) {
    $params = array();
    $params['HITId'] = $HITId;
    return $this->_call('DisableHIT', $params);
  }

  // delete HIT, only available after everything approved
  function DisposeHIT($HITId) {
    $params = array();
    $params['HITId'] = $HITId;
    return $this->_call('DisposeHIT', $params);
  }


  function ExtendHIT($HITId, $MaxAssignmentsIncrement, $ExpirationIncrementInSeconds = "") {
    $params = array();
    $params['HITId'] = $HITId;
    if ($MaxAssignmentsIncrement)      $params['MaxAssignmentsIncrement']      = $MaxAssignmentsIncrement;
    if ($ExpirationIncrementInSeconds) $params['ExpirationIncrementInSeconds'] = $ExpirationIncrementInSeconds;
    return $this->_call('ExtendHIT', $params);
  }

  function ApproveAssignment($AssignmentId) {
    $params = array();
    $params['AssignmentId'] = $AssignmentId;
    return $this->_call('ApproveAssignment', $params);
  }

  function RejectAssignment($AssignmentId) {
    $params = array();
    $params['AssignmentId'] = $AssignmentId;
    return $this->_call('RejectAssignment', $params);
  }


  // Todo, allow changing of defaults
  function CreateQualificationType($name, $description, $test = '', $answers = '', $keywords ="") {
    $params = array();
    $params['Name']        = $name;
    $params['Description'] = $description;
    if ($keywords) $params['Keywords'] = $keywords;
    $params['RetryDelayInSeconds'] = 60*60*24;
//  $params['TestDurationInSeconds'] = 60*60*4; // 4 hours
    $params['QualificationTypeStatus'] = 'Active'; // or Inactive - but why inactive?
    return $this->_call('CreateQualificationType', $params);
  }

  function SearchQualificationTypes($query = '', $pagesize = 10, $pagenumber = 1, $direction = 'Descending') {
    $params = array();
    if ($query) $params['Query'] = $query;
    $params['SortProperty'] = 'Name';
    $params['SortDirection'] = $direction; //  'Ascending | Descending';
    $params['PageSize'] = $pagesize;
    $params['PageNumber'] = $pagenumber;
    $params['MustBeRequestable'] = 'true'; // true to exclude system searches
    return $this->_call('SearchQualificationTypes', $params);
  }

  function GetQualificationRequests($QualificationTypeId, $pagesize = 10, $pagenumber = 1, $direction = 'Descending') {
    $params = array();
    if ($id) $params['QualificationTypeId'] = $QualificationTypeId; // ALL if not submitted
    $params['SortProperty'] = 'SubmitTime'; //  QualificationTypeId  | SubmitTime  
    $params['SortDirection'] = $direction; //  'Ascending | Descending';
    $params['PageSize'] = $pagesize;
    $params['PageNumber'] = $pagenumber;
    return $this->_call('GetQualificationRequests', $params);

  }


  function GrantQualification($QualificationRequestId, $Value = 0) {
    $params = array();
    $params['QualificationRequestId'] = $id;
    $params['Value'] = $value; // TODO: TEST, docs wrong? Always set to 1? IntegerValue?
    return $this->_call('GrantQualification', $params);
  }

  function UpdateQualificationScore($QualificationTypeId, $SubjectId, $Value) {
    $params = array();
    $params['QualificationTypeId'] = $QualificationTypeId;
    $params['IntegerValue'] = $Value; // TODO? Value *** DOCS WRONG - this must be IntegerValue, not Value!
    $params['SubjectId'] = $SubjectId;
    return $this->_call('UpdateQualificationScore', $params);
  }

  function NotifyWorkers($workers, $Subject, $MessageText) {
    $params = array();
      if (! is_array($workers)) {
        $w = array();
        $w[] = $workers;
      } else {
        $w = $workers;
      }
      $i = 1;
      foreach ($w as $worker) {
        $params['WorkerId.' . $i] = $worker;
      }
     
      $params['Subject'] = 'Subject';
      $params['MessageText'] = $MessageText;
      return $this->_call('NotifyWorkers', $params);
   }


   /**
    * Begin to build up a hit. If it is used, it MUST be called before
    * any other functions, to appropriately set the identifiers
   */

   // See: http://docs.amazonwebservices.com/AWSMechanicalTurkRequester/2005-10-01/ApiReference/QualificationRequirementDataStructureArticle.html
//   define('Worker_PercentAssignmentsSubmitted', '00000000000000000000'); // etc

   function createQualificationRequirement(&$params, $QualificationTypeId, $Comparator, $IntegerValue = 0) {
    static $i = 0;
    if (empty($params)) $i = 0;
    $i++;
    $params["QualificationRequirement.$i.QualificationTypeId"]  = $QualificationTypeId;
    $params["QualificationRequirement.$i.Comparator"]           = $Comparator; //  LessThan  | LessThanOrEqualTo  | GreaterThan  | GreaterThanOrEqualTo  | EqualTo  | NotEqualTo  | Exists  
#    $params["QualificationRequirement.$i.RequiredToPreview"]    = 1;
    if ($Comparator != 'Exists') $params["QualificationRequirement.$i.IntegerValue"]   = $IntegerValue;
   }


  function _call($action, &$params, $q = '') {
    // Add Actions
    $params['Service']   = 'AWSMechanicalTurkRequester';
    $params['Operation'] = $action;

    $result = $this->xmlrequest($params); 
    return $result;
  }

}
?>
