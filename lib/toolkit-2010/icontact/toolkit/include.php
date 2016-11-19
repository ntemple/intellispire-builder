<?php
/**
 * Copyright (C) 2006 by Broadwick.
 * 
 * This would be some really fancy legalese if we were concerned about that sort of stuff right now.  Mainly,
 * we guarantee nothing.  You should use this at your own risk.  The most we can promise is that using
 * this won't cause God to kill kittens.
 * 
 * @version $Id: include.php 21 2013-03-15 19:35:01Z ntemple $
 * @package IcMain
 */

/**
 * This knows how to load all classes that start with Ic
 *
 * @param string $classname
 */
function __autoload($classname) {
	if(substr($classname,0,2) == "Ic") {
		$filename = "classes/" . $classname . ".php";
		require $filename;
	}
}

?>