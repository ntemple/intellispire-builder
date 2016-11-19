<?php
/* SVN FILE: $Id: wordtracker.class.php 21 2013-03-15 19:35:01Z ntemple $*/
/**
* Copyright (c)2008 Intellispire
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
* @category   API
* @package    Wordtracker
* @author     Nick Temple <Nick.Temple@intellispire.com>
* @copyright  2006-2008 Intellispire
* @license    MIT: http://www.opensource.org/licenses/mit-license.php
* @version    SVN: $Id: wordtracker.class.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*
* Requires PHP 5+
*/

class wordtracker {
    var $id;
    var $client;
    var $lasterror;
    
    // Set these before individual calls to override
    var $max = 0;
    // all phrases by default
    var $adult_filter         = 'remove_offensive';
    // off, remove_dubious, remove_offensive, adult_only
    var $include_plurals      = true;
    // true, false
    var $include_misspellings = false;
    // true, false
    var $case                 = 'case_distinct';
    // case_distinct, cast_folded, case_sensitive
    var $timeout              = 15;
    
    
    
    function wordtracker($id, $server)
    {
        $this->id = $id;
        $this->client = new IXR_Client($server);
    }
    
    function ping()
    {
        if (! $this->client->query('ping', $this->id)) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
    
    function query_permissions()
    {
        if (! $this->client->query('query_permissions', $this->id)) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
    
    function query_version()
    {
        if (! $this->client->query('query_version')) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
    
    function query_balance()
    {
        if (! $this->client->query('query_balance', $this->id)) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
    
    function get_total_searches()
    {
        if (! $this->client->query('get_total_searches', $this->id)) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
    
    function get_plurals($kws)
    {
        if (! is_array($kws)) {
            $kws = explode(',', $kws);
        }
        
        if (! $this->client->query('get_plurals',
        $this->id,
        $kws
        )
        ) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
    function get_exact_phrase_popularity($kws)
    {
        if (! is_array($kws)) {
            $kws = explode(',', $kws);
        }
        
        if (! $this->client->query('get_exact_phrase_popularity',
        $this->id,
        $kws,
        $this->case,
        $this->include_misspellings,
        $this->include_plurals,
        $this->adult_filter,
        $this->max,
        $this->timeout
        )
        ) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
    function get_lateral_keyphrases($kws)
    {
        if (! is_array($kws)) {
            $kws = explode(',', $kws);
        }
        
        if (! $this->client->query('get_lateral_keyphrases',
        $this->id,
        $kws,
        $this->include_plurals,
        $this->adult_filter,
        $this->max,
        $this->timeout
        )
        ) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
    
    function get_thesaurus_keyphrases($kws, $depth=1)
    {
        if (! is_array($kws)) {
            $kws = explode(',', $kws);
        }
        
        if (! $this->client->query('get_thesaurus_keyphrases',
        $this->id,
        $kws,
        $this->include_plurals,
        $this->adult_filter,
        $depth,
        $this->max
        )
        ) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
    
    
    function get_embedded_phrase_popularity($kws)
    {
        if (! is_array($kws)) {
            $kws = explode(',', $kws);
        }
        
        if (! $this->client->query('get_embedded_phrase_popularity',
        $this->id,
        $kws,
        $this->case,
        $this->include_misspellings,
        $this->include_plurals,
        $this->adult_filter,
        $this->max,
        $this->timeout
        )
        ) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
    
    function get_all_words_popularity($kws)
    {
        if (! is_array($kws)) {
            $kws = explode(',', $kws);
        }
        
        if (! $this->client->query('get_all_words_popularity',
        $this->id,
        $kws,
        $this->case,
        $this->include_misspellings,
        $this->include_plurals,
        $this->adult_filter,
        $this->max,
        $this->timeout
        )
        ) {
            $this->lasterror = $this->client->getErrorMessage();
            return false;
        }
        return $this->client->getResponse();
    }
    
}
/* end wordtracker */

?>
