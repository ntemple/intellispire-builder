<?php
/**
* @version    $Id: mwgBaseGizmo.class.php 21 2013-03-15 19:35:01Z ntemple $
* @package    MWG
* @copyright  Copyright (C) 2010 Intellispire, LLC. All rights reserved.
* @license    GNU/GPL v2.0, see LICENSE.txt
*
* Marketing Website Generator is free software. 
* This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_MWG') or die ('Restricted Access');

/**
* base class for gizmos.
*/
abstract class mwgBaseGizmo {

  /** @var MWG */
  var $mwg;  
  var $identity;
  var $gizmo_mf;

  var $id;
  var $name;
  var $title;
  var $params; 
  var $data;
  var $active;
  
  var $ordre;
  var $position;
  var $display_title;
  var $display_group;
  var $display_hidden;

  /**
  * Constructor called with serialized
  * paramaters
  * 
  * @param mixed $params
  * @return mwgGizmoBase
  */
  function __construct($identity)  {
    $this->identity = $identity;
    $this->id = '';
  }
  
  function hasAdminDisplay() {
    return method_exists($this, 'getAdminForm');
  }
  
  function hasRender() {
    return method_exists($this, 'render');
  }
  
  function hasRenderAsWidget() {
    return $this->hasRender();
  }  
  
  // Override the below methods to add functionality

  /* Modify these functions */
  abstract function getName();

  

  function getFields() {
    return array(
    );
  }

  /**
  * display the form to get the parameters
  *
  * @param array $atts associative array of attributes from the database
  * @return string
  */
/*
  function getAdminForm($atts = array()) {
    
    $fields = $this->getFields();
    $params = shortcode_atts($fields, $atts);

    $out = $this->generateAdminForm($fields, $params, false);

    return "<div>\n$out</div>\n";
  }
*/

  /**
  * The main routine to display the gizmo.
  *
  * In addition to the atts,
  * $this->params contans the params from the admin form
  *
  * You can use $this->saveLocalData to serialize an array for later retrieval
  * and $this->getLocalData to get it back later.
  *
  * @param mixed $atts shortcode style attributes if called via [gizmo id="x"]
  */
/*
  function render($atts) {
    $data = shortcode_atts($this->params, $atts);
    extract($data);
    
    $out = '';

    return $out;
  }
*/

  /**
  * Render a Gizmo similiar to to a wordpress widget
  * 
  *   $defaults = array(
  *    'name'          => sprintf(__('Sidebar %d'), $i ),
  *    'id'            => 'sidebar-$i',
  *    'before_widget' => '<li id="%1$s" class="widget %2$s">',
  *    'after_widget'  => '</li>',
  *    'before_title'  => '<h2 class="widgettitle">',
  *    'after_title'   => '</h2>' 
  *  ); 
  *
  * 
  * @param mixed $atts
  */
  
  function render_as_widget($atts) {
    // No title to display
    if (!$this->display_title) return $this->render($atts);

 
    ob_start();
    if (isset($atts['before_title'])) echo $atts['before_title'];
    echo $this->title . "<br>\n";
    if (isset($atts['after_title'])) echo $atts['after_title'];
    if (isset($atts['before_widget'])) echo $atts['before_widget'];
    echo $this->render($atts);
    if (isset($atts['after_widget'])) echo $atts['after_widget'];
    return ob_get_clean();
  }

  /* 
  * Events model.  override to hook into appropriate events
  * Events are added occasionally, so check documentation in
  * lib/mwg/mwgBaseGizmo.php.
  */

  /* Payment Functions */
  // function paypalIPN($ipn, $valid) {  }


  /**
  * Called after a signup has been completed
  * 
  * @param mixed $member_id
  * @param mixed $password
  */
  //function afterSignup($member_id, $password) { print "afterSignup($member_id, $password)\n"); }

  /**
  * Called before a signup. Allows you to modify the POST data if necessary
  * 
  */
  //function beforeSignup() { print "beforeSignup()\n"); }


  /**
  * Called after the template has been processed, but before
  * shortcodes are run. Used to forcefully add or remove existing
  * shortcodes from pages.
  * 
  * For example, use:
  * $this->add_shortcode($shortcode, $method);
  *
  * @param mixed $document
  * @param mixed $content
  */
  // function beforeDoShortcode(mwgDocument $document, &$content) {  print "beforeDoShortcode {$this->id}\n"; }

  /**
  * Called just before the page is put together with the
  * head, body and other components.  Great place to
  * add javascript, analytics, etc to the document.
  *
  * @param mixed $document
  * @param mixed $content
  */
  // function beforeDocumentRender(mwgDocument $document, &$content) {  print "beforeDocumentRender {$this->id}\n"; }

  /**
  * Last call before the page is displayed.
  *
  * @param mixed $page
  */
  // function afterDocumentRender(&$page) {  print "afterDocumentRender {$this->id}\n"; }
    
  
  
  /**
  * Not necessary to override
  * 
  */
  
  function add_shortcode($shortcode, $method) {
    return add_shortcode($shortcode, array($this, $method));
  }
  
  function generateAdminForm($fields, $params, $generator = true) {
    $out = '';
    $name = $this->getName();
    foreach ($fields as $field => $default) {
      $id = $name . "gizmo-$field";
      if ($generator) {
        $value = '< ?php echo $params['. $field .']; ?>';
      } else {
        $value = $params[$field];
      }
      $out .= "  <label for='$id' style='line-height:35px;display:block;'>$id: <input type='text' id='$id' name='$id' value='$value' /></label>\n";
    }
    $id = $name . "gizmo-submit";
    $out .= "  <input type='hidden' name='$id' id='$id' value='1' />\n";
    if ($generator) $out = "<pre>\n" . htmlentities($out) . "\n</pre>\n";
    return  "<hr>\n$out\n<hr>";
  }

  /**
  * Given a request, extract the data into a format you can use.
  * Assume someone submitted your AdminForm
  * The return will then be serialized and
  *
  * @param mwgRequest $request
  */

  function extractAdminFormData(mwgRequest $request) {
    $name = $this->getName();
    $id = $name . "gizmo-submit";
    if ($request->get($id) != 1) return null;
    $params = array();
    $fields = $this->getFields();
    foreach ($fields as $field => $default) {
      $id = $name . "gizmo-$field";
      $params[$field] = $request->get($id);
    }
    return $params;
  }

  
  // Implementation. You should not need to override
  // the following methods.

  function getManifest() {
    if ($this->gizmo_mf) return $this->gizmo_mf;

    $ident = explode('.', $this->identity);
    $mwg = array_shift($ident);
    if ($mwg != 'mwg') return false;

    $path = $ident;
    array_unshift($path, MWG_BASE);
    $path = implode('/', $path) . '.yml.php';
    $this->gizmo_mf = SPYC::YAMLLoad($path);
    return $this->gizmo_mf;
  }

  function setManifest($gizmo_mf) {
    $this->gizmo_mf = $gizmo_mf;    
  }

  function hydrate($row = null) {
    if ($row) {
      foreach ($row as $name=>$value) {
        $this->$name = $value;
      }
      if ($this->params) $this->params = unserialize($this->params);
      if ($this->data) $this->data = unserialize($this->data);
    } else {
      // set defaults
      $gizmo_mf      = $this->getManifest();
      $this->name   = $gizmo_mf['name'];
      $this->title  = $gizmo_mf['title'];
      $this->active = 0;
    }
    return $this;
  }

  function store() {
    $row = get_object_vars($this);

    // Don't update the local data unless it has been set
    if ($row['data'])   
      $row['data'] = serialize($row['data']);
    else 
      unset($row['data']);

    if ($row['params']) $row['params'] = serialize($row['params']);
    if (! $row['id']) unset($row['id']);

    $db = MWG::getInstance()->getDb();
    $this->id = $db->store('mwg_gizmo', $row);
    return $this;
  }

  function getDocumentation() {
    $mf = $this->getManifest();
    
    if (method_exists($this, 'documentation')) {
      return $this->documentation($mf['documentation']);
    } else {
      return $mf['documentation'];    
    }
    
  }

  function getLocalData() { 
    return $this->data; 
  }

  function saveLocalData($data) {
    $this->data = $data;
    $row = array (
    'id' => $this->id,
    'data' => serialize($data)
    );
    MWG::getInstance()->getDb()->update('mwg_gizmos', $row);
  }
}




