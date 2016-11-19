<?php
/*
 Plugin Name: Mobile Site Builder
 Plugin URI: http://www.intellispire.com/mobile/
 Description: Mobile Site Builder
 Author: Nick Temple
 Version: 1.0
 Author URI: http://www.intellispire.com
 */


$views = loadViews();
print_r($views);
die();

add_action( 'init', 'create_post_type' );
function create_post_type() {
	
	
	
	register_post_type( 'mobileview',
	array(
    'labels' => array(  
      'name' => __( 'Mobile Pages' ),  
      'singular_name' => __( 'Mobile Page' ),
	  'add_new' =>  _x('Add New', 'Mobile Page'),
	  'not_found' =>    __( 'No mobile pages found' )
	),
    'public' => true,
    'has_archive' => false,
    'show_ui' => true,
	//    'rewrite' => array("slug" => "message"),
	'capability_type' => 'post',
	'show_in_nav_menus' => true,
	'supports' => array(
	  'title',
	  'editor',	  
	  )
	));
	
	register_taxonomy('mobilepagetypes',array (
  0 => 'mobileview',
),array( 'hierarchical' => true, 'label' => 'Mobile Page Types','show_ui' => false,'query_var' => false,'rewrite' => array('slug' => ''),'singular_label' => 'Mobile Page Type') );
	
/*	
  register_post_type('mobileview', array(	'label' => 'Mobile Pages','description' => '','public' => true,'show_ui' => true,'show_in_menu' => 'menu.php','capability_type' => 'post','hierarchical' => true,'rewrite' => array('slug' => ''),'query_var' => true,'supports' => array('title','editor','revisions','page-attributes',),'labels' => array (
  'name' => 'Mobile Pages',
  'singular_name' => 'Mobile Page',
  'menu_name' => 'Mobile Menu',
  'add_new' => 'Add New',
  'add_new_item' => 'Add New Mobile Page',
  'edit' => 'Edit',
  'edit_item' => 'Edit Mobile Page',
  'new_item' => 'New Mobile Page',
  'view' => 'View Mobile Page',
  'view_item' => 'View Mobile Page',
  'search_items' => 'Search Mobile Pages',
  'not_found' => 'No Mobile Pages Found',
  'not_found_in_trash' => 'No Mobile Pages Found in Trash',
  'parent' => 'Parent Mobile Page',
),) );
*/
	
}

add_action( 'add_meta_boxes', 'myplugin_add_custom_box' );

function myplugin_add_custom_box() {
  add_meta_box("mobilesite-meta-pagetype", "Page Type", "mobilesite_meta_pagetype",  "mobileview", "side", "low");
  add_meta_box("my-property-meta", "Property Options", "my_property_meta",  "mobileview", "side", "low");
}

function mobilesite_meta_pagetype() {
//	print '<i>Please select a Pagetype then "publish" to view additional options.</i>';
wp_nonce_field( plugin_basename( __FILE__ ), 'mobilesite_noncename' );
$out =  <<<EOD
   
    <select name='mobilesite_pagetype'>
    <option value="">Please Select</option>
    <option vale="RSS">News Feed (RSS)</option>
    </select>
    <br>
	<i>Please select a Pagetype then "publish" to view additional options.</i>
EOD;


print $out;	
}

function my_property_meta() { 
	echo "Boo!"; 
	
	  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  // The actual fields for data entry
  echo '<label for="myplugin_new_field">';
       _e("Description for this field", 'myplugin_textdomain' );
  echo '</label> ';
  echo '<input type="text" id="myplugin_new_field" name="myplugin_new_field" value="whatever" size="25" />';

}

/* When the post is saved, saves our custom data */
add_action( 'save_post', 'myplugin_save_postdata' );
function myplugin_save_postdata( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data

  $mydata = $_POST['myplugin_new_field'];
  
  print_r($mydata);
  die();

  // Do something with $mydata 
  // probably using add_post_meta(), update_post_meta(), or 
  // a custom table (see Further Reading section below)
}



add_action( 'template_redirect', 'runmobile' );
function runmobile() {
    global $post;	
	
     if ($post->post_type != 'mobileview') return;
	
	print "<pre>\n";
//	print_r($GLOBALS);

print_r($post);
    print "</pre>\n";
	
    die();
}

/*

global $post;
print_r($post);
print_r($_GET);
// die();
?>
<?php get_header();?>
  <?php if(have_posts()) :?>
    <div class="post">
        <div class="entry">
                <?php the_content(); ?>
        </div>
    </div>
<?php endif; ?>
<?php get_footer(); ?>

*/

function loadViews($filter = array('headertext', 'footertext', 'mobilepage_title', 'mobilepage_description')) {
	$start =  microtime(true);
	$path = plugin_dir_path( __FILE__ );
	$viewpath = $path . "views/page/tmpl";
	$files = ls($viewpath);
	
	$views = array();
	foreach ($files as $file) {
		if (strpos($file, ".xml") > 0) {
		  $contents = file_get_contents("$viewpath/$file");
		  		  
		  $xml  = new SimpleXMLElement($contents);
		  $xml = simplexml2array($xml);
		  
	  
		  $view = array();
		  $view['file'] = $file;
		  $view['path'] = "$viewpath/$file";
		  $view['title'] = $xml['layout']['message'];
		  		  
		  $params = $xml['state']['params']['param'];

		  foreach ($params as $xparam) {
		  	$param = $xparam['@'];
		  	
		  	// Here we filter out the fields that wordpress supplies
		  	// by default;
		  	if (in_array($param['name'], $filter)) continue;
		  	
		  	// Add additional options, if available
		  	if (isset($xparam['option'])) $param['option'] = $xparam['option'];

		  	$view['fields'][] = $param;
		  }
		  		  
		  $views[] = $view;
	
		}
	}
	$end = microtime(true);	
	return $views;
}

function ls($path) {
	$files = array();
    $dir = opendir($path);
    while (($file = readdir($dir)) !== false) {
    	$files[] = $file; 
    }	
	closedir($dir);
	return $files;
}
    

// if (!function_exists('simplexml2array')) {
  function simplexml2array($xml) {

    if (is_object($xml) && get_class($xml) == 'SimpleXMLElement') {
      $attributes = $xml->attributes();
      foreach($attributes as $k=>$v) {
        if ($v) $a[$k] = (string) $v;
      }
      $x = $xml;
      $xml = get_object_vars($xml);
    }
    
    if (is_array($xml)) {
      if (count($xml) == 0) return (string) $x; // for CDATA
      foreach($xml as $key=>$value) {
        $r[$key] = simplexml2array($value);
      }
      if (isset($a)) $r['@'] = $a;    // Attributes
      return $r;
    }
    
    return (string) $xml;
  }
// }
