<?php

require_once "phing/mappers/FileNameMapper.php";

/**
 * A mapper that makes those ugly DOS filenames.
 */
class VariableMapper implements FileNameMapper {

    private $from;
    private $to;

    public function setFrom($from) {  $this->from = $from; }
    public function setTo($to) { $this->to = $to; }

    /**
     * The main() method actually performs the mapping.
     *
     * In this case we transform the $sourceFilename into
     * a DOS-compatible name.  E.g.
     * ExtendingPhing.html -> EXTENDI~.DOC
     *
     * @param string $sourceFilename The name to be coverted.
     * @return array The matched filenames.
     */
    public function main($sourceFilename) {

        if ($this->from === null || $this->to == null) {
            throw new BuildException("IdentMapper error, to attribute not set");
        }

        $fname = $sourceFilename;

        $fname = str_replace($this->from, $this->to, $fname);
        $fname = str_replace(strtolower($this->from), strtolower($this->to), $fname);
        $fname = str_replace(strtoupper($this->from), strtoupper($this->to), $fname);

        return array($fname);
    }


}