<?php
/**
* @version    $Id: mwgMember.class.php 21 2013-03-15 19:35:01Z ntemple $
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

class mwgMember {

  var $id; // user id
  var $record;
  var $levels;

  function __construct($id = '') {
    if ($id) {
      $this->id = $id;    
      $this->_load();
    } else {
      // legacy session
      if ($SESSION['sess_id']) {
        $this->record =  MWG::getDb()->get_row('select * from members where mdid=?', $SESSION['sess_id']);
        if ($this->record) $this->id = $record['id'];
      }    
    } 

    if (!$this->id) {
      // This is a guest user, we need to special case.
    }    
  }
  
  function __get($name) {
    if (!$this->record) return null;
    
    if (isset($this->record[$name])) {
      return $this->record($name);
    } else {
      return null;
    }
  }

  protected function _load() {
    $this->record = MWG::getDb()->get_row('select * from members where id=?', $this->id);    
  }

  /*** Membership Levels ***/
  
  
  /**
  * Return current member level
  */
  function getRankingLevel() {
    if (!$this->record) return 0; // Guest is member of nothing
    return ($this->record['membership_id']);    
  }
      
  function getLevels($force = false) {
    if (!$this->record) return array(); // Guest is member of nothing
    if ($force) unset($this->levels);
    
    if (! $this->levels) {
      $raw_levels = explode(",", $this->record['history']);
      $this->levels = $this->dedupe($raw_levels);
    }
    return $this->levels;
  }
  
  function addLevel($membership_id) {
    if (!$this->record) return array(); // Guest is member of nothing

    $levels = $this->getLevels();
    $levels[] = $membership_id;
    $this->storeLevels($levels);       
    return $this->getLevels();    
  }

  function removeLevel($membership_id) {
    if (!$this->record) return array(); // Guest is member of nothing

    $raw_levels = $this->getLevels();
    $levels = $this->dedupe($raw_levels, $membership_id);
    $this->storeLevels($levels);
    return $this->getLevels();
  }
  
  /**
  * Remove current top rank and add a new one.
  * The new top rank becomes the greatest of 
  * allowed membership levels.
  * 
  * @param mixed $membership_id
  */
  function changeLevel($membership_id) {
    if (!$this->record) return array(); // Guest is member of nothing
    
    $this->removeLevel($this->record['membership_id']);
    $this->addLevel($membership_id);
    return $this->getLevels();    
  }
  
  /**
  * Promote member to the next level.
  * By default, you keep access to the current level.
  * 
  * @param mixed $ignore array of levels to ignore during calculations.
  * @param mixed $keep  default, keep access to current level. Pass false to remove access
  */
  
  function promoteLevel($ignore = array(), $keep = true) {
    list($prev, $next) = $this->getBoundingLevels($ignore);

    if ($keep) {
      $levels = $this->addLevel($next);
    } else {
      $levels = $this->changeLevel($next);
    }        
    return $levels;
  }
  
  /**
  * Demote level, removing access to the current ranking level.
  * 
  * @param mixed $ignore
  */
  
  function demoteLevel($ignore = array()) {
    list($prev, $next) = $this->getBoundingLevels($ignore);
    return $this->changeLevel($prev);   
  }

  /**
  * returns the previous and next levels for the given member,
  * ignoring ignored and inactive ranks
  * 
  * NOTE: if the current level itself is inactive or ignored, 
  * then the "promotions" will be to the next ACTIVE level
  * 
  * If there is no previous or next, the current membership_id is returned
  * 
  * @param array $ignore
  * @param mixed $membership_id
  * @return mixed
  */
  
  function getBoundingLevels($ignore = array(), $membership_id = 0 ) {
    $db = MWG::getDb();

    if (! $membership_id) {
      $membership_id = $this->record['membership_id'];    
    }
    
    $inactive = $db->get_column("select id from membership where active=0");
    $ignore = array_merge($inactive, $ignore);

    $ranks = $db->get_select("select rank,id from membership order by rank asc");
    $current_rank = $db->get_value("select rank from membership where id=?", $membership_id);
    
    $next  = false;
    $prev  = $membership_id;
    $prev_id = $membership_id;
    $next_id = $membership_id;
       
    foreach ($ranks as $rank => $id) {
      if ($id != $membership_id) // Don't ignore self
        if ( in_array($id, $ignore)  ) continue; // Skip ignored ranks, UNLESS this rank is included
      
      if ($next) {
        $next = false;
        $next_id = $id; // next valid membership id
      }                 

      if ($current_rank == $rank) {
        $prev_id = $prev; // previous valid membership id
        $next = true;
      }      
      $prev = $id;
    }
    return array($prev_id, $next_id);
  }
  
  
  /**
  * Deduplicate a numeric array
  * optional remove remove a specific level from the array 
  * during the process
  * 
  * @param mixed $raw_levels
  * @param mixed $remove       optional level to remove 
  * @return array
  */

  function dedupe($raw_levels, $remove = 0) {
    // de-duplicate and weed out blanks
    $current_levels = array();
    foreach ($raw_levels as $level) {
      if ($level) $current_levels[$level] = true;
    }
    if (($remove > 0) && isset($current_levels[$remove])) {
      unset($current_levels[$remove]);
    }
    $levels = array_keys($current_levels);
    return $levels;        
  }

  /**
  * store the new, updated levels array
  * Note that we assume that rank may not be unique, this the somewhat convaluted rank sorting logic
  * 
  * @param array $levels new levels array
  */
  function storeLevels($levels) {

    // Levels have changed (added or deleted)
    // Set the new rank and save the record
    $levels = $this->dedupe($levels);
    $in = implode(",", $levels);
    $ranks = MWG::getDb()->get_select("select id,rank from membership where id in($in) order by rank asc");

    // Loop through to create the levels in rank order, and determine the max rank id.    
    // Our main membership id is the maximum we are allowed access to.
    // Note it IS possible that our main membership id is inactive. 
    // In this case, results become undefined. 
    // Don't deactivate membership levels in which you have members!

    $levels = array(); // Clear the array
    foreach ($ranks as $id => $rank) {
      $levels[] = $id;
      $membership_id = $id;
    }
    $this->record['history'] =   implode(',', $levels) . ","; // Extra comma added for backward compatibility until we fix the legacy data handling routines.
    $this->record['membership_id'] = $membership_id;
    MWG::getDb()->query('update members set membership_id=?, history=? where id=?', $this->record['membership_id'], $this->record['history'], $this->id);
    $this->levels = ''; // Remove levels cache
    return $this->getLevels(true);
  }

  function dump() {
    print_r($this);
    print_r($SESSION);
  }
}