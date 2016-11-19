<?php

    #
    # A simple Atom parser, compatible with PEAR's XML::RSS
    # Requires PEAR's XML::Parser
    #
    # (C)2004 Cal Henderson, <cal@iamcal.com>
    #

require_once 'XML/Parser.php';

class XML_Atom extends XML_Parser
{
    var $insideTag = '';
    var $activeTag = '';
    var $channel = array();
    var $items = array();
    var $item = array();
    var $insideEntry = 0;


    function XML_Atom($handle = '')
    {
        $this->XML_Parser();

        if (@is_resource($handle)) {
            $this->setInput($handle);
        } elseif ($handle != '') {
            $this->setInputFile($handle);
        } else {
            $this->raiseError('No filename passed.');
        }
    }

    function startHandler($parser, $element, $attribs)
    {
        switch ($element) {
            case 'FEED':
            case 'ENTRY':
                $this->insideTag = $element;
                break;

            case 'CONTENT':
            case 'SUMMARY':
                if ($this->insideTag == 'ENTRY'){
                    $this->insideEntry = 1;
                }
                   break;

            case 'LINK':
                if ($this->insideTag == 'ENTRY'){
                    if ($attribs[REL]=='alternate' || !$attribs[REL]){
                        $this->_add('item', 'link', $attribs[HREF]);
                    }
                }
                break;

            default:
                $this->activeTag = $element;
        }
    }

    function endHandler($parser, $element)
    {
        if ($element == $this->insideTag) {
            $this->insideTag = '';
            $this->struct[] = array_merge(array('type' => strtolower($element)), $this->last);
        }

        if ($element == 'ENTRY') {
            $this->items[] = $this->item;
            $this->item = '';
        }

        if ($element == 'CONTENT' || $element == 'SUMMARY') {
            $this->insideEntry = 0;
        }

        $this->activeTag = '';
    }

    function cdataHandler($parser, $cdata)
    {
        switch ($this->insideTag) {

            // Grab general channel information
            case 'FEED':
                switch ($this->activeTag) {
                    case 'TITLE':
                        $this->_add('channel', strtolower($this->activeTag), $cdata);
                        break;
                }
                break;

            // Grab item information
            case 'ENTRY':
                switch ($this->activeTag) {
                    case 'TITLE':
                        $this->_add('item', strtolower($this->activeTag), $cdata);
                        break;

                    case 'CONTENT':
                    case 'SUMMARY':
                        $this->_add('item', 'description', $cdata);
                        break;
                }
                break;


        }   

        if ($this->insideEntry){
            $this->_add('item', 'description', $cdata);
        }
   
    }

    function defaultHandler($parser, $cdata)
    {
        return;
    }

    function _add($type, $field, $value)
    {
        if (empty($this->{$type}) || empty($this->{$type}[$field])) {
            $this->{$type}[$field] = $value;
        } else {
            $this->{$type}[$field] .= $value;
        }

        $this->last = $this->{$type};
    }

    function getStructure()
    {
        return (array)$this->struct;
    }

    function getChannelInfo()
    {
        return (array)$this->channel;
    }

    function getItems()
    {
        return (array)$this->items;
    }


}
?>
