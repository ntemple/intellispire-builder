<?php
/**
* @version    $Id: mwgDocument.class.php 21 2013-03-15 19:35:01Z ntemple $
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

// Encapsulate a document (single page)

class mwgDocument {

  var $head;            // array of extra headers
  var $title;           // current title
  var $meta_description; // current description
  var $meta_keywords;   // keywords
  var $before_body_end; // code to add before the body end

  var $template;

  function __construct() {
    $this->head = array();
    $this->before_body_end = array();
    $this->addJs(MWG_BASEHREF . '/js/functions.js');
  }

  function addToHead($string) {
    $this->head[] = $string;
  }

  function addJs($path) {
    $this->head[] = "<script src='$path' type='text/javascript'></script>";
  }

  function addCSS($path) {
    $this->head[] = "<link rel='stylesheet' href='$path' type='text/css' />";    
  }

  function addBeforeBodyEnd($string) {
    $this->before_body_end[] = $string;
  }

  /**
  This should be changed to be page specific. Right now, BFM
  has one title / description / keywords for all pages
  */ 
  function getHead() {

    $out = "";

    if ($this->meta_keywords)    array_push($this->head, "<meta name='keywords' content='{$this->meta_keywords}' />");
    if ($this->meta_description) array_push($this->head, "<meta name='description' content='{$this->meta_description}' />");

    foreach ($this->head as $string) {
      $out .= "    $string\n";
    }
    return "\n$out\n";
  }

  function setDescription($description, $default = false) {
    if ($default && $this->meta_description) return;
    $this->meta_description = $description;
  }

  function setKeywords($keywords, $default = false) {
    if ($default && $this->meta_keywords) return;
    $this->meta_keywords = $keywords;
  }


  /**
  * Set the document title
  * 
  * @param mixed $title   new title
  * @param mixed $append  a string to use a seperator, if you want to append to the current title
  * @param mixed $default don't overwrite
  */
  function setTitle($title, $append = false, $default = false) {

    if ($default && $this->title) return;

    if ($append) {
      $title = $this->title . $append . $title;
    }
    $this->title = $title;
  }

  function getTitle() {
    return $this->title;
  }


  function setContent($content) {
    $this->content = $content;
  }


  /**
  * Last function to be called.
  * Completes regex replacement, 
  * and prints the document
  * 
  */
  function renderDocument() {
    global $unread_inbox; // @todo make this a gizmo
    
    $content = $this->content;

    // Add head and body tags
    if (stripos($content, '</head>') === false) {
      $skel = file_get_contents(MWG_BASE . '/templates/skeleton.html');
      $content = str_ireplace('{body}', $content, $skel);
    }
 
    $newbody = implode("\n", $this->before_body_end ) . "</body>";
    $content = str_ireplace('</body>', $newbody, $content);

    $head = $this->getHead();
    $content = str_ireplace('</head>', "$head</head>", $content);
    $content = preg_replace("|<title>(.*?)</title>|i", "<title>" . $this->getTitle() . "</title>", $content);
    $content = str_replace('{inbox}',  $unread_inbox, $content);

    return $content;
  }

}

