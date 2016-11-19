<?php
/* SVN FILE: $Id: manifest.class.php 21 2013-03-15 19:35:01Z ntemple $*/
/**
 *
 * ISN - Intellispire Network Client Toolkit
 * Copyright (c) 2008 Nick Temple, Intellispire
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License. (and no other version)
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category   ISN
 * @package    Client
 * @author     Nick Temple <Nick.Temple@intellispire.com>
 * @copyright  2008 Intellispire
 * @license    LGPL 2.1
 * @version    SVN: $Id: manifest.class.php 21 2013-03-15 19:35:01Z ntemple $
 * @since      File available since Release 1.0
 *
 */

require_once('spyc.php');

class Manifest {

  var $location;
  var $manifest;
  var $sect;

  function __construct($section = 'mwg') {
     $this->sect = 'mwg'; # $section;
  }


  function getSoftwareList($sect = null) {
    if (!$sect) $sect = $this->sect;
    return $this->manifest['software'][$sect];
  }


  /**
  * @desc Load the manifest from disk.
  */
  function loadManifest($location, $force = false) {
    if (! file_exists($location)) {
      return null;
    }

    if ($location == $this->location && $force == false) {
      return $this->manifest;
    }
    $this->manifest =  Spyc::YAMLLoad($location);

    /* Normally, we use the iversion number to determine if
       to install a software package. For the upgrader, we need to use the version #.
       If no upgrade is needed, zero out the version number.
    */

    $swl = $this-> getSoftwareList();

    if ($this->isPro() && isset($swl['joomla.jupgrade'])) {
      if (! $this->needUpdate()) {
        $swl['joomla.jupgrade']['iversion'] = 0;
      }
    }

    return $this->manifest;
  }

  /**
  * @desc Get an array of items.
  * TODO: make more generic
  * TODO: allow sorting / filtering on the list
  */
  function getItems($limitstart = 0, $limit = 1000) {

    $allitems =  $this->getSoftwareList();
    if ($limit == 0) return $allitems;

    return array_slice($allitems, $limitstart, $limit, true);
  }

  function countItems() {
    $swl = $this->getSoftwareList();
    return count($swl);
  }

  function isPro() {
    if (isset($this->manifest['product']))
       if ($this->manifest['product'] == 'jpro')
         return true;
    return false;
  }

  function needUpdate() {
    $r = version_compare(JVERSION, $this->getLatestJoomlaVersion());

    if ($r < 0) return true;
    return false;

  }

  function getLatestJoomlaVersion() {
     if ( isset($this->manifest['software']['joomla.jupgrade'] ) )
       return  $this->manifest['software']['joomla.jupgrade']['version'];

     if (isset($this->manifest['jlatest']))
       return $this->manifest['jlatest'];

     return JVERSION; // in case we don't know
  }

  function getItemCount() {
    return countItems();
  }

  function zone_enabled($zoneid) {
    $zones = $this->manifest['zones'];
    if (! $zones) return false;
    if (in_array($zoneid, $zones)) return true;

    return false;
  }

  function getMessage() {
    if (isset($this->manifest['message'])) {
      return $this->manifest['message'];
    }
    return '';
  }

  function getItem($package) {
    // TODO: Cache
    # $remote_data = @file_get_contents('http://www.intellispire.com/network/server5/details.php?package=' . $package);
    # $remote_data = Spyc::YAMLLoad($remote_data);

    $swl = $this->getSoftwareList();
    $packagedata = $swl[$package];
    # $packagedata = array_merge($packagedata, $remote_data);

    # $description = trim($packagedata['description']);

    # if (strpos($description, 'http:') === 0) {
    #   $page =  file_get_contents($description);
    #   $packagedata['description'] = $page;
    # }

    return  $packagedata;
  }

  function getSupportTypes() {
    return  $this->manifest['support'];
  }

  // Package functions

  function getPrice($package) {
    $swl = $this->getSoftwareList();
    if (isset($swl[$package]['credits'])) {
      return $swl[$package]['credits'];
    } else {
      return 0;
    }

  }

  /**
  * check to see if we can update the installed version
  *
  * @param mixed $package
  * @param mixed $installed_version
  * @return boolean
  */
  function getUpdateVersion($package, $installed_version) {

    $swl = $this->getSoftwareList();

    if (isset($swl[$package]['updates'])) {
      $updates = $swl[$package]['updates'];
      if (in_array($installed_version, $updates)) {
        return true;
      }
    }
    return false;
  }

  function hasBeenPaidFor($package) {
    if ($this->getPrice($package) == 0) {
      return true;
    } else {
      return false;
    }
  }

  function getSerial($package) {
     $swl = $this->getSoftwareList();

     if (isset($swl[$package]['iversion'])) {
       $iversion = $swl[$package]['iversion'];
       return $iversion;
     } else {
        return 0;
     }
  }

  function getPlatforms($package) {
    $swl = $this->getSoftwareList();
    return $swl[$package]['compatibility'];
  }

  function getUpdateCheck($package) {
    $swl = $this->getSoftwareList();
      if (isset($swl[$package]['updatecheck'])) {
         $extension = $swl[$package]['updatecheck'];
      } else {
        $extension = null;
      }
      return $extension;
  }

  function getConfirmation($package, $value = '') {
    $swl = $this->getSoftwareList();
     if (isset($swl[$package]['confirm'])) {
         $value = $swl[$package]['confirm'];
     }
     return $value;
  }

  function getStabilityHTML($package) {

    $swl = $this->getSoftwareList();

    if (isset($swl[$package]['stability'])) {
         $stability = $swl[$package]['stability'];
         $level = $stability['level'];
         if (! isset($stability['msg'])) {
            $stability['msg'] = '';
         }
    } else {
        $stability['level'] = 'Good';
        $stability['msg'] = '';
    }
    return $this->createStabilityMessage($stability);
  }

  private function createStabilityMessage($stability) {
    switch ($stability['level']) {
       case 'Excellent':  $title = 'Well supported, no known issues.'; $color = 'green'; break;
       case 'Fair':       $title = 'Some problems are known.'; $color = 'black'; break;
       case 'Poor':       $title = 'Some problems may make this component unuseable.'; $color = 'red';  break;
       case 'Good':       $title = 'Well supported, no known issues.'; $color = 'green';  break;
       default:   $title = 'No major issues.'; $color = 'block'; $text= 'Good'; break;
    }
    if ($stability['msg'] == '') { $stability['msg'] = $title; }
    $stability['color'] = $color;
    return $stability;
  }

}
