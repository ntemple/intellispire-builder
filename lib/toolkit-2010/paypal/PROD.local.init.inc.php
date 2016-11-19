<?php
// THIS IS PRODUCTION. DO NOT OVERWRITE
     define('PDC_TESTING',  0);
     define('PDC_ROOTPATH', './');
     define('PDC_SITENAME', 'PaydotCom.com');
     define('PDC_WEBMASTEREMAIL', 'support@PayDotCom.com');
     define('PDC_LOG_DIR', '/home/paydotco/logs');

     // PAYPAL
     define('PDC_PP_URL', 'https://www.paypal.com/cgi-bin/webscr'); // {pay_url}
     define('PDC_PP_NOTIFY_URL', 'https://paydotcom.com/paypal.ipn.php'); // {notify_url}
     define('PDC_PP_ADDRESS', 'www.paypal.com');


     // STORMPAY
     define('PDC_SM_URL', 'https://www.stormpay.com/stormpay/handle_gen.php'); // {pay_url}
     define('PDC_SM_NOTIFY_URL', 'https://paydotcom.com/stormpay.ipn.php');    // {notify_url}


     define('PDC_DOMAIN', '.paydotcom.com');
     define('IPN_TABLE', 'purchase_ipn');


     define('PDC_DB_P_HOST',     'localhost');
     define('PDC_DB_P_DATABASE', 'paydotco_p');
     define('PDC_DB_P_USER',     'paydotco_p');
     define('PDC_DB_P_PASSWORD', 'trinity');

     define('PDC_DB_A_HOST',     'localhost');
     define('PDC_DB_A_DATABASE', 'paydotco_a');
     define('PDC_DB_A_USER',     'paydotco_p');
     define('PDC_DB_A_PASSWORD', 'trinity');

     define('PDC_DB_MIKE_HOST',     'mikefilsaime.com');
     define('PDC_DB_MIKE_DATABASE', 'mikef_z');
     define('PDC_DB_MIKE_USER',     'mikef_z');
     define('PDC_DB_MIKE_PASSWORD', 'trinity');
?>
