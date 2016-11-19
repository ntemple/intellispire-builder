<?php



class ContextPager {
	
	var $pagerOptions;
	
	function ContextPager($mode='Sliding',$delta=2,$perPage=15){
		$this->pagerOptions = array(
             'mode'    => $mode,
             'delta'   => $delta,
             'perPage' => $perPage,
             );

		
	}
	function get_paged_data($db,$query){
	    
		$paged_data = $this->Pager_Wrapper_MDB2($db, $query, $this->pagerOptions);
		return $paged_data;		
	}
	
	function rewriteCountQuery($sql)
   {
    if (preg_match('/^\s*SELECT\s+\bDISTINCT\b/is', $sql) || preg_match('/\s+GROUP\s+BY\s+/is', $sql)) {
        return false;
    }
    $open_parenthesis = '(?:\()';
    $close_parenthesis = '(?:\))';
    $subquery_in_select = $open_parenthesis.'.*\bFROM\b.*'.$close_parenthesis;
    $pattern = '/(?:.*'.$subquery_in_select.'.*)\bFROM\b\s+/Uims';
    if (preg_match($pattern, $sql)) {
        return false;
    }
    $subquery_with_limit_order = $open_parenthesis.'.*\b(LIMIT|ORDER)\b.*'.$close_parenthesis;
    $pattern = '/.*\bFROM\b.*(?:.*'.$subquery_with_limit_order.'.*).*/Uims';
    if (preg_match($pattern, $sql)) {
        return false;
    }
    $queryCount = preg_replace('/(?:.*)\bFROM\b\s+/Uims', 'SELECT COUNT(*) FROM ', $sql, 1);
    list($queryCount, ) = preg_split('/\s+ORDER\s+BY\s+/is', $queryCount);
    list($queryCount, ) = preg_split('/\bLIMIT\b/is', $queryCount);
    return trim($queryCount);
}

/**
 * @param object PEAR::DB instance
 * @param string db query
 * @param array  PEAR::Pager options
 * @param boolean Disable pagination (get all results)
 * @param integer fetch mode constant
 * @param mixed  parameters for query placeholders
 *        If you use placeholders for table names or column names, please
 *        count the # of items returned by the query and pass it as an option:
 *        $pager_options['totalItems'] = count_records('some query');
 * @return array with links and paged data
 */
function Pager_Wrapper_DB(&$db, $query, $pager_options = array(), $disabled = false, $fetchMode = DB_FETCHMODE_ASSOC, $dbparams = null)
{
   if (!array_key_exists('totalItems', $pager_options)) {
        //  be smart and try to guess the total number of records
        if ($countQuery = rewriteCountQuery($query)) {
            $totalItems = $db->getOne($countQuery, $dbparams);
            if (PEAR::isError($totalItems)) {
                return $totalItems;
            }
        } else {
            $res =& $db->query($query, $dbparams);
            if (PEAR::isError($res)) {
                return $res;
            }
            $totalItems = (int)$res->numRows();
            $res->free();
        }
        $pager_options['totalItems'] = $totalItems;
    }
    require_once 'HTML/Pager/Pager.php';
    $pager = Pager::factory($pager_options);

    $page = array();
    $page['totalItems'] = $pager_options['totalItems'];
    $page['links'] = $pager->links;
    $page['page_numbers'] = array(
        'current' => $pager->getCurrentPageID(),
        'total'   => $pager->numPages()
    );
    list($page['from'], $page['to']) = $pager->getOffsetByPageId();

    $res = ($disabled)
        ? $db->limitQuery($query, 0, $totalItems, $dbparams)
        : $db->limitQuery($query, $page['from']-1, $pager_options['perPage'], $dbparams);

    if (PEAR::isError($res)) {
        return $res;
    }
    $page['data'] = array();
    while ($res->fetchInto($row, $fetchMode)) {
       $page['data'][] = $row;
    }
    if ($disabled) {
        $page['links'] = '';
        $page['page_numbers'] = array(
            'current' => 1,
            'total'   => 1
        );
    }
    return $page;
}

/**
 * @param object PEAR::MDB instance
 * @param string db query
 * @param array  PEAR::Pager options
 * @param boolean Disable pagination (get all results)
 * @param integer fetch mode constant
 * @return array with links and paged data
 */
function Pager_Wrapper_MDB(&$db, $query, $pager_options = array(), $disabled = false, $fetchMode = MDB_FETCHMODE_ASSOC)
{
    if (!array_key_exists('totalItems', $pager_options)) {
        //be smart and try to guess the total number of records
        if ($countQuery = rewriteCountQuery($query)) {
            $totalItems = $db->queryOne($countQuery);
            if (PEAR::isError($totalItems)) {
                return $totalItems;
            }
        } else {
            $res = $db->query($query);
            if (PEAR::isError($res)) {
                return $res;
            }
            $totalItems = (int)$db->numRows($res);
            $db->freeResult($res);
        }
        $pager_options['totalItems'] = $totalItems;
    }
    require_once 'HTML/Pager/Pager.php';
    $pager = Pager::factory($pager_options);

    $page = array();
    $page['totalItems'] = $pager_options['totalItems'];
    $page['links'] = $pager->links;
    $page['page_numbers'] = array(
        'current' => $pager->getCurrentPageID(),
        'total'   => $pager->numPages()
    );
    list($page['from'], $page['to']) = $pager->getOffsetByPageId();

    $res = ($disabled)
        ? $db->limitQuery($query, null, 0, $totalItems)
        : $db->limitQuery($query, null, $page['from']-1, $pager_options['perPage']);

    if (PEAR::isError($res)) {
        return $res;
    }
    $page['data'] = array();
    while ($row = $db->fetchInto($res, $fetchMode)) {
        $page['data'][] = $row;
    }
    if ($disabled) {
        $page['links'] = '';
        $page['page_numbers'] = array(
            'current' => 1,
            'total'   => 1
        );
    }
    return $page;
}

/**
 * @param object PEAR::MDB2 instance
 * @param string db query
 * @param array  PEAR::Pager options
 * @param boolean Disable pagination (get all results)
 * @param integer fetch mode constant
 * @return array with links and paged data
 */
function Pager_Wrapper_MDB2(&$db, $query, $pager_options = array(), $disabled = false, $fetchMode = MDB2_FETCHMODE_ASSOC)
{
    if (!array_key_exists('totalItems', $pager_options)) {
        //be smart and try to guess the total number of records
      // $rs = $db->query($query);
       $totalItems = $db->countresult($query);
        $pager_options['totalItems'] = $totalItems;
    }
    require_once 'HTML/Pager/Pager.php';
    $pager = Pager::factory($pager_options);

    $page = array();
    $page['links'] = $pager->links;
    $page['totalItems'] = $pager_options['totalItems'];
    $page['page_numbers'] = array(
        'current' => $pager->getCurrentPageID(),
        'total'   => $pager->numPages()
    );
    list($page['from'], $page['to']) = $pager->getOffsetByPageId();
    $page['limit'] = $page['to'] - $page['from'] +1;
    if (!$disabled) {
        //$db->setLimit($pager_options['perPage'], $page['from']-1);
        $query .= ' LIMIT ' . ($page['from']-1) . ' , ' . $pager_options['perPage'];
       
    }
    $page['data'] = $db->get_results($query);
    if (PEAR::isError($page['data'])) {
        return $page['data'];
    }
    if ($disabled) {
        $page['links'] = '';
        $page['page_numbers'] = array(
            'current' => 1,
            'total'   => 1
        );
    }
    return $page;
}

/**
 * @param object PEAR::DataObject instance
 * @param array  PEAR::Pager options
 * @param boolean Disable pagination (get all results)
 * @return array with links and paged data
 * @author Massimiliano Arione <garak@studenti.it>
 */
function Pager_Wrapper_DBDO(&$db, $pager_options = array(), $disabled = false)
{
    if (!array_key_exists('totalItems', $pager_options)) {
        $totalItems = $db->count();
        $pager_options['totalItems'] = $totalItems;
    }
    require_once 'HTML/Pager/Pager.php';
    $pager = Pager::factory($pager_options);

    $page = array();
    $page['links'] = $pager->links;
    $page['totalItems'] = $pager_options['totalItems'];
    $page['page_numbers'] = array(
        'current' => $pager->getCurrentPageID(),
        'total'   => $pager->numPages()
    );
    list($page['from'], $page['to']) = $pager->getOffsetByPageId();
    $page['limit'] = $page['to'] - $page['from'] + 1;
    if (!$disabled) {
        $db->limit($page['from'] - 1, $pager_options['perPage']);
    }
    $db->find();
    while ($db->fetch()) {
        $db->getLinks();
        $page['data'][] = $db->toArray('%s', true);
    }
    return $page;
}

/**
 * @param object PHP Eclipse instance
 * @param string db query
 * @param array  PEAR::Pager options
 * @param boolean Disable pagination (get all results)
 * @return array with links and paged data
 * @author Matte Edens <matte@arubanetworks.com>
 * @see http://sourceforge.net/projects/eclipselib/
 */
function Pager_Wrapper_Eclipse(&$db, $query, $pager_options = array(), $disabled = false)
{
    if (!$disabled) {
        require_once(ECLIPSE_ROOT . 'PagedQuery.php');
        $query =& new PagedQuery($db->query($query), $pager_options['perPage']);
        $totalrows = $query->getRowCount();
        $numpages  = $query->getPageCount();
        $whichpage = isset($_GET[$pager_options['urlVar']]) ? (int)$_GET[$pager_options['urlVar']] - 1 : 0;
        if ($whichpage >= $numpages) {
            $whichpage = $numpages - 1;
        }
        $result = $query->getPage($whichpage);
    } else {
        $result    = $db->query($query);
        $totalrows = $result->getRowCount();
        $numpages  = 1;
    }
    if (!$result->isSuccess()) {
        return PEAR::raiseError($result->getErrorMessage());
    }
    if (!array_key_exists('totalItems', $pager_options)) {
        $pager_options['totalItems'] = $totalrows;
    }

    $page = array();
    require_once(ECLIPSE_ROOT . 'QueryIterator.php');
    for ($it =& new QueryIterator($result); $it->isValid(); $it->next()) {
        $page['data'][] =& $it->getCurrent();
    }
    require_once 'HTML/Pager/Pager.php';
    $pager = Pager::factory($pager_options);

    $page['links']        = $pager->links;
    $page['totalItems']   = $pager_options['totalItems'];
    $page['page_numbers'] = array(
        'current' => $pager->getCurrentPageID(),
        'total'   => $numpages
    );
	$page['perPageSelectBox'] = $pager->getperpageselectbox();
    list($page['from'], $page['to']) = $pager->getOffsetByPageId();
    $page['limit'] = $page['to'] - $page['from'] +1;
    if ($disabled) {
        $page['links'] = '';
        $page['page_numbers'] = array(
            'current' => 1,
            'total'   => 1
        );
    }
    return $page;
}

}

?>