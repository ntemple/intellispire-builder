<?php

date_default_timezone_set('America/New_York');

require_once "phing/Task.php";

class date extends Task {

    private $propertName = 'date.today';

    public function setPropertyName($str) {
        $this->propertyName = $str;
    }

    public function getPropertyName() {
        return $this->propertyName;
    }

    /**
     * The init method: Do init steps.
     */
    public function init() {
      // nothing to do here
    }

    /**
     * The main entry point method.
     */
    public function main() {
      $this->project->setProperty($this->getPropertyName(), date("Y-m-d"));
    }
}
