<?php
 
  //  THIS IS TESTING
     define('PDC_TESTING', 1);
     define('PDC_ROOTPATH', './');
     define('PDC_SITENAME', 'sandbox.pdc.intelliforge.org');
     define('PDC_WEBMASTEREMAIL', 'admin@pdc.intelliforge.org');

     define('PDC_LOG_DIR', '/home/pdcforge/s_logs');


     // define('NOTIFY_URL', 'http://testing.pdc.intelliforge.org/ipn.unified.php');
     // PAYPAL
      
     define('PDC_PP_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr'); // {pay_url}
     define('PDC_PP_NOTIFY_URL', 'http://sandbox.pdc.intelliforge.org/paypal.ipn.php'); // {notify_url}
     define('PDC_PP_ADDRESS', 'www.sandbox.paypal.com');



     // STORMPAY     
     define('PDC_SM_URL', 'https://www.stormpay.com/stormpay/handle_gen.php'); // {pay_url}
     define('PDC_SM_NOTIFY_URL', 'http://sandbox.pdc.intelliforge.org/stormpay.ipn.php');    // {norify_url}



     define('IPN_TABLE', 'purchase_ipn');
     define('PDC_DOMAIN', '.pdc.intelliforge.org');
     // define('PDC_DOMAIN', '.paydotcom.com');


     define('PDC_DB_P_HOST',     'localhost');
     define('PDC_DB_P_DATABASE', 'pdcforge_p');
     define('PDC_DB_P_USER',     'pdcforge');
     define('PDC_DB_P_PASSWORD', 'T2yg7Fy3');

     define('PDC_DB_A_HOST',     'localhost');
     define('PDC_DB_A_DATABASE', 'pdcforge_a');
     define('PDC_DB_A_USER',     'pdcforge');
     define('PDC_DB_A_PASSWORD', 'T2yg7Fy3');

     define('PDC_DB_MIKE_HOST',     'localhost');
     define('PDC_DB_MIKE_DATABASE', 'pdcforge_a');
     define('PDC_DB_MIKE_USER',     'pdcforge');
     define('PDC_DB_MIKE_PASSWORD', 'T2yg7Fy3');
?>
