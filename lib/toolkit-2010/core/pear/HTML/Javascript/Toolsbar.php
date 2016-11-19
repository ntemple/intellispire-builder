<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors:                                                             |
// | Pierre-Alain Joye <paj@pearfr.org>                                   |
// +----------------------------------------------------------------------+
// $Id: Toolsbar.php 21 2013-03-15 19:35:01Z ntemple $

/**
 * A class to create DHTML toolbar.
 *
 * It generates HTML Table with links for non JS browsers
 * Known to work with DOM browsers and NS4/IE series.
 * It supports toolbar elements as:
 * - image
 * - text
 * - image+text
 * - Select, text input or hidden field form element (with or withouth label)
 *
 * Each element owns an action property:
 * - JavaSscript callback
 * - Link
 *
 * Additionnals passive elements are available:
 * - New row
 * - Space
 *
 * The generated script can be saved as a javascript source
 * file or generated on each request.
 *
 * The toolbar object provides client side functions to manage it:
 * - Show/Hide
 * - Enable/disable individual button
 * - Exec a toolbar action
 * - Set the state (active, inactive, i.e. bold pressed)
 *
 * Multiple toolbars are allowed in the same page.
 *
 * Usage example:
 * <code>
 * $mytoolbar = new HTML_Javascript_Toolsbar()
 * $mytoolbar = array(
 *                  'bold'=>array('callback', 'Bold', 'Bold', 'bold.gif', 3),
 *                  'italic'=>array( 'callback',
 *                                   'Italic',
 *                                   'Italic',
 *                                   'italic.gif',
 *                                   3),
 *                  'fonts'=>array( 'select',
 *                                  'Fonts',
 *                                  'Fonts',
 *                                  array(
 *                                          "1 (8 pt)"=>"1",
 *                                          "2 (10 pt)"=>"2",
 *                                          "3 (12 pt)"=>"3",
 *                                          "4 (14 pt)"=>"4",
 *                                          "5 (18 pt)"=>"5",
 *                                          "6 (24 pt)"=>"6",
 *                                          "7 (36 pt)"=>"7"
 *                                  ),
 *                                  'onchange'='fonts_onchange'
 *                                  3),
 *                  'search'=>array('input','Search','search', array(
 *                                                              'size'=>30
 *                                                             ),
 *                  'home'=>array(  'link',
 *                                  'home.html',
 *                                  'Return to home',
 *                                  'home.gif',
 *                                  1
 *                          )
 *              );
 *
 * $b = $js->convertVar($a, 'arr', true);
 * </code>
 *
 * @author Pierre-Alian Joye <paj@pearfr.org>
 * @package HTML_Javascript
 * @subpackage Toolsbar
 */
/**
 * Conversion tools
 */
require_once 'HTML/Javascript/Convert.php';

/**
 * Core Javascript tools, save files, writeln
 */
require_once 'HTML/Javascript.php';

if(!defined('HTML_JAVASCRIPT_URL')) {
/**
 * Javascript root URL
 */
    define('HTML_JAVASCRIPT_URL','/js/pear/html/javascript');
}
if(!defined('HTML_JAVASCRIPT_FILEPATH')) {
/**
 * Javascript filesystem Path
 * This path is used to store JS files if the outputMode is set to
 * {@link HTML_JAVASCRIPT_OUTPUT_FILE}, you must have write access to this
 * folder.
 */
    define('HTML_JAVASCRIPT_FILEPATH','toolbar/config');
}


if(!defined('HTML_JAVASCRIPT_IMG_URL')) {
/**
 * Images root URL
 */
    define('HTML_JAVASCRIPT_IMG_URL','/js/pear/html/javascript');
}

/** Error codes */
/** Wrong argument type */
define ('HTML_JAVASCRIPT_TOOLSBAR_INVALID_ARG', 501);

/** Wrong element type */
define('HTML_JAVASCRIPT_TOOLSBAR_INVALID_TYPE', 502);

/** Element given is the position array does not exist in the element lists */
define('HTML_JAVASCRIPT_TOOLSBAR_ELEMS_NOT_SET', 503);

/** An invalid options has been given */
define('HTML_JAVASCRIPT_TOOLSBAR_INVALID_OPTION', 504);

/** One of the arguement is not allowed or raises a conflict with an option */
define('HTML_JAVASCRIPT_TOOLSBAR_ARG_NOT_ALLOWED', 505);

/** Element given is the position array does not exist in the element lists */
define('HTML_JAVASCRIPT_NOT_IMPLEMENTED', 599);

/**
 * Main Class
 * Toolsbar class, manage all datas of a toolbars
 *
 * @package HTML_Javascript
 * @subpackage Toolsbar
 * @access public
 */
class HTML_Javascript_Toolsbar extends HTML_Javascript {

    /**
     * Name of the toolsbar
     * @var string
     * @access private
     */
    var $name;

    /**
     * Definition of the elements
     * @var array
     * @access private
     */
    var $elements;

    /**
     * Definition of the elements
     * @see toolbar_example.php How to use toolsbar
     * @var array
     * @access private
     */
    var $positions;

    /**
     * Number of elements
     * @var integer
     * @access private
     */
    var $_elems;

    /**
     * Options of the toolbar:
     *
     * @var array
     * @access private
     */
    var $options;

    // {{{ HTML_Javascript_Toolsbar

    /**
     * Constructor
     *
     * You can pass every data needed by a toolsbar to the constructor
     * and generate the toolsbar in a few lines.
     *
     * @param  string   Name of the toolbar
     *                  only valid JS variable name are allowed
     * @param  string   Options for the toolbar (@link $options)
     * @param  string   Elements (@link elements)
     * @param  string   Positions of the elements
     *
     * @access public
     * @return object   HTML_Javascript_Toolsbar
     */
    function HTML_Javascript_Toolsbar(
                $name, $options=null, $elements=null, $positions=null
            ) {
        $this->name = $name;
        $this->elements = array();
        $this->positions = array();
        if(!is_null($options)){
            $this->setOptions($options);
        }
        if(!is_null($elements)){
            $this->setElements($elements);
        }
    }

    // }}} HTML_Javascript_Toolsbar
    // {{{ addElements

    /**
     * Set the elements of the toolbar.
     *
     * Add many elements using the array $elements
     * The key is the name of each element.
     * <code>
     * $elements = array(
     *                  'mybutton'=>array('foo','bar')
     *              );
     * </code>
     * @see addElement
     * @param  string  $param coment
     * @access public
     * @return mixed return
     */
    function addElements( $elements )
    {
        foreach($elements as $name=>$props) {
            $this->addElement($name, $props);
        }
    }

    // }}} addElements
    // {{{ addElement

    /**
     * Add an element
     *
     * Add an element using $name as name and $properties
     * See the documentation for the list of allowed elements and properties
     * Special elements are 'space', 'separator' and 'newline'.
     *
     * @param   string  element name<br>
     *                  Special names:
     *                  - space: create an empty element
     *                  - separator: create a separator element (vertical line)
     *                  - newline: start a new line
     * @param   array   properties
     * @access public
     * @return mixed return
     */
    function addElement( $name, $properties=null )
    {
        static $separator=0;
        if($name=='space' || $name=='newrow'){
            $this->element[] = $name;
        } else {
            if(is_array($properties) && isset($properties['type']) ){
                switch($properties['type']){
                    case 'text':
                        $this->_addText($name, $properties);
                    break;
                    case 'image':
                        $this->_addImage($name, $properties);
                    break;
                    case 'select':
                        $this->_addSelect($name, $properties);
                    break;
                    case 'input':
                        $this->_addInput($name, $properties);
                    break;
                    case 'checkbox':
                        $this->_addCheckbox($name, $properties);
                    break;
                    case 'separator':
                        $this->elements['separator'.$separator++] = array();
                    default:
                        return $this->raiseError(
                            HTML_JAVASCRIPT_TOOLSBAR_INVALID_TYPE,
                            'addElement'
                        );
                    break;
                }
            }
        }
    }

    // }}} addElement
    // {{{ setPositions

    /**
     * Set the positions of each element.
     * The array must contain either the name (key)
     * of an element or a separator (space, newrow).
     * <code>
     * $positions = array('bold','space','italic','newrow','home');
     * $mytoolbar->setPositions($positions);
     * </code>
     *
     * @param  array    $positions An orderer list of elements names
     * @access public
     * @return mixed    returns true on success or HTML_Javascript::Error
     *                  An error raises when one or more invalid name is given.
     */
    function setPositions( $positions  )
    {
        if( !is_null( $this->elements ) ){
            if( is_array($positions) ){
                foreach($positions as $pos) {
                    $this->positions[] = $pos;
                }
            } else {
                return $this->raiseError(
                    HTML_JAVASCRIPT_TOOLSBAR_INVALID_ARG,
                    'setPositions'
                );
            }
        } else {
            return $this->raiseError(
                HTML_JAVASCRIPT_TOOLSBAR_ELEMS_NOT_SET,
                'setPositions'
            );
        }
    }

    // }}} setPositions
    // {{{ setOptions

    /**
     * Set the options for the active toolsbar.
     *
     * - URL: define the URL of the JS files, default is HTML_Javascript_URL
     * Available options:
     * - URL
     * URL of the javascript folder
     *              default set to {@link HTML_JAVASCRIPT_URL}
     * - filepath
     * System filepath to the javascript folder
     * default set to DOCUMENT_ROOT/js/pearjs/toolsbar
     * - cssClass
     * CSS class for the toolsbar
     * Default 'pearjs_toolsbar
     * - outputMode:
     * whether to generate a JS file or not
     * see {@link setOutputMode}
     * Only {@link HTML_JAVASCRIPT_OUTPUT_FILE} and
     * {@link HTML_JAVASCRIPT_OUTPUT_RETURN} are allowed
     *
     * @param  string  $param coment
     * @access public
     * @return mixed    True on success or an HTML_Javascript_Error object
     */
    function setOptions( $options )
    {
        $this->options = array(
                            'URL'       => HTML_JAVASCRIPT_URL,
                            'cssClass'  => 'pearjs_toolsbar',
                            'imgURL'    => 'images'
                        );
        $this->filepath = HTML_JAVASCRIPT_FILEPATH;
        $file = $this->filepath.'/'.$this->name.'.js';
        $this->setOutputMode(HTML_JAVASCRIPT_OUTPUT_FILE, $file);

        if( is_array($options) ){
            $outputMode = -1;
            $file = '';
            foreach($options as $key=>$val){
                switch ($key){
                    case 'URL':
                    case 'cssClass':
                    case 'imgURL':
                        $this->options[$key] = $val;
                        break;
                    case 'outputMode':
                        $this->outputMode = $val;
                        break;
                    case 'jsfile':
                        $jsfile = $val;
                        break;
                    case 'filepath':
                        $this->filepath = $val;
                        break;
                    default:
                        $this->raiseError(
                            HTML_JAVASCRIPT_TOOLSBAR_INVALID_OPTION,
                            'setOptions'
                        );
                }
            }
            if($this->getOutputMode()==HTML_JAVASCRIPT_OUTPUT_FILE
                && !empty($jsfile)
            ){
                return $this->raiseError(
                    HTML_JAVASCRIPT_TOOLSBAR_ARG_NOT_ALLOWED,
                    'setOptions'
                );
            }
        } else {
            return $this->raiseError(
                HTML_JAVASCRIPT_TOOLSBAR_INVALID_ARG,
                'setOptions'
            );
        }
    }

    // }}} setOptions
    // {{{ setFromXML

    /**
     * Set the toolbar using a XML definition
     * This function uses the PEAR::Tree module
     * Not yet implemented
     *
     * @param   string  XML data or filepath
     * @param   bool    $xml is a filepath or not
     * @access public
     * @return mixed return
     */
    function setFromXML($xml, $file=true)
    {
        return $this->raiseError(HTML_JAVASCRIPT_NOT_IMPLEMENTED);
        /** Tree module */
        include_once 'Tree/Tree.php';
        if($file){
            $xmlTree = new Tree(array('XML',$xml));
        }
    }

    // }}} setFromXML
    // {{{ _addText
    /**
     * Comments
     *
     * @param  string  $param coment
     * @access private
     * @return mixed return
     */
    function _addText($name, $properties)
    {
        $pos = $this->_elems++;
        $this->elements[$name] = array();
        foreach($properties as $key=>$prop){
            $this->elements[$name][$key] = $prop;
        }
    }

    // }}} _addText
    // {{{ _addImage
    /**
     * Comments
     *
     * @param  string  $param coment
     * @access private
     * @return mixed return
     */
    function _addImage($name, $properties)
    {
        $pos = $this->_elems++;
        $this->elements[$name] = array();
        foreach($properties as $key=>$prop){
            $this->elements[$name][$key] = $prop;
        }
    }

    // }}} _addImage
    // {{{ _addSelect
    /**
     * Comments
     *
     * @param  string  $param coment
     * @access private
     * @return mixed return
     */
    function _addSelect($name, $properties)
    {
        $pos = $this->_elems++;
        $this->elements[$name] = array();
        foreach($properties as $key=>$prop){
            $this->elements[$name][$key] = $prop;
        }
    }

    // }}} _addSelect
    // {{{ out

    /**
     * Comments
     *
     * @param  string  $param coment
     * @access private
     * @return mixed return
     */
    function out(  )
    {
        return $this->_out($this->get());
    }

    // }}} out
    // {{{ get

    /**
     * Comments
     *
     * @param  string  $param coment
     * @access public
     * @return mixed return
     */
    function get(  )
    {
        $ret = HTML_JAVASCRIPT_NL;
        $ret .= HTML_Javascript_Convert::convertArrayToProperties(
                    $this->elements, $this->name.'_data', true
                    );
        $ret .= HTML_JAVASCRIPT_NL.
        $ret .= HTML_Javascript_Convert::convertArrayToProperties(
                    $this->positions, $this->name.'_position', true
                    );
        $ret .= HTML_JAVASCRIPT_NL;
        $ret .= HTML_Javascript_Convert::convertVar(
                    $this->name,$this->name.'_name'
                    );
        $ret .= HTML_JAVASCRIPT_NL;
        $ret .= HTML_Javascript_Convert::convertArrayToProperties(
                    $this->options,$this->name.'_options'
                    );
        $ret .= HTML_JAVASCRIPT_NL;
        return $ret;
    }

    // }}} get
}
?>
