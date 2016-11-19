<pre>
<?php

ini_set('include_path', "/srv/lib:" . ini_get('include_path'));
print ini_get('include_path');

include('scaffolding.php');



$db = new mysql_database();
$db->prefix = 'jos';
$db->connect('localhost', 'jdev_cloudfront', 'joomla', 'joomla');

$app = 'cloudfront';
$entities = array (
  'asset' => 'assets',
  'assettype' => 'assettypes',
  'distribution' => 'distributions',
);

foreach ($entities as $name => $plural) {
  
  // Setup context  
  $ctx = new SmartyContext('templates');
  $ctx->app = 'cloudfront';
  $ctx->model = $name;
  $ctx->modelname = $name;
  $ctx->models = $plural;
  $table = "#__" . $ctx->app . '_' . $ctx->models;    
  $ctx->reflect($db, $table);
   

  $runner = new templateRunner($ctx, 'templates');
  $runner->walk(true);  
}




