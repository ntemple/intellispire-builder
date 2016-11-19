<?php
/**
 * @desc MySQL Database rows pagging
 */
class mysqlPaging {
	var $max;
	var $query;
	var $dataQuery;
	var $start;
	var $page;
	var $total;
	var $pageNum;
	var $link;
	
	/**
	 * @param String $query
	 * @param Integer $max
	 * @param Integer $link
	 * @return void
	 */
	function mysqlPaging($query, $max, $link) {
		$this->query=$query;
		$this->max=$max;
		$this->link=$link;
		if (isset($_GET['page'])) {
			$this->page = $_GET['page'];
		} else {
			$this->page = 1;
		}
		$this->total = mysql_num_rows($this->link->query($this->query));
		$this->start = ($this->max*$this->page)-$this->max;
		if ($this->total<=$this->max) {
			$this->pageNum = 1;
		} elseif (($this->total%$this->max)==0) {
			$this->pageNum=$this->total/$this->max;
		} else {
			$this->pageNum=$this->total/$this->max+1;
		}
		$this->dataQuery=$this->query." LIMIT ".$this->start.", ".$this->max;			
	}
	
	/**
	 * Prints page numbers
	 * @return void
	 */
	function printPagesNums($moreparams) {
		print "<p>\n";
		$exp = isset($moreparams) && !empty($moreparams)?"&$moreparams":"";
		for ($pageId=1; $pageId<=$this->pageNum; $pageId++) {
			if ($pageId != $this->page) {
				print '<a href="'.$_SERVER['PHP_SELF'].'?page='.$pageId.$exp.'">'.$pageId.'</a> | '."\n";
			} else {
				print '<strong>'.$pageId.'</strong> | '."\n";
			}
		}
		print "</p>\n";				
	}
	
	/**
	 * Returns Prepared MySQL Query
	 * @return MySQL Query
	 */
	function returnQuery() {
		return $this->dataQuery;
	}
}
?>