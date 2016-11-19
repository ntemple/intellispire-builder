<?php

require_once('../config.php');
require_once('turk.class.php');
test();

function test() {
  $mt = new MTurk(API_AMAZON_ACCESS_KEY, API_AMAZON_SECRET, TURK_ENDPOINT);
#  $result = $mt->GetAccountBalance();
#  print_r($result);

#  $result = $mt->Help('GetReviewableHITs');
#  print_r($result);

#  DZJ4YVZ71WXZ5BH9EW2Z
#  $result = $mt->CreateQualificationType('T1 - Intellispire Text Review', 'This qualification allows you to review and write text for Intellispire based HITs. No test is involved, it is automatically granted with a few hours. Your score will increase with accurately completed HITs');
#  print_r($result);

#  $result = $mt->SearchQualificationTypes('T1 - Intellispire Text Review');
#  print_r($result);

#  $result = $mt->SearchQualificationTypes();
#  print_r($result);

#  $result = $mt->GetQualificationRequests();
#  print_r($result);


#  $result = $mt->GetQualificationRequests('DZJ4YVZ71WXZ5BH9EW2Z');
#  print_r($result);

    
#  $result = $mt->GrantQualification('DZJ4YVZ71WXZ5BH9EW2Z2ZEPQ9Y3V9GZ1GYTGZ0Z', 0);
#  print_r($result);

# $result = $mt->UpdateQualificationScore('DZJ4YVZ71WXZ5BH9EW2Z', 'AUAJWQBGCYUYQ', 0);
# print_r($result);

#  $result = $mt->NotifyWorkers('AUAJWQBGCYUYQ', 'Test', 'This is a test notification from Amazon');
#  print_r($result);

#   $qualifications = array();
#   $mt->createQualificationRequirement($qualifications, 'DZJ4YVZ71WXZ5BH9EW2Z', 'GreaterThanOrEqualTo', 0);
//   $mt->createQualificationRequirement($qualifications, 'DZJ4YVZ71WXZ5BH9EW2Z', 'GreaterThanOrEqualTo', 0);

#   $Template = $mt->CreateHITTemplate($qualifications, 'Test HIT', 'This is a test hit description from PHP', '0.01', 'intellispire, test, hit');
#   $Question = $mt->CreateQuestion('question.xml', array('title' => 'My Title') );
  
   # $result = $mt->CreateHIT($Template, $Question, 'databaseid',1);
   # print_r($result);

   $result = $mt->GetReviewableHITs();
   print_r($result);
   $result = $mt->multi_exec();
   print_r($result);

# RAK60EYH9W96SWXCAG7Z0YBZ33H2K28W0YYJPW3Z
   if (isset($result['HITTID'])) {
   $ids = $result['HITID'];
   foreach ($ids as $id) {  
#       print "$id\n";
     $result = $mt->GetAssignmentsForHIT($id);
     print_r($result);

     if ($result['ASSIGNMENTSTATUS'] == 'Submitted') {
       $id = $result['ASSIGNMENTID'];
       print "$id\n";

  #     $result = $mt->RejectAssignment($id);
        $result = $mt->ApproveAssignment($id);
        print_r($result);
        exit; 
     }
   }
   }

}

