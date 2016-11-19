<?php
/* SVN FILE: $Id: wordtracker.client.php 21 2013-03-15 19:35:01Z ntemple $*/
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
* @version    SVN: $Id: wordtracker.client.php 21 2013-03-15 19:35:01Z ntemple $
* @since      File available since Release 0.1
*
* Requires PHP 5+
*/


require_once('../config.php');
require_once('wordtracker.class.php');

working();

function working()
{
    $wt = new wordtracker(API_WORDTRACKER_KEY, 'http://xmlrpc.wordtracker.com');
    
    $result = $wt->get_thesaurus_keyphrases('sword', 3);
    print_r($result);

    
    // use an array
    $result = $wt->get_lateral_keyphrases(array('sword') );
    print_r($result);
  
    $result = $wt->get_exact_phrase_popularity('sword');
    print_r($result);
    
    $result = $wt->query_balance();
    print("Balance: $result\n");
}

