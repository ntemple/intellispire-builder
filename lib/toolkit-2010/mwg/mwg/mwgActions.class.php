<?php
/**
 * Sabrayla PHP Classes and Functions
 *
 * @category   sabrayla
 * @package    sabrayla
 * @author     Nick Temple <Nick.Temple@intellispire.com>
 * @copyright  2002-2006 Intellispire
 * @license    http://www.sabrayla.com/license/1_0.txt Sabrayla License 1.0
 * @version    SVN: $Id: mwgActions.class.php 21 2013-03-15 19:35:01Z ntemple $
 * @since      File available since Release 0.1
 */

/** ensure this file is being included by a parent file */
defined( '_SB_VALID_' ) or die( 'Direct Access to this location is not allowed.' );
# require_once('sabrayla.class.php');

class mwgActions {
  var $_template = null;

  function doView(mwgRequest $request, mwgResponse $response) {
  }  
}




class basicClass /* extends Object */ {

  var $_request;
  var $_response;
  var $_template;

  function doView(mwgRequest $request, mwgResponse $response) {
     $this->_template = $request->_original_page;

     $response->_template = $this->_template;

     if (AUTOBUILD > 0) {
        $this->_buildtemplate($request, $response, 'doview.tpl.html');
     }
  }


  function _init(&$request, &$response, $template = NULL) {
     $this->_request  = $request;
     $this->_response = $response;

     if ($template == NULL) $template = $request->_original_page;
     $this->_template = $template;
     $response->_template = $template;
  }

/*
  function _buildtemplate($tpl) {
       sabrayla_log("_buildtemplate($tpl)");
       $outfile  = THEME_DIR . '/default/' . $this->_template;
       $srcdir = THEME_DIR . '/src/';
       if (! file_exists($outfile) && AUTOBUILD) {
                $ctx = new  Context($srcdir, NULL);
                $ctx->writeOutput($outfile, $tpl);
                return true;
         }
    }

*/
}

class defaultClass extends basicClass {

  var $_template = 'default';
  var $_table    = '';
  var $_menu     = NULL;
  var $_class    = '';
  var $_pkey     = 'id';

  # Here are our default SQL templates
  var $_sql_list = 'select * from {table}';

  function doView(mwgRequest $request, mwgResponse $response) {
     $this->doListView($request, $response);
  }

  # Return an array containing top-level submenu items
  function getMenu()   {  return NULL; }
  function getHead()   {  return ''; }
  function libMethod() {  return "inside libMethod\n";  }

  function doStore(&$request, &$response) {
    if ($request->req['error'] == '1') {
      $response->errors['error'] = 'An error occured.';
      $response->_template= 'test.html';
      return false;
    }

    return true;  # success
  }

  function doListView(mwgRequest $request, mwgResponse $response) {
        $response->_template = $this->_class . '_list.tpl.html';
        $this->_buildtemplate($request, $response, 'list.tpl.html');

        $sql = str_replace('{table}', $this->_table, $this->_sql_list);
        $items = $request->db->get_results($sql);
        $response->list = $items;

  }

  function _buildtemplate(mwgRequest $request, mwgResponse $response, $tpl) {

     if (! file_exists('templates/' . $response->_template) && AUTOBUILD) {
  	  	$ctx = new Context('templates/default', CACHE);
                $request->db->halt = false;
  	  	$fields = $request->db->get_results('show columns from ' . $this->_table);
  	  	$request->db->halt = true;
  	  	if ($fields == NULL) return false;

  	  	$ctx->ob = '{';
  	  	$ctx->cb = '}';

  	  	$ctx->fields = $fields;
  	  	$ctx->writeOutput('templates/', $response->_template, $tpl);
  	  	return true;
 	  }

  }

  function doReadView(mwgRequest $request, mwgResponse $response) {
  	  $response->_template = $this->_class . '_read.tpl.html';
  	  $this->_buildtemplate($request, $response, 'read.tpl.html');

      if ($request->req['id'] != '') {
        $row = $request->db->get_row('select * from ' . $this->_table . ' where id=? LIMIT 1', $request->req['id']);
        $response->setData($row);
      }
  }

  function doCreateView(mwgRequest $request, mwgResponse $response) {
  	  $response->_template = $this->_class . '_create.tpl.html';
  	  $this->_buildtemplate($request, $response, 'create.tpl.html');

      if (isset($request->req['id']) && $request->req['id'] != '') {
        $row = $request->db->get_row('select * from ' . $this->_table . ' where id=? LIMIT 1', $request->req['id']);
        $response->setData($row);
      }
  }

  function doCreateStore(mwgRequest $request, mwgResponse $response) {
    // TODO: Sanity checks
    $id = $request->db->store($this->_table, $request->req, $this->_pkey);
    $response->id = $id;
    $request->req['a'] = 'List';
    return true;
  }

  function doDeleteView(mwgRequest $request, mwgResponse $response) {
  	  $response->_template = $this->_class . '_delete.tpl.html';
  	  $this->_buildtemplate($request, $response, 'delete.tpl.html');

      if ($request->req['id'] != '') {
        $row = $request->db->get_row('select * from ' . $this->_table . ' where id=? LIMIT 1', $request->req['id']);
        $response->setData($row);
      }
  }

  function doDeleteStore(mwgRequest $request, mwgResponse $response) {
    if ($request->req['id'] != '') {
        $request->db->query_rw('delete from ' . $this->_table . ' where id=? limit 1', $request->req['id']);
    }
    $request->req['a'] = 'List';
    return true;
    # $this->doListView($request, $response);
  }

}
