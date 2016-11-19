<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors:  nobody <nobody@localhost>                                  |
// +----------------------------------------------------------------------+
//
// $Id: Tokenizer.php 21 2013-03-15 19:35:01Z ntemple $
//
//  The Source Lex file. (Tokenizer.lex) and the Generated one (Tokenizer.php)
// You should always work with the .lex file and generate by
//
// #mono phpLex/phpLex.exe Tokenizer.lex
// The lexer is available at http://sourceforge.net/projects/php-sharp/
// 
// or the equivialant .NET runtime on windows...
//
//  Note need to change a few of these defines, and work out
// how to modifiy the lexer to handle the changes..
//
require_once 'HTML/Template/Flexy/Token.php';
define('HTML_TEMPLATE_FLEXY_TOKEN_NONE',1);
define('HTML_TEMPLATE_FLEXY_TOKEN_OK',2);
define('HTML_TEMPLATE_FLEXY_TOKEN_ERROR',3);
define("YYINITIAL"     ,0);
define("IN_SINGLEQUOTE"     ,   1) ;
define("IN_TAG"     ,           2)  ;
define("IN_ATTR"     ,          3);
define("IN_ATTRVAL"     ,       4) ;
define("IN_NETDATA"     ,       5);
define("IN_ENDTAG"     ,        6);
define("IN_DOUBLEQUOTE"     ,   7);
define("IN_MD"     ,            8);
define("IN_COM"     ,           9);
define("IN_DS",                 10);
define("IN_FLEXYMETHOD"     ,   11);
define("IN_FLEXYMETHODQUOTED"  ,12);
define("IN_FLEXYMETHODQUOTED_END" ,13);
define("IN_SCRIPT",             14);
define("IN_CDATA"     ,         15);
define("IN_DSCOM",              16);
define("IN_PHP",                17);
define('YY_E_INTERNAL', 0);
define('YY_E_MATCH',  1);
define('YY_BUFFER_SIZE', 4096);
define('YY_F' , -1);
define('YY_NO_STATE', -1);
define('YY_NOT_ACCEPT' ,  0);
define('YY_START' , 1);
define('YY_END' , 2);
define('YY_NO_ANCHOR' , 4);
define('YY_BOL' , 257);
define('YY_EOF' , 258);


class HTML_Template_Flexy_Tokenizer
{

    /**
    * ignoreHTML flag
    *
    * @var      boolean  public
    * @access   public
    */
    var $ignoreHTML = false;
    /**
    * ignorePHP flag - default is to remove all PHP code from template.
    * although this may not produce a tidy result - eg. close ?> in comments
    * it will have the desired effect of blocking injection of PHP from templates.
    *
    * @var      boolean  public
    * @access   public
    */
    var $ignorePHP = true;
    /**
    * the start position of a cdata block
    *
    * @var int
    * @access private
    */
    var $yyCdataBegin = 0;
     /**
    * the start position of a comment block
    *
    * @var int
    * @access private
    */
    var $yyCommentBegin = 0;
    /**
    * the name of the file being parsed (used by error messages)
    *
    * @var string
    * @access public
    */
    var $fileName;
    /**
    * the string containing an error if it occurs..
    *
    * @var string
    * @access public
    */
    var $error;
    /**
    * raise an error: = return an error token and set the error variable.
    *
    * 
    * @param   string           Error type
    * @param   string           Full Error message
    * @param   boolean          is it fatal..
    *
    * @return   int the error token.
    * @access   public
    */
    function raiseError($s,$n='',$isFatal=false) {
        $this->error = "ERROR $n in File {$this->fileName} on Line {$this->yyline} Position:{$this->yy_buffer_end}: $s\n";
        return HTML_TEMPLATE_FLEXY_TOKEN_ERROR;
    }
    /**
    * return text
    *
    * Used mostly by the ignore HTML code. - really a macro :)
    *
    * @return   int   token ok.
    * @access   public
    */
    function returnSimple() {
        $this->value = HTML_Template_Flexy_Token::factory('TextSimple',$this->yytext(),$this->yyline);
        return HTML_TEMPLATE_FLEXY_TOKEN_OK;
    }


    var $yy_reader;
    var $yy_buffer_index;
    var $yy_buffer_read;
    var $yy_buffer_start;
    var $yy_buffer_end;
    var $yy_buffer;
    var $yychar;
    var $yyline;
    var $yyEndOfLine;
    var $yy_at_bol;
    var $yy_lexical_state;

    function HTML_Template_Flexy_Tokenizer($data) 
    {
        $this->yy_buffer = $data;
        $this->yy_buffer_read = strlen($data);
        $this->yy_buffer_index = 0;
        $this->yy_buffer_start = 0;
        $this->yy_buffer_end = 0;
        $this->yychar = 0;
        $this->yyline = 0;
        $this->yy_at_bol = true;
        $this->yy_lexical_state = YYINITIAL;
    }

    var $yy_state_dtrans = array  ( 
        0,
        212,
        35,
        125,
        234,
        235,
        236,
        237,
        54,
        65,
        244,
        246,
        265,
        279,
        280,
        288,
        83,
        85
    );


    function yybegin ($state)
    {
        $this->yy_lexical_state = $state;
    }



    function yy_advance ()
    {
        if ($this->yy_buffer_index < $this->yy_buffer_read) {
            return ord($this->yy_buffer{$this->yy_buffer_index++});
        }
        return YY_EOF;
    }


    function yy_move_end ()
    {
        if ($this->yy_buffer_end > $this->yy_buffer_start && 
            '\n' == $this->yy_buffer{$this->yy_buffer_end-1})
        {
            $this->yy_buffer_end--;
        }
        if ($this->yy_buffer_end > $this->yy_buffer_start &&
            '\r' == $this->yy_buffer{$this->yy_buffer_end-1})
        {
            $this->yy_buffer_end--;
        }
    }


    var $yy_last_was_cr=false;


    function yy_mark_start ()
    {
        for ($i = $this->yy_buffer_start; $i < $this->yy_buffer_index; $i++) {
            if ($this->yy_buffer{$i} == "\n" && !$this->yy_last_was_cr) {
                $this->yyline++; $this->yyEndOfLine = $this->yychar;
            }
            if ($this->yy_buffer{$i} == "\r") {
                $this->yyline++; $this->yyEndOfLine = $this->yychar;
                $this->yy_last_was_cr=true;
            } else {
                $this->yy_last_was_cr=false;
            }
        }
        $this->yychar = $this->yychar + $this->yy_buffer_index - $this->yy_buffer_start;
        $this->yy_buffer_start = $this->yy_buffer_index;
    }


    function yy_mark_end ()
    {
        $this->yy_buffer_end = $this->yy_buffer_index;
    }


    function  yy_to_mark ()
    {
        $this->yy_buffer_index = $this->yy_buffer_end;
        $this->yy_at_bol = ($this->yy_buffer_end > $this->yy_buffer_start) &&
            ($this->yy_buffer{$this->yy_buffer_end-1} == '\r' ||
            $this->yy_buffer{$this->yy_buffer_end-1} == '\n');
    }


    function yytext()
    {
        return substr($this->yy_buffer,$this->yy_buffer_start,$this->yy_buffer_end - $this->yy_buffer_start);
    }


    function yylength ()
    {
        return $this->yy_buffer_end - $this->yy_buffer_start;
    }


    var $yy_error_string = array(
        "Error: Internal error.\n",
        "Error: Unmatched input.\n"
        );


    function yy_error ($code,$fatal)
    {
        if (method_exists($this,'raiseError')) { 
 	    return $this->raiseError($code, $this->yy_error_string[$code], $fatal); 
 	}
        echo $this->yy_error_string[$code];
        if ($fatal) {
            exit;
        }
    }


    var  $yy_acpt = array (
        /* 0 */   YY_NOT_ACCEPT,
        /* 1 */   YY_NO_ANCHOR,
        /* 2 */   YY_NO_ANCHOR,
        /* 3 */   YY_NO_ANCHOR,
        /* 4 */   YY_NO_ANCHOR,
        /* 5 */   YY_NO_ANCHOR,
        /* 6 */   YY_NO_ANCHOR,
        /* 7 */   YY_NO_ANCHOR,
        /* 8 */   YY_NO_ANCHOR,
        /* 9 */   YY_NO_ANCHOR,
        /* 10 */   YY_NO_ANCHOR,
        /* 11 */   YY_NO_ANCHOR,
        /* 12 */   YY_NO_ANCHOR,
        /* 13 */   YY_NO_ANCHOR,
        /* 14 */   YY_NO_ANCHOR,
        /* 15 */   YY_NO_ANCHOR,
        /* 16 */   YY_NO_ANCHOR,
        /* 17 */   YY_NO_ANCHOR,
        /* 18 */   YY_NO_ANCHOR,
        /* 19 */   YY_NO_ANCHOR,
        /* 20 */   YY_NO_ANCHOR,
        /* 21 */   YY_NO_ANCHOR,
        /* 22 */   YY_NO_ANCHOR,
        /* 23 */   YY_NO_ANCHOR,
        /* 24 */   YY_NO_ANCHOR,
        /* 25 */   YY_NO_ANCHOR,
        /* 26 */   YY_NO_ANCHOR,
        /* 27 */   YY_NO_ANCHOR,
        /* 28 */   YY_NO_ANCHOR,
        /* 29 */   YY_NO_ANCHOR,
        /* 30 */   YY_NO_ANCHOR,
        /* 31 */   YY_NO_ANCHOR,
        /* 32 */   YY_NO_ANCHOR,
        /* 33 */   YY_NO_ANCHOR,
        /* 34 */   YY_NO_ANCHOR,
        /* 35 */   YY_NO_ANCHOR,
        /* 36 */   YY_NO_ANCHOR,
        /* 37 */   YY_NO_ANCHOR,
        /* 38 */   YY_NO_ANCHOR,
        /* 39 */   YY_NO_ANCHOR,
        /* 40 */   YY_NO_ANCHOR,
        /* 41 */   YY_NO_ANCHOR,
        /* 42 */   YY_NO_ANCHOR,
        /* 43 */   YY_NO_ANCHOR,
        /* 44 */   YY_NO_ANCHOR,
        /* 45 */   YY_NO_ANCHOR,
        /* 46 */   YY_NO_ANCHOR,
        /* 47 */   YY_NO_ANCHOR,
        /* 48 */   YY_NO_ANCHOR,
        /* 49 */   YY_NO_ANCHOR,
        /* 50 */   YY_NO_ANCHOR,
        /* 51 */   YY_NO_ANCHOR,
        /* 52 */   YY_NO_ANCHOR,
        /* 53 */   YY_NO_ANCHOR,
        /* 54 */   YY_NO_ANCHOR,
        /* 55 */   YY_NO_ANCHOR,
        /* 56 */   YY_NO_ANCHOR,
        /* 57 */   YY_NO_ANCHOR,
        /* 58 */   YY_NO_ANCHOR,
        /* 59 */   YY_NO_ANCHOR,
        /* 60 */   YY_NO_ANCHOR,
        /* 61 */   YY_NO_ANCHOR,
        /* 62 */   YY_NO_ANCHOR,
        /* 63 */   YY_NO_ANCHOR,
        /* 64 */   YY_NO_ANCHOR,
        /* 65 */   YY_NO_ANCHOR,
        /* 66 */   YY_NO_ANCHOR,
        /* 67 */   YY_NO_ANCHOR,
        /* 68 */   YY_NO_ANCHOR,
        /* 69 */   YY_NO_ANCHOR,
        /* 70 */   YY_NO_ANCHOR,
        /* 71 */   YY_NO_ANCHOR,
        /* 72 */   YY_NO_ANCHOR,
        /* 73 */   YY_NO_ANCHOR,
        /* 74 */   YY_NO_ANCHOR,
        /* 75 */   YY_NO_ANCHOR,
        /* 76 */   YY_NO_ANCHOR,
        /* 77 */   YY_NO_ANCHOR,
        /* 78 */   YY_NO_ANCHOR,
        /* 79 */   YY_NO_ANCHOR,
        /* 80 */   YY_NO_ANCHOR,
        /* 81 */   YY_NO_ANCHOR,
        /* 82 */   YY_NO_ANCHOR,
        /* 83 */   YY_NO_ANCHOR,
        /* 84 */   YY_NO_ANCHOR,
        /* 85 */   YY_NO_ANCHOR,
        /* 86 */   YY_NO_ANCHOR,
        /* 87 */   YY_NOT_ACCEPT,
        /* 88 */   YY_NO_ANCHOR,
        /* 89 */   YY_NO_ANCHOR,
        /* 90 */   YY_NO_ANCHOR,
        /* 91 */   YY_NO_ANCHOR,
        /* 92 */   YY_NO_ANCHOR,
        /* 93 */   YY_NO_ANCHOR,
        /* 94 */   YY_NO_ANCHOR,
        /* 95 */   YY_NO_ANCHOR,
        /* 96 */   YY_NO_ANCHOR,
        /* 97 */   YY_NO_ANCHOR,
        /* 98 */   YY_NO_ANCHOR,
        /* 99 */   YY_NO_ANCHOR,
        /* 100 */   YY_NO_ANCHOR,
        /* 101 */   YY_NO_ANCHOR,
        /* 102 */   YY_NO_ANCHOR,
        /* 103 */   YY_NO_ANCHOR,
        /* 104 */   YY_NO_ANCHOR,
        /* 105 */   YY_NO_ANCHOR,
        /* 106 */   YY_NO_ANCHOR,
        /* 107 */   YY_NO_ANCHOR,
        /* 108 */   YY_NO_ANCHOR,
        /* 109 */   YY_NO_ANCHOR,
        /* 110 */   YY_NO_ANCHOR,
        /* 111 */   YY_NO_ANCHOR,
        /* 112 */   YY_NO_ANCHOR,
        /* 113 */   YY_NO_ANCHOR,
        /* 114 */   YY_NO_ANCHOR,
        /* 115 */   YY_NO_ANCHOR,
        /* 116 */   YY_NO_ANCHOR,
        /* 117 */   YY_NO_ANCHOR,
        /* 118 */   YY_NO_ANCHOR,
        /* 119 */   YY_NO_ANCHOR,
        /* 120 */   YY_NO_ANCHOR,
        /* 121 */   YY_NOT_ACCEPT,
        /* 122 */   YY_NO_ANCHOR,
        /* 123 */   YY_NO_ANCHOR,
        /* 124 */   YY_NO_ANCHOR,
        /* 125 */   YY_NO_ANCHOR,
        /* 126 */   YY_NO_ANCHOR,
        /* 127 */   YY_NO_ANCHOR,
        /* 128 */   YY_NO_ANCHOR,
        /* 129 */   YY_NO_ANCHOR,
        /* 130 */   YY_NO_ANCHOR,
        /* 131 */   YY_NOT_ACCEPT,
        /* 132 */   YY_NO_ANCHOR,
        /* 133 */   YY_NO_ANCHOR,
        /* 134 */   YY_NO_ANCHOR,
        /* 135 */   YY_NOT_ACCEPT,
        /* 136 */   YY_NO_ANCHOR,
        /* 137 */   YY_NO_ANCHOR,
        /* 138 */   YY_NOT_ACCEPT,
        /* 139 */   YY_NO_ANCHOR,
        /* 140 */   YY_NOT_ACCEPT,
        /* 141 */   YY_NO_ANCHOR,
        /* 142 */   YY_NOT_ACCEPT,
        /* 143 */   YY_NO_ANCHOR,
        /* 144 */   YY_NOT_ACCEPT,
        /* 145 */   YY_NO_ANCHOR,
        /* 146 */   YY_NOT_ACCEPT,
        /* 147 */   YY_NO_ANCHOR,
        /* 148 */   YY_NOT_ACCEPT,
        /* 149 */   YY_NO_ANCHOR,
        /* 150 */   YY_NOT_ACCEPT,
        /* 151 */   YY_NO_ANCHOR,
        /* 152 */   YY_NOT_ACCEPT,
        /* 153 */   YY_NOT_ACCEPT,
        /* 154 */   YY_NOT_ACCEPT,
        /* 155 */   YY_NOT_ACCEPT,
        /* 156 */   YY_NOT_ACCEPT,
        /* 157 */   YY_NOT_ACCEPT,
        /* 158 */   YY_NOT_ACCEPT,
        /* 159 */   YY_NOT_ACCEPT,
        /* 160 */   YY_NOT_ACCEPT,
        /* 161 */   YY_NOT_ACCEPT,
        /* 162 */   YY_NOT_ACCEPT,
        /* 163 */   YY_NOT_ACCEPT,
        /* 164 */   YY_NOT_ACCEPT,
        /* 165 */   YY_NOT_ACCEPT,
        /* 166 */   YY_NOT_ACCEPT,
        /* 167 */   YY_NOT_ACCEPT,
        /* 168 */   YY_NOT_ACCEPT,
        /* 169 */   YY_NOT_ACCEPT,
        /* 170 */   YY_NOT_ACCEPT,
        /* 171 */   YY_NOT_ACCEPT,
        /* 172 */   YY_NOT_ACCEPT,
        /* 173 */   YY_NOT_ACCEPT,
        /* 174 */   YY_NOT_ACCEPT,
        /* 175 */   YY_NOT_ACCEPT,
        /* 176 */   YY_NOT_ACCEPT,
        /* 177 */   YY_NOT_ACCEPT,
        /* 178 */   YY_NOT_ACCEPT,
        /* 179 */   YY_NOT_ACCEPT,
        /* 180 */   YY_NOT_ACCEPT,
        /* 181 */   YY_NOT_ACCEPT,
        /* 182 */   YY_NOT_ACCEPT,
        /* 183 */   YY_NOT_ACCEPT,
        /* 184 */   YY_NOT_ACCEPT,
        /* 185 */   YY_NOT_ACCEPT,
        /* 186 */   YY_NOT_ACCEPT,
        /* 187 */   YY_NOT_ACCEPT,
        /* 188 */   YY_NOT_ACCEPT,
        /* 189 */   YY_NOT_ACCEPT,
        /* 190 */   YY_NOT_ACCEPT,
        /* 191 */   YY_NOT_ACCEPT,
        /* 192 */   YY_NOT_ACCEPT,
        /* 193 */   YY_NOT_ACCEPT,
        /* 194 */   YY_NOT_ACCEPT,
        /* 195 */   YY_NOT_ACCEPT,
        /* 196 */   YY_NOT_ACCEPT,
        /* 197 */   YY_NOT_ACCEPT,
        /* 198 */   YY_NOT_ACCEPT,
        /* 199 */   YY_NOT_ACCEPT,
        /* 200 */   YY_NOT_ACCEPT,
        /* 201 */   YY_NOT_ACCEPT,
        /* 202 */   YY_NOT_ACCEPT,
        /* 203 */   YY_NOT_ACCEPT,
        /* 204 */   YY_NOT_ACCEPT,
        /* 205 */   YY_NOT_ACCEPT,
        /* 206 */   YY_NOT_ACCEPT,
        /* 207 */   YY_NOT_ACCEPT,
        /* 208 */   YY_NOT_ACCEPT,
        /* 209 */   YY_NOT_ACCEPT,
        /* 210 */   YY_NOT_ACCEPT,
        /* 211 */   YY_NOT_ACCEPT,
        /* 212 */   YY_NOT_ACCEPT,
        /* 213 */   YY_NOT_ACCEPT,
        /* 214 */   YY_NOT_ACCEPT,
        /* 215 */   YY_NOT_ACCEPT,
        /* 216 */   YY_NOT_ACCEPT,
        /* 217 */   YY_NOT_ACCEPT,
        /* 218 */   YY_NOT_ACCEPT,
        /* 219 */   YY_NOT_ACCEPT,
        /* 220 */   YY_NOT_ACCEPT,
        /* 221 */   YY_NOT_ACCEPT,
        /* 222 */   YY_NOT_ACCEPT,
        /* 223 */   YY_NOT_ACCEPT,
        /* 224 */   YY_NOT_ACCEPT,
        /* 225 */   YY_NOT_ACCEPT,
        /* 226 */   YY_NOT_ACCEPT,
        /* 227 */   YY_NOT_ACCEPT,
        /* 228 */   YY_NOT_ACCEPT,
        /* 229 */   YY_NOT_ACCEPT,
        /* 230 */   YY_NOT_ACCEPT,
        /* 231 */   YY_NOT_ACCEPT,
        /* 232 */   YY_NOT_ACCEPT,
        /* 233 */   YY_NOT_ACCEPT,
        /* 234 */   YY_NOT_ACCEPT,
        /* 235 */   YY_NOT_ACCEPT,
        /* 236 */   YY_NOT_ACCEPT,
        /* 237 */   YY_NOT_ACCEPT,
        /* 238 */   YY_NOT_ACCEPT,
        /* 239 */   YY_NOT_ACCEPT,
        /* 240 */   YY_NOT_ACCEPT,
        /* 241 */   YY_NOT_ACCEPT,
        /* 242 */   YY_NOT_ACCEPT,
        /* 243 */   YY_NOT_ACCEPT,
        /* 244 */   YY_NOT_ACCEPT,
        /* 245 */   YY_NOT_ACCEPT,
        /* 246 */   YY_NOT_ACCEPT,
        /* 247 */   YY_NOT_ACCEPT,
        /* 248 */   YY_NOT_ACCEPT,
        /* 249 */   YY_NOT_ACCEPT,
        /* 250 */   YY_NOT_ACCEPT,
        /* 251 */   YY_NOT_ACCEPT,
        /* 252 */   YY_NOT_ACCEPT,
        /* 253 */   YY_NOT_ACCEPT,
        /* 254 */   YY_NOT_ACCEPT,
        /* 255 */   YY_NOT_ACCEPT,
        /* 256 */   YY_NOT_ACCEPT,
        /* 257 */   YY_NOT_ACCEPT,
        /* 258 */   YY_NOT_ACCEPT,
        /* 259 */   YY_NOT_ACCEPT,
        /* 260 */   YY_NOT_ACCEPT,
        /* 261 */   YY_NOT_ACCEPT,
        /* 262 */   YY_NOT_ACCEPT,
        /* 263 */   YY_NOT_ACCEPT,
        /* 264 */   YY_NOT_ACCEPT,
        /* 265 */   YY_NOT_ACCEPT,
        /* 266 */   YY_NOT_ACCEPT,
        /* 267 */   YY_NOT_ACCEPT,
        /* 268 */   YY_NOT_ACCEPT,
        /* 269 */   YY_NOT_ACCEPT,
        /* 270 */   YY_NOT_ACCEPT,
        /* 271 */   YY_NOT_ACCEPT,
        /* 272 */   YY_NOT_ACCEPT,
        /* 273 */   YY_NOT_ACCEPT,
        /* 274 */   YY_NOT_ACCEPT,
        /* 275 */   YY_NOT_ACCEPT,
        /* 276 */   YY_NOT_ACCEPT,
        /* 277 */   YY_NOT_ACCEPT,
        /* 278 */   YY_NOT_ACCEPT,
        /* 279 */   YY_NOT_ACCEPT,
        /* 280 */   YY_NOT_ACCEPT,
        /* 281 */   YY_NOT_ACCEPT,
        /* 282 */   YY_NOT_ACCEPT,
        /* 283 */   YY_NOT_ACCEPT,
        /* 284 */   YY_NOT_ACCEPT,
        /* 285 */   YY_NOT_ACCEPT,
        /* 286 */   YY_NOT_ACCEPT,
        /* 287 */   YY_NOT_ACCEPT,
        /* 288 */   YY_NOT_ACCEPT,
        /* 289 */   YY_NOT_ACCEPT,
        /* 290 */   YY_NOT_ACCEPT,
        /* 291 */   YY_NOT_ACCEPT,
        /* 292 */   YY_NO_ANCHOR,
        /* 293 */   YY_NO_ANCHOR,
        /* 294 */   YY_NO_ANCHOR,
        /* 295 */   YY_NO_ANCHOR,
        /* 296 */   YY_NOT_ACCEPT,
        /* 297 */   YY_NOT_ACCEPT,
        /* 298 */   YY_NOT_ACCEPT,
        /* 299 */   YY_NOT_ACCEPT,
        /* 300 */   YY_NOT_ACCEPT,
        /* 301 */   YY_NOT_ACCEPT,
        /* 302 */   YY_NOT_ACCEPT,
        /* 303 */   YY_NOT_ACCEPT,
        /* 304 */   YY_NOT_ACCEPT,
        /* 305 */   YY_NOT_ACCEPT,
        /* 306 */   YY_NOT_ACCEPT,
        /* 307 */   YY_NOT_ACCEPT,
        /* 308 */   YY_NOT_ACCEPT,
        /* 309 */   YY_NOT_ACCEPT,
        /* 310 */   YY_NOT_ACCEPT,
        /* 311 */   YY_NOT_ACCEPT,
        /* 312 */   YY_NOT_ACCEPT,
        /* 313 */   YY_NOT_ACCEPT,
        /* 314 */   YY_NOT_ACCEPT,
        /* 315 */   YY_NOT_ACCEPT,
        /* 316 */   YY_NOT_ACCEPT,
        /* 317 */   YY_NOT_ACCEPT,
        /* 318 */   YY_NOT_ACCEPT,
        /* 319 */   YY_NOT_ACCEPT,
        /* 320 */   YY_NOT_ACCEPT,
        /* 321 */   YY_NOT_ACCEPT,
        /* 322 */   YY_NOT_ACCEPT,
        /* 323 */   YY_NOT_ACCEPT,
        /* 324 */   YY_NOT_ACCEPT,
        /* 325 */   YY_NOT_ACCEPT,
        /* 326 */   YY_NOT_ACCEPT,
        /* 327 */   YY_NOT_ACCEPT,
        /* 328 */   YY_NOT_ACCEPT,
        /* 329 */   YY_NOT_ACCEPT,
        /* 330 */   YY_NOT_ACCEPT,
        /* 331 */   YY_NOT_ACCEPT,
        /* 332 */   YY_NOT_ACCEPT,
        /* 333 */   YY_NOT_ACCEPT,
        /* 334 */   YY_NOT_ACCEPT,
        /* 335 */   YY_NOT_ACCEPT,
        /* 336 */   YY_NOT_ACCEPT,
        /* 337 */   YY_NOT_ACCEPT,
        /* 338 */   YY_NOT_ACCEPT,
        /* 339 */   YY_NOT_ACCEPT,
        /* 340 */   YY_NOT_ACCEPT,
        /* 341 */   YY_NOT_ACCEPT
        );


    var  $yy_cmap = array(
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 11, 5, 31, 31, 12, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        11, 14, 30, 2, 32, 24, 1, 29,
        42, 21, 32, 32, 52, 15, 7, 9,
        3, 3, 3, 3, 3, 45, 3, 55,
        3, 3, 10, 4, 8, 28, 13, 23,
        31, 19, 46, 17, 18, 6, 6, 6,
        6, 38, 6, 6, 6, 6, 6, 6,
        40, 6, 37, 33, 20, 6, 6, 6,
        6, 6, 6, 16, 25, 22, 31, 26,
        31, 50, 46, 35, 47, 49, 44, 6,
        51, 39, 6, 6, 54, 6, 53, 48,
        40, 6, 36, 34, 41, 6, 6, 6,
        6, 6, 6, 27, 31, 43, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 0, 0 
         );


    var $yy_rmap = array(
        0, 1, 2, 3, 4, 5, 1, 6,
        7, 8, 9, 1, 10, 1, 11, 12,
        1, 3, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 13,
        1, 1, 1, 14, 1, 1, 15, 16,
        17, 1, 1, 18, 19, 18, 1, 1,
        1, 20, 1, 1, 21, 1, 22, 1,
        23, 24, 25, 1, 1, 26, 27, 28,
        29, 30, 1, 1, 31, 32, 1, 33,
        1, 1, 34, 1, 1, 1, 35, 36,
        1, 37, 1, 38, 1, 39, 1, 40,
        41, 42, 1, 43, 44, 1, 1, 45,
        46, 47, 48, 49, 50, 18, 51, 52,
        53, 49, 54, 55, 56, 57, 58, 59,
        60, 1, 61, 62, 1, 63, 64, 65,
        66, 67, 68, 69, 70, 71, 72, 70,
        73, 74, 1, 75, 1, 76, 77, 78,
        79, 80, 81, 82, 83, 84, 85, 86,
        87, 88, 89, 90, 91, 92, 93, 94,
        95, 96, 97, 98, 99, 100, 101, 102,
        103, 104, 105, 106, 107, 108, 109, 110,
        111, 112, 113, 114, 115, 116, 117, 118,
        119, 120, 121, 122, 123, 124, 125, 126,
        127, 128, 129, 130, 131, 132, 133, 134,
        135, 136, 137, 138, 139, 140, 141, 142,
        143, 144, 145, 146, 147, 148, 149, 150,
        151, 152, 153, 154, 155, 68, 156, 157,
        158, 159, 160, 161, 162, 163, 164, 165,
        166, 167, 168, 169, 170, 171, 172, 173,
        174, 16, 175, 176, 177, 178, 82, 73,
        77, 179, 180, 62, 181, 182, 183, 84,
        86, 184, 185, 186, 187, 188, 189, 190,
        191, 192, 193, 194, 195, 196, 197, 198,
        199, 200, 90, 201, 202, 203, 204, 205,
        206, 207, 208, 209, 210, 211, 212, 213,
        214, 215, 216, 217, 218, 219, 220, 221,
        222, 223, 224, 225, 226, 72, 227, 228,
        229, 70, 108, 230, 231, 232, 233, 234,
        120, 235, 236, 129, 237, 238, 141, 239,
        145, 240, 160, 241, 166, 242, 185, 243,
        191, 244, 202, 245, 208, 246, 247, 248,
        249, 250, 251, 252, 253, 254, 255, 256,
        257, 258, 259, 260, 261, 262 
        );


    var $yy_nxt = array(
        array( 1, 2, 3, 3, 3, 3, 3, 3,
            88, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 89, 292, 3,
            3, 3, 3, 123, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, 87, 3, 3, 3, 4, 3,
            -1, 3, 3, 3, 3, 3, 3, 3,
            3, 4, 4, 4, 4, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 4, 4, 4, 4, 4, 4, 4,
            4, 4, 3, 3, 4, 3, 4, 4,
            4, 4, 4, 4, 3, 4, 4, 3 ),
        array( -1, 121, 3, 3, 3, 3, 3, 3,
            131, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, -1, 3, 3,
            3, 3, 3, -1, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3 ),
        array( -1, -1, -1, 4, 90, 90, 4, 4,
            -1, -1, -1, -1, -1, -1, -1, 4,
            -1, 4, 4, 4, 4, -1, -1, -1,
            -1, -1, 4, -1, -1, -1, -1, -1,
            -1, 4, 4, 4, 4, 4, 4, 4,
            4, 4, -1, -1, 4, 4, 4, 4,
            4, 4, 4, 4, -1, 4, 4, 4 ),
        array( -1, -1, -1, 5, -1, 91, 5, 5,
            -1, -1, 5, 91, 91, -1, -1, 5,
            -1, 5, 5, 5, 5, -1, -1, -1,
            -1, -1, 5, -1, -1, -1, -1, -1,
            -1, 5, 5, 5, 5, 5, 5, 5,
            5, 5, -1, -1, 5, 5, 5, 5,
            5, 5, 5, 5, -1, 5, 5, 5 ),
        array( -1, -1, -1, -1, -1, 92, 15, -1,
            -1, -1, -1, 92, 92, -1, -1, -1,
            -1, 15, 15, 15, 15, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 15, 15, 15, 15, 15, 15, 15,
            15, 15, -1, -1, 15, -1, 15, 15,
            15, 15, 15, 15, -1, 15, 15, -1 ),
        array( -1, -1, -1, 8, 93, 93, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 8, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 8 ),
        array( -1, -1, -1, 9, 94, 94, 9, 9,
            -1, -1, -1, -1, -1, -1, -1, 9,
            -1, 9, 9, 9, 9, -1, -1, -1,
            -1, -1, 9, -1, -1, -1, -1, -1,
            -1, 9, 9, 9, 9, 9, 9, 9,
            9, 9, -1, -1, 9, 9, 9, 9,
            9, 9, 9, 9, -1, 9, 9, 9 ),
        array( -1, -1, -1, 10, -1, 95, 10, 10,
            -1, 148, 10, 95, 95, -1, -1, 10,
            -1, 10, 10, 10, 10, -1, -1, -1,
            -1, -1, 10, -1, -1, -1, -1, -1,
            -1, 10, 10, 10, 10, 10, 10, 10,
            10, 10, -1, -1, 10, 10, 10, 10,
            10, 10, 10, 10, -1, 10, 10, 10 ),
        array( -1, -1, -1, 12, -1, 96, 12, 12,
            -1, -1, -1, 96, 96, -1, -1, 12,
            -1, 12, 12, 12, 12, -1, -1, -1,
            -1, -1, 12, -1, -1, -1, -1, -1,
            -1, 12, 12, 12, 12, 12, 12, 12,
            12, 12, -1, -1, 12, 12, 12, 12,
            12, 12, 12, 12, -1, 12, 12, 12 ),
        array( -1, -1, -1, -1, -1, 97, -1, -1,
            -1, -1, -1, 97, 97, -1, -1, -1,
            -1, 157, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 15, -1, 98, 15, 15,
            -1, -1, -1, 98, 98, -1, -1, 15,
            -1, 15, 15, 15, 15, -1, -1, -1,
            -1, -1, 15, -1, -1, -1, -1, -1,
            -1, 15, 15, 15, 15, 15, 15, 15,
            15, 15, -1, -1, 15, 15, 15, 15,
            15, 15, 15, 15, -1, 15, 15, 15 ),
        array( -1, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            -1, 213, 31, -1, 31, -1, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31 ),
        array( 1, 132, 132, 132, 132, 100, 132, 132,
            36, 132, 132, 100, 100, 37, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132 ),
        array( -1, -1, -1, 38, -1, 102, 38, 38,
            -1, -1, 38, 102, 102, -1, -1, 38,
            -1, 38, 38, 38, 38, -1, -1, -1,
            -1, -1, 38, -1, 40, -1, -1, -1,
            -1, 38, 38, 38, 38, 38, 38, 38,
            38, 38, -1, -1, 38, 38, 38, 38,
            38, 38, 38, 38, -1, 38, 38, 38 ),
        array( -1, -1, -1, -1, -1, 233, -1, -1,
            -1, -1, -1, 233, 233, 41, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 40, -1, -1,
            -1, -1, -1, 40, 40, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 43, 43, 43, 43, 103, 43, 43,
            43, 43, 43, 103, 103, -1, 43, 43,
            43, 43, 43, 43, 43, 43, 43, 43,
            43, 43, 43, 43, 43, -1, -1, 43,
            43, 43, 43, 43, 43, 43, 43, 43,
            43, 43, 43, 43, 43, 43, 43, 43,
            43, 43, 43, 43, 43, 43, 43, 43 ),
        array( -1, 43, 43, 44, 43, 104, 44, 44,
            43, 43, 43, 104, 104, -1, 43, 44,
            43, 44, 44, 44, 44, 43, 43, 43,
            43, 43, 44, 43, 43, -1, -1, 43,
            43, 44, 44, 44, 44, 44, 44, 44,
            44, 44, 43, 43, 44, 44, 44, 44,
            44, 44, 44, 44, 43, 44, 44, 44 ),
        array( -1, -1, -1, -1, -1, 49, -1, -1,
            -1, -1, -1, 49, 49, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            -1, 238, 52, -1, 52, 52, -1, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52 ),
        array( 1, 55, 55, 56, 55, 106, 57, 58,
            55, 55, 55, 106, 106, 59, 55, 58,
            60, 57, 57, 57, 57, 55, 55, 55,
            107, 55, 58, 55, 55, 128, 134, 55,
            55, 57, 57, 57, 57, 57, 57, 57,
            57, 57, 55, 55, 57, 56, 57, 57,
            57, 57, 57, 57, 55, 57, 57, 56 ),
        array( -1, -1, -1, 56, -1, 108, 61, 61,
            -1, -1, -1, 108, 108, -1, -1, 61,
            -1, 61, 61, 61, 61, -1, -1, -1,
            -1, -1, 61, -1, -1, -1, -1, -1,
            -1, 61, 61, 61, 61, 61, 61, 61,
            61, 61, -1, -1, 61, 56, 61, 61,
            61, 61, 61, 61, -1, 61, 61, 56 ),
        array( -1, -1, -1, 57, -1, 109, 57, 57,
            -1, -1, -1, 109, 109, -1, -1, 57,
            -1, 57, 57, 57, 57, -1, -1, -1,
            -1, -1, 57, -1, -1, -1, -1, -1,
            -1, 57, 57, 57, 57, 57, 57, 57,
            57, 57, -1, -1, 57, 57, 57, 57,
            57, 57, 57, 57, -1, 57, 57, 57 ),
        array( -1, -1, -1, 58, -1, 110, 58, 58,
            -1, -1, -1, 110, 110, -1, -1, 58,
            -1, 58, 58, 58, 58, -1, -1, -1,
            -1, -1, 58, -1, -1, -1, -1, -1,
            -1, 58, 58, 58, 58, 58, 58, 58,
            58, 58, -1, -1, 58, 58, 58, 58,
            58, 58, 58, 58, -1, 58, 58, 58 ),
        array( -1, -1, -1, 61, -1, 111, 61, 61,
            -1, -1, -1, 111, 111, -1, -1, 61,
            -1, 61, 61, 61, 61, -1, -1, -1,
            -1, -1, 61, -1, -1, -1, -1, -1,
            -1, 61, 61, 61, 61, 61, 61, 61,
            61, 61, -1, -1, 61, 61, 61, 61,
            61, 61, 61, 61, -1, 61, 61, 61 ),
        array( -1, -1, -1, -1, -1, 62, -1, -1,
            -1, -1, -1, 62, 62, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 63, 112, 112, 63, 63,
            -1, -1, -1, 112, 112, -1, -1, 63,
            -1, 63, 63, 63, 63, -1, -1, -1,
            -1, -1, 63, -1, -1, -1, -1, -1,
            -1, 63, 63, 63, 63, 63, 63, 63,
            63, 63, -1, -1, 63, 63, 63, 63,
            63, 63, 63, 63, -1, 63, 63, 63 ),
        array( -1, -1, -1, -1, -1, 64, -1, -1,
            -1, -1, -1, 64, 64, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( 1, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 137,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114 ),
        array( -1, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, -1, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 245, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 116, -1, -1, -1 ),
        array( -1, -1, -1, 74, -1, -1, 74, 267,
            -1, -1, -1, -1, -1, -1, -1, -1,
            268, 74, 74, 74, 74, -1, -1, -1,
            336, -1, 74, -1, -1, -1, -1, -1,
            -1, 74, 74, 74, 74, 74, 74, 74,
            74, 74, -1, -1, 74, 74, 74, 74,
            74, 74, 74, 74, -1, 74, 74, 74 ),
        array( -1, 78, 78, 78, 78, 78, 78, 78,
            -1, 78, 78, 78, 78, 78, 78, 78,
            78, 78, 78, 78, 78, 78, 78, 78,
            78, 78, 78, 78, 78, 78, 78, 78,
            78, 78, 78, 78, 78, 78, 78, 78,
            78, 78, 78, 78, 78, 78, 78, 78,
            78, 78, 78, 78, 78, 78, 78, 78 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 281, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 81, 81, 81, 81, 81, 81, 81,
            81, 81, 81, 81, 81, 81, 81, 81,
            81, 81, 81, 81, 81, 81, -1, 81,
            81, 81, 81, 81, 81, 81, 81, 81,
            81, 81, 81, 81, 81, 81, 81, 81,
            81, 81, 81, 81, 81, 81, 81, 81,
            81, 81, 81, 81, 81, 81, 81, 81 ),
        array( 1, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 151,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119 ),
        array( 1, 120, 120, 120, 120, 120, 120, 120,
            120, 120, 120, 120, 120, 120, 120, 120,
            120, 120, 120, 120, 120, 120, 120, 291,
            120, 120, 120, 120, 120, 120, 120, 120,
            120, 120, 120, 120, 120, 120, 120, 120,
            120, 120, 120, 120, 120, 120, 120, 120,
            120, 120, 120, 120, 120, 120, 120, 120 ),
        array( -1, -1, -1, 8, -1, -1, 9, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 9, 9, 9, 9, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 9, 9, 9, 9, 9, 9, 9,
            9, 9, -1, -1, 9, 8, 9, 9,
            9, 9, 9, 9, -1, 9, 9, 8 ),
        array( -1, -1, -1, -1, -1, 3, 5, -1,
            -1, 135, -1, 3, 3, 6, 138, -1,
            3, 5, 5, 5, 5, -1, 3, 7,
            -1, 3, 3, 3, -1, -1, -1, 3,
            -1, 5, 5, 5, 5, 5, 5, 5,
            5, 5, -1, 3, 5, -1, 5, 5,
            5, 5, 5, 5, -1, 5, 5, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 140, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 91, -1, -1,
            -1, -1, -1, 91, 91, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 92, -1, -1,
            -1, -1, -1, 92, 92, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 95, -1, -1,
            -1, 148, -1, 95, 95, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 96, -1, -1,
            -1, -1, -1, 96, 96, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 97, -1, -1,
            -1, -1, -1, 97, 97, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 98, -1, -1,
            -1, -1, -1, 98, 98, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 214 ),
        array( -1, -1, -1, -1, -1, 100, -1, -1,
            -1, -1, -1, 100, 100, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 102, -1, -1,
            -1, -1, -1, 102, 102, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 40, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 103, -1, -1,
            -1, -1, -1, 103, 103, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 104, -1, -1,
            -1, -1, -1, 104, 104, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 106, -1, -1,
            -1, -1, -1, 106, 106, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 62, 63, -1,
            -1, -1, -1, 62, 62, -1, -1, -1,
            -1, 63, 63, 63, 63, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 63, 63, 63, 63, 63, 63, 63,
            63, 63, -1, -1, 63, -1, 63, 63,
            63, 63, 63, 63, -1, 63, 63, -1 ),
        array( -1, -1, -1, -1, -1, 108, -1, -1,
            -1, -1, -1, 108, 108, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 109, -1, -1,
            -1, -1, -1, 109, 109, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 110, -1, -1,
            -1, -1, -1, 110, 110, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 111, -1, -1,
            -1, -1, -1, 111, 111, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 112, -1, -1,
            -1, -1, -1, 112, 112, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 241,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 67, -1, 243,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 267,
            -1, -1, -1, -1, -1, -1, -1, -1,
            268, -1, -1, -1, -1, -1, -1, -1,
            336, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 129, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 289,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119 ),
        array( -1, 120, 120, 120, 120, 120, 120, 120,
            120, 120, 120, 120, 120, 120, 120, 120,
            120, 120, 120, 120, 120, 120, 120, -1,
            120, 120, 120, 120, 120, 120, 120, 120,
            120, 120, 120, 120, 120, 120, 120, 120,
            120, 120, 120, 120, 120, 120, 120, 120,
            120, 120, 120, 120, 120, 120, 120, 120 ),
        array( -1, -1, -1, 3, 3, 3, -1, 3,
            -1, 3, 3, 3, 3, 3, 3, 3,
            3, -1, -1, -1, -1, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 3, 3, -1, 3, -1, -1,
            -1, -1, -1, -1, 3, -1, -1, 3 ),
        array( -1, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, -1, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31 ),
        array( -1, -1, -1, -1, -1, -1, 142, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, -1, -1, -1,
            -1, -1, 144, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 296,
            142, 142, -1, -1, 341, -1, 142, 142,
            142, 326, 142, 142, -1, 142, 142, -1 ),
        array( -1, -1, -1, -1, -1, -1, 215, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 215, 215, 215, 215, -1, -1, -1,
            -1, -1, 215, -1, -1, -1, -1, -1,
            -1, 215, 215, 215, 215, 215, 215, 215,
            215, 215, -1, -1, 215, -1, 215, 215,
            215, 215, 215, 215, -1, 215, 215, -1 ),
        array( 1, 132, 132, 132, 132, 100, 38, 132,
            36, 39, 132, 100, 100, 37, 132, 132,
            132, 38, 38, 38, 38, 132, 132, 136,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 38, 38, 38, 38, 38, 38, 38,
            38, 38, 132, 132, 38, 132, 38, 38,
            38, 38, 38, 38, 132, 38, 38, 132 ),
        array( -1, 43, 43, 126, 43, 104, 126, 126,
            43, 43, 43, 104, 104, -1, 43, 126,
            43, 126, 126, 126, 126, 43, 43, 43,
            43, 43, 126, 43, 43, -1, -1, 43,
            43, 126, 126, 126, 126, 126, 126, 126,
            126, 126, 43, 43, 126, 126, 126, 126,
            126, 126, 126, 126, 43, 126, 126, 126 ),
        array( -1, 239, 239, 239, 239, 239, 239, 239,
            239, 239, 239, 239, 239, 239, 239, 239,
            239, 239, 239, 239, 239, 239, 239, 239,
            239, 239, 239, 239, 239, 64, 239, 239,
            239, 239, 239, 239, 239, 239, 239, 239,
            239, 239, 239, 239, 239, 239, 239, 239,
            239, 239, 239, 239, 239, 239, 239, 239 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 82, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 3, -1, -1,
            -1, -1, -1, 3, 3, -1, -1, -1,
            3, -1, -1, -1, -1, -1, 3, -1,
            -1, 3, 3, 3, -1, -1, -1, 3,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 3, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 121, 3, 3, 3, 3, 3, 3,
            131, 3, 3, 3, 3, 17, 3, 3,
            3, 3, 3, 3, 3, -1, 3, 3,
            3, 3, 3, -1, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3 ),
        array( -1, 240, 240, 240, 240, 240, 240, 240,
            240, 240, 240, 240, 240, 240, 240, 240,
            240, 240, 240, 240, 240, 240, 240, 240,
            240, 240, 240, 240, 240, 240, 113, 240,
            240, 240, 240, 240, 240, 240, 240, 240,
            240, 240, 240, 240, 240, 240, 240, 240,
            240, 240, 240, 240, 240, 240, 240, 240 ),
        array( -1, -1, -1, -1, -1, 146, 10, -1,
            -1, 148, -1, 146, 146, 11, -1, -1,
            -1, 10, 10, 10, 10, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 10, 10, 10, 10, 10, 10, 10,
            10, 10, -1, -1, 10, -1, 10, 10,
            10, 10, 10, 10, -1, 10, 10, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 42, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 242,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114 ),
        array( -1, -1, -1, -1, -1, -1, 12, -1,
            -1, -1, -1, -1, -1, 13, -1, 150,
            14, 12, 12, 12, 12, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 12, 12, 12, 12, 12, 12, 12,
            12, 12, -1, -1, 12, -1, 12, 12,
            12, 12, 12, 12, -1, 12, 12, -1 ),
        array( -1, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            52, -1, 52, 52, 52, 52, -1, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 16, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 247, 71, 247, 247, 247, 247, 247,
            247, 247, 247, 247, 247, 247, 247, 247,
            247, 247, 247, 247, 247, 247, 247, 247,
            247, 247, 247, 247, 247, 247, 247, 247,
            247, 247, 247, 247, 247, 247, 247, 247,
            247, 247, 247, 247, 247, 247, 247, 247,
            247, 247, 247, 247, 247, 247, 247, 247 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, 248, -1, -1, 248, 249,
            -1, -1, -1, -1, -1, -1, -1, -1,
            250, 248, 248, 248, 248, 251, -1, -1,
            334, -1, 248, -1, -1, -1, -1, -1,
            -1, 248, 248, 248, 248, 248, 248, 248,
            248, 248, -1, -1, 248, 248, 248, 248,
            248, 248, 248, 248, 72, 248, 248, 248 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 20, 19, 142, 142, 142, 142,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 252, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 73, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, 146, -1, -1,
            -1, 148, -1, 146, 146, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 266, 75, 266, 266, 266, 266, 266,
            266, 266, 266, 266, 266, 266, 266, 266,
            266, 266, 266, 266, 266, 266, 266, 266,
            266, 266, 266, 266, 266, 266, 266, 266,
            266, 266, 266, 266, 266, 266, 266, 266,
            266, 266, 266, 266, 266, 266, 266, 266,
            266, 266, 266, 266, 266, 266, 266, 266 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            21, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 269, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            270, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 76, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 22,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 290,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119 ),
        array( -1, -1, -1, -1, -1, -1, 158, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 158, 158, 158, 158, -1, -1, -1,
            -1, -1, 158, -1, -1, -1, -1, -1,
            -1, 158, 158, 158, 158, 158, 158, 158,
            158, 158, -1, -1, 158, -1, 158, 158,
            158, 158, 158, 158, -1, 158, 158, -1 ),
        array( -1, -1, -1, -1, -1, -1, 159, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 159, 159, 159, 159, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 159, 159, 159, 159, 159, 159, 159,
            159, 159, -1, -1, 159, -1, 159, 159,
            159, 159, 159, 159, -1, 159, 159, -1 ),
        array( -1, -1, -1, 160, -1, -1, 160, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 160, 160, 160, 160, -1, -1, -1,
            -1, -1, 160, -1, -1, -1, -1, -1,
            -1, 160, 160, 160, 160, 160, 160, 160,
            160, 160, -1, -1, 160, 160, 160, 160,
            160, 160, 160, 160, -1, 160, 160, 160 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 161, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 162, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 164, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 158, -1, -1, 158, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            165, 158, 158, 158, 158, -1, -1, -1,
            166, -1, 158, -1, -1, -1, -1, -1,
            -1, 158, 158, 158, 158, 158, 158, 158,
            158, 158, 18, 19, 158, 158, 158, 158,
            158, 158, 158, 158, -1, 158, 158, 158 ),
        array( -1, -1, -1, -1, -1, -1, 159, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 159, 159, 159, 159, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 159, 159, 159, 159, 159, 159, 159,
            159, 159, -1, 19, 159, -1, 159, 159,
            159, 159, 159, 159, -1, 159, 159, -1 ),
        array( -1, -1, -1, 160, -1, -1, 160, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 160, 160, 160, 160, -1, 167, -1,
            168, -1, 160, -1, -1, -1, -1, -1,
            -1, 160, 160, 160, 160, 160, 160, 160,
            160, 160, -1, -1, 160, 160, 160, 160,
            160, 160, 160, 160, -1, 160, 160, 160 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 154, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, 169, -1,
            -1, -1, -1, -1, -1, -1, 170, -1,
            -1, 169, 169, 169, 169, -1, -1, -1,
            -1, -1, 169, -1, -1, -1, -1, -1,
            -1, 169, 169, 169, 169, 169, 169, 169,
            169, 169, -1, -1, 169, -1, 169, 169,
            169, 169, 169, 169, -1, 169, 169, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 171, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 173, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 174, -1, -1, 174, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 174, 174, 174, 174, -1, -1, -1,
            -1, -1, 174, -1, -1, -1, -1, -1,
            -1, 174, 174, 174, 174, 174, 174, 174,
            174, 174, -1, -1, 174, 174, 174, 174,
            174, 174, 174, 174, -1, 174, 174, 174 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 299, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, -1, -1, -1, -1, -1, -1, -1,
            155, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 19, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 175, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 169, -1, -1, 169, 176,
            -1, -1, -1, -1, -1, -1, -1, -1,
            177, 169, 169, 169, 169, -1, -1, -1,
            327, -1, 169, -1, -1, -1, -1, -1,
            -1, 169, 169, 169, 169, 169, 169, 169,
            169, 169, 23, 24, 169, 169, 169, 169,
            169, 169, 169, 169, -1, 169, 169, 169 ),
        array( -1, -1, -1, -1, -1, -1, 169, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 169, 169, 169, 169, -1, -1, -1,
            -1, -1, 169, -1, -1, -1, -1, -1,
            -1, 169, 169, 169, 169, 169, 169, 169,
            169, 169, -1, -1, 169, -1, 169, 169,
            169, 169, 169, 169, -1, 169, 169, -1 ),
        array( -1, -1, -1, -1, -1, -1, 159, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 159, 159, 159, 159, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 159, 159, 159, 159, 159, 159, 159,
            159, 159, -1, 25, 159, -1, 159, 159,
            159, 159, 159, 159, -1, 159, 159, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 178, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 179, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 174, -1, -1, 174, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 174, 174, 174, 174, -1, 180, -1,
            181, -1, 174, -1, -1, -1, -1, -1,
            -1, 174, 174, 174, 174, 174, 174, 174,
            174, 174, -1, -1, 174, 174, 174, 174,
            174, 174, 174, 174, -1, 174, 174, 174 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 167, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 167,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, 182, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 182, 182, 182, 182, -1, -1, -1,
            -1, -1, 182, -1, -1, -1, -1, -1,
            -1, 182, 182, 182, 182, 182, 182, 182,
            182, 182, -1, -1, 182, -1, 182, 182,
            182, 182, 182, 182, -1, 182, 182, -1 ),
        array( -1, -1, -1, 183, -1, -1, 183, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 183, 183, 183, 183, -1, -1, -1,
            -1, -1, 183, -1, -1, -1, -1, -1,
            -1, 183, 183, 183, 183, 183, 183, 183,
            183, 183, -1, -1, 183, 183, 183, 183,
            183, 183, 183, 183, -1, 183, 183, 183 ),
        array( -1, -1, -1, -1, -1, -1, 159, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 159, 159, 159, 159, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 159, 159, 159, 159, 159, 159, 159,
            159, 159, -1, 26, 159, -1, 159, 159,
            159, 159, 159, 159, -1, 159, 159, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 184, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            165, -1, -1, -1, -1, -1, -1, -1,
            166, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 19, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 185, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 182, -1, -1, 182, 176,
            -1, -1, -1, -1, -1, -1, -1, -1,
            186, 182, 182, 182, 182, -1, -1, -1,
            330, -1, 182, -1, -1, -1, -1, -1,
            -1, 182, 182, 182, 182, 182, 182, 182,
            182, 182, 23, 24, 182, 182, 182, 182,
            182, 182, 182, 182, -1, 182, 182, 182 ),
        array( -1, -1, -1, 183, -1, -1, 183, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 183, 183, 183, 183, -1, 187, -1,
            188, -1, 183, -1, -1, -1, -1, -1,
            -1, 183, 183, 183, 183, 183, 183, 183,
            183, 183, -1, -1, 183, 183, 183, 183,
            183, 183, 183, 183, -1, 183, 183, 183 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            27, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 180, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 180,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 190, -1, -1, 190, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 190, 190, 190, 190, -1, -1, -1,
            -1, -1, 190, -1, -1, -1, -1, -1,
            -1, 190, 190, 190, 190, 190, 190, 190,
            190, 190, -1, -1, 190, 190, 190, 190,
            190, 190, 190, 190, -1, 190, 190, 190 ),
        array( -1, -1, -1, -1, -1, -1, -1, 176,
            -1, -1, -1, -1, -1, -1, -1, -1,
            177, -1, -1, -1, -1, -1, -1, -1,
            327, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 23, 24, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 191, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 192, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, 190, -1, -1, 190, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 190, 190, 190, 190, -1, 193, -1,
            194, -1, 190, -1, -1, -1, -1, -1,
            -1, 190, 190, 190, 190, 190, 190, 190,
            190, 190, -1, -1, 190, 190, 190, 190,
            190, 190, 190, 190, -1, 190, 190, 190 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 187, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 187,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, 195, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 195, 195, 195, 195, -1, -1, -1,
            -1, -1, 195, -1, -1, -1, -1, -1,
            -1, 195, 195, 195, 195, 195, 195, 195,
            195, 195, -1, -1, 195, -1, 195, 195,
            195, 195, 195, 195, -1, 195, 195, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 176,
            -1, -1, -1, -1, -1, -1, -1, -1,
            186, -1, -1, -1, -1, -1, -1, -1,
            330, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 23, 24, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 196, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 195, -1, -1, 195, 197,
            -1, -1, -1, -1, -1, -1, -1, -1,
            198, 195, 195, 195, 195, -1, -1, -1,
            332, -1, 195, -1, -1, -1, -1, -1,
            -1, 195, 195, 195, 195, 195, 195, 195,
            195, 195, -1, 28, 195, 195, 195, 195,
            195, 195, 195, 195, 300, 195, 195, 195 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 193, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 193,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, 199, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 199, 199, 199, 199, -1, -1, -1,
            -1, -1, 199, -1, -1, -1, -1, -1,
            -1, 199, 199, 199, 199, 199, 199, 199,
            199, 199, -1, -1, 199, -1, 199, 199,
            199, 199, 199, 199, -1, 199, 199, -1 ),
        array( -1, -1, -1, 200, -1, -1, 200, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 200, 200, 200, 200, -1, -1, -1,
            -1, -1, 200, -1, -1, -1, -1, -1,
            -1, 200, 200, 200, 200, 200, 200, 200,
            200, 200, -1, -1, 200, 200, 200, 200,
            200, 200, 200, 200, -1, 200, 200, 200 ),
        array( -1, -1, -1, 199, -1, -1, 199, 197,
            -1, -1, -1, -1, -1, -1, -1, -1,
            202, 199, 199, 199, 199, -1, -1, -1,
            333, -1, 199, -1, -1, -1, -1, -1,
            -1, 199, 199, 199, 199, 199, 199, 199,
            199, 199, -1, 28, 199, 199, 199, 199,
            199, 199, 199, 199, 300, 199, 199, 199 ),
        array( -1, -1, -1, 200, -1, -1, 200, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 200, 200, 200, 200, -1, 203, -1,
            204, -1, 200, -1, -1, -1, -1, -1,
            -1, 200, 200, 200, 200, 200, 200, 200,
            200, 200, -1, -1, 200, 200, 200, 200,
            200, 200, 200, 200, -1, 200, 200, 200 ),
        array( -1, -1, -1, 201, -1, -1, 201, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 201, 201, 201, 201, -1, -1, -1,
            -1, -1, 201, -1, -1, -1, -1, -1,
            -1, 201, 201, 201, 201, 201, 201, 201,
            201, 201, -1, 29, 201, 201, 201, 201,
            201, 201, 201, 201, 205, 201, 201, 201 ),
        array( -1, -1, -1, 206, -1, -1, 206, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 206, 206, 206, 206, -1, -1, -1,
            -1, -1, 206, -1, -1, -1, -1, -1,
            -1, 206, 206, 206, 206, 206, 206, 206,
            206, 206, -1, -1, 206, 206, 206, 206,
            206, 206, 206, 206, -1, 206, 206, 206 ),
        array( -1, -1, -1, -1, -1, -1, -1, 197,
            -1, -1, -1, -1, -1, -1, -1, -1,
            198, -1, -1, -1, -1, -1, -1, -1,
            332, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 28, -1, -1, -1, -1,
            -1, -1, -1, -1, 300, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 207, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, 208, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 208, 208, 208, 208, -1, -1, -1,
            -1, -1, 208, -1, -1, -1, -1, -1,
            -1, 208, 208, 208, 208, 208, 208, 208,
            208, 208, -1, -1, 208, -1, 208, 208,
            208, 208, 208, 208, -1, 208, 208, -1 ),
        array( -1, -1, -1, 206, -1, -1, 206, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 206, 206, 206, 206, -1, 209, -1,
            210, -1, 206, -1, -1, -1, -1, -1,
            -1, 206, 206, 206, 206, 206, 206, 206,
            206, 206, -1, -1, 206, 206, 206, 206,
            206, 206, 206, 206, -1, 206, 206, 206 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 203, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 203,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 208, -1, -1, 208, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 208, 208, 208, 208, -1, -1, -1,
            -1, -1, 208, -1, -1, -1, -1, -1,
            -1, 208, 208, 208, 208, 208, 208, 208,
            208, 208, -1, 30, 208, 208, 208, 208,
            208, 208, 208, 208, -1, 208, 208, 208 ),
        array( -1, -1, -1, -1, -1, -1, -1, 197,
            -1, -1, -1, -1, -1, -1, -1, -1,
            202, -1, -1, -1, -1, -1, -1, -1,
            333, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 28, -1, -1, -1, -1,
            -1, -1, -1, -1, 300, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 211, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 209, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 209,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( 1, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            99, 122, 31, 124, 31, 32, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31,
            31, 31, 31, 31, 31, 31, 31, 31 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 297, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 215, -1, -1, 215, 216,
            -1, -1, 217, -1, -1, -1, -1, -1,
            218, 215, 215, 215, 215, -1, -1, -1,
            219, -1, 215, -1, -1, -1, -1, -1,
            -1, 215, 215, 215, 215, 215, 215, 215,
            215, 215, 33, 34, 215, 215, 215, 215,
            215, 215, 215, 215, -1, 215, 215, 215 ),
        array( -1, -1, -1, -1, -1, -1, 220, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 220, 220, 220, 220, -1, -1, -1,
            -1, -1, 220, -1, -1, -1, -1, -1,
            -1, 220, 220, 220, 220, 220, 220, 220,
            220, 220, -1, -1, 220, -1, 220, 220,
            220, 220, 220, 220, -1, 220, 220, -1 ),
        array( -1, -1, -1, -1, -1, -1, 221, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 221, 221, 221, 221, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 221, 221, 221, 221, 221, 221, 221,
            221, 221, -1, -1, 221, -1, 221, 221,
            221, 221, 221, 221, -1, 221, 221, -1 ),
        array( -1, -1, -1, 222, -1, -1, 222, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 222, 222, 222, 222, -1, -1, -1,
            -1, -1, 222, -1, -1, -1, -1, -1,
            -1, 222, 222, 222, 222, 222, 222, 222,
            222, 222, -1, -1, 222, 222, 222, 222,
            222, 222, 222, 222, -1, 222, 222, 222 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 315, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 223 ),
        array( -1, -1, -1, 220, -1, -1, 220, 216,
            -1, -1, 217, -1, -1, -1, -1, -1,
            224, 220, 220, 220, 220, -1, -1, -1,
            328, -1, 220, -1, -1, -1, -1, -1,
            -1, 220, 220, 220, 220, 220, 220, 220,
            220, 220, 33, 34, 220, 220, 220, 220,
            220, 220, 220, 220, -1, 220, 220, 220 ),
        array( -1, -1, -1, -1, -1, -1, 221, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 221, 221, 221, 221, -1, -1, -1,
            225, -1, -1, -1, -1, -1, -1, -1,
            -1, 221, 221, 221, 221, 221, 221, 221,
            221, 221, -1, 34, 221, -1, 221, 221,
            221, 221, 221, 221, -1, 221, 221, -1 ),
        array( -1, -1, -1, 222, -1, -1, 222, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 222, 222, 222, 222, -1, 226, -1,
            227, -1, 222, -1, -1, -1, -1, -1,
            -1, 222, 222, 222, 222, 222, 222, 222,
            222, 222, -1, -1, 222, 222, 222, 222,
            222, 222, 222, 222, -1, 222, 222, 222 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 34, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 34,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 228, -1, -1, 228, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 228, 228, 228, 228, -1, -1, -1,
            -1, -1, 228, -1, -1, -1, -1, -1,
            -1, 228, 228, 228, 228, 228, 228, 228,
            228, 228, -1, -1, 228, 228, 228, 228,
            228, 228, 228, 228, -1, 228, 228, 228 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 223 ),
        array( -1, -1, -1, -1, -1, -1, -1, 216,
            -1, -1, 217, -1, -1, -1, -1, -1,
            218, -1, -1, -1, -1, -1, -1, -1,
            219, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 34, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 229, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 228, -1, -1, 228, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 228, 228, 228, 228, -1, 230, -1,
            231, -1, 228, -1, -1, -1, -1, -1,
            -1, 228, 228, 228, 228, 228, 228, 228,
            228, 228, -1, -1, 228, 228, 228, 228,
            228, 228, 228, 228, -1, 228, 228, 228 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 226, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 226,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 216,
            -1, -1, 217, -1, -1, -1, -1, -1,
            224, -1, -1, -1, -1, -1, -1, -1,
            328, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 34, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 232, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 230, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 230,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( 1, 43, 43, 44, 43, -1, 293, 293,
            101, 45, 43, 132, -1, 46, 43, 293,
            43, 293, 293, 293, 293, 43, 43, 43,
            43, 43, 293, 43, 43, 47, 48, 43,
            43, 293, 293, 293, 293, 293, 293, 293,
            293, 293, 43, 43, 293, 44, 293, 293,
            293, 293, 293, 293, 43, 293, 293, 44 ),
        array( 1, 132, 132, 132, 132, 49, 132, 132,
            132, 132, 132, 49, 49, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132 ),
        array( 1, 50, 50, 50, 50, -1, 50, 50,
            50, 50, 50, 50, -1, 51, 50, 50,
            50, 50, 50, 50, 50, 50, 50, 50,
            50, 50, 50, 50, 50, 50, 50, 50,
            50, 50, 50, 50, 50, 50, 50, 50,
            50, 50, 50, 50, 50, 50, 50, 50,
            50, 50, 50, 50, 50, 50, 50, 50 ),
        array( 1, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            105, 139, 52, 127, 52, 52, 53, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52,
            52, 52, 52, 52, 52, 52, 52, 52 ),
        array( -1, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, -1,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114,
            114, 114, 114, 114, 114, 114, 114, 114 ),
        array( -1, 66, 66, 66, 66, 66, 66, 66,
            66, 66, 66, 66, 66, 67, 66, 115,
            66, 66, 66, 66, 66, 66, 66, 66,
            66, 66, 66, 66, 66, 66, 66, 66,
            66, 66, 66, 66, 66, 66, 66, 66,
            66, 66, 66, 66, 66, 66, 66, 66,
            66, 66, 66, 66, 66, 66, 66, 66 ),
        array( 1, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 69, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 70, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( 1, 132, 141, 132, 132, -1, 143, 132,
            132, 132, 132, 132, -1, 132, 132, 132,
            132, 143, 143, 143, 143, 145, 132, 132,
            132, 132, 143, 132, 132, 132, 132, 132,
            132, 143, 143, 143, 143, 143, 143, 143,
            143, 143, 132, 132, 143, 132, 143, 143,
            143, 143, 143, 143, 132, 143, 143, 132 ),
        array( -1, -1, -1, -1, -1, -1, 253, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 253, 253, 253, 253, -1, -1, -1,
            -1, -1, 253, -1, -1, -1, -1, -1,
            -1, 253, 253, 253, 253, 253, 253, 253,
            253, 253, -1, -1, 253, -1, 253, 253,
            253, 253, 253, 253, -1, 253, 253, -1 ),
        array( -1, -1, -1, 254, -1, -1, 254, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 254, 254, 254, 254, -1, -1, -1,
            -1, -1, 254, -1, -1, -1, -1, -1,
            -1, 254, 254, 254, 254, 254, 254, 254,
            254, 254, -1, -1, 254, 254, 254, 254,
            254, 254, 254, 254, -1, 254, 254, 254 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 301, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 72, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, 255, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 255, 255, 255, 255, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 255, 255, 255, 255, 255, 255, 255,
            255, 255, -1, -1, 255, -1, 255, 255,
            255, 255, 255, 255, -1, 255, 255, -1 ),
        array( -1, -1, -1, 253, -1, -1, 253, 249,
            -1, -1, -1, -1, -1, -1, -1, -1,
            256, 253, 253, 253, 253, 251, -1, -1,
            335, -1, 253, -1, -1, -1, -1, -1,
            -1, 253, 253, 253, 253, 253, 253, 253,
            253, 253, -1, -1, 253, 253, 253, 253,
            253, 253, 253, 253, 72, 253, 253, 253 ),
        array( -1, -1, -1, 254, -1, -1, 254, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 254, 254, 254, 254, -1, 257, -1,
            258, -1, 254, -1, -1, -1, -1, -1,
            -1, 254, 254, 254, 254, 254, 254, 254,
            254, 254, -1, -1, 254, 254, 254, 254,
            254, 254, 254, 254, -1, 254, 254, 254 ),
        array( -1, -1, -1, -1, -1, -1, 255, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 255, 255, 255, 255, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 255, 255, 255, 255, 255, 255, 255,
            255, 255, -1, 73, 255, -1, 255, 255,
            255, 255, 255, 255, -1, 255, 255, -1 ),
        array( -1, -1, -1, 260, -1, -1, 260, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 260, 260, 260, 260, -1, -1, -1,
            -1, -1, 260, -1, -1, -1, -1, -1,
            -1, 260, 260, 260, 260, 260, 260, 260,
            260, 260, -1, -1, 260, 260, 260, 260,
            260, 260, 260, 260, -1, 260, 260, 260 ),
        array( -1, -1, -1, -1, -1, -1, -1, 249,
            -1, -1, -1, -1, -1, -1, -1, -1,
            250, -1, -1, -1, -1, 251, -1, -1,
            334, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 72, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 261, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, 259, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 259, 259, 259, 259, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 259, 259, 259, 259, 259, 259, 259,
            259, 259, -1, 72, 259, -1, 259, 259,
            259, 259, 259, 259, -1, 259, 259, -1 ),
        array( -1, -1, -1, 260, -1, -1, 260, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 260, 260, 260, 260, -1, 262, -1,
            263, -1, 260, -1, -1, -1, -1, -1,
            -1, 260, 260, 260, 260, 260, 260, 260,
            260, 260, -1, -1, 260, 260, 260, 260,
            260, 260, 260, 260, -1, 260, 260, 260 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 257, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 257,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 249,
            -1, -1, -1, -1, -1, -1, -1, -1,
            256, -1, -1, -1, -1, 251, -1, -1,
            335, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 72, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 264, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 262, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 262,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( 1, 132, 147, 132, 132, -1, 74, 132,
            132, 132, 132, 132, -1, 132, 132, 132,
            132, 74, 74, 74, 74, 149, 132, 132,
            132, 132, 74, 132, 132, 132, 132, 132,
            132, 74, 74, 74, 74, 74, 74, 74,
            74, 74, 132, 132, 74, 132, 74, 74,
            74, 74, 74, 74, 132, 74, 74, 132 ),
        array( -1, -1, -1, -1, -1, -1, 294, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 294, 294, 294, 294, -1, -1, -1,
            -1, -1, 294, -1, -1, -1, -1, -1,
            -1, 294, 294, 294, 294, 294, 294, 294,
            294, 294, -1, -1, 294, -1, 294, 294,
            294, 294, 294, 294, -1, 294, 294, -1 ),
        array( -1, -1, -1, 271, -1, -1, 271, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 271, 271, 271, 271, -1, -1, -1,
            -1, -1, 271, -1, -1, -1, -1, -1,
            -1, 271, 271, 271, 271, 271, 271, 271,
            271, 271, -1, -1, 271, 271, 271, 271,
            271, 271, 271, 271, -1, 271, 271, 271 ),
        array( -1, -1, -1, -1, -1, -1, 272, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 272, 272, 272, 272, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 272, 272, 272, 272, 272, 272, 272,
            272, 272, -1, -1, 272, -1, 272, 272,
            272, 272, 272, 272, -1, 272, 272, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 273 ),
        array( -1, -1, -1, 271, -1, -1, 271, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 271, 271, 271, 271, -1, 117, -1,
            275, -1, 271, -1, -1, -1, -1, -1,
            -1, 271, 271, 271, 271, 271, 271, 271,
            271, 271, -1, -1, 271, 271, 271, 271,
            271, 271, 271, 271, -1, 271, 271, 271 ),
        array( -1, -1, -1, -1, -1, -1, 272, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 272, 272, 272, 272, -1, -1, -1,
            270, -1, -1, -1, -1, -1, -1, -1,
            -1, 272, 272, 272, 272, 272, 272, 272,
            272, 272, -1, 76, 272, -1, 272, 272,
            272, 272, 272, 272, -1, 272, 272, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 76, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 76,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 276, -1, -1, 276, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 276, 276, 276, 276, -1, -1, -1,
            -1, -1, 276, -1, -1, -1, -1, -1,
            -1, 276, 276, 276, 276, 276, 276, 276,
            276, 276, -1, -1, 276, 276, 276, 276,
            276, 276, 276, 276, -1, 276, 276, 276 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 277, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 276, -1, -1, 276, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 276, 276, 276, 276, -1, 295, -1,
            278, -1, 276, -1, -1, -1, -1, -1,
            -1, 276, 276, 276, 276, 276, 276, 276,
            276, 276, -1, -1, 276, 276, 276, 276,
            276, 276, 276, 276, -1, 276, 276, 276 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 117, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 117,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 302, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( 1, 132, 132, 132, 132, -1, 132, 132,
            132, 132, 132, 132, -1, 132, 132, 132,
            132, 132, 132, 132, 132, 149, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 132, 132, 132, 132,
            132, 132, 132, 132, 77, 132, 132, 132 ),
        array( 1, 78, 78, 78, 78, 78, 78, 78,
            79, 78, 78, 78, 78, 78, 78, 78,
            78, 78, 78, 78, 78, 78, 78, 78,
            78, 78, 78, 78, 78, 78, 78, 78,
            78, 78, 78, 78, 78, 78, 78, 78,
            78, 78, 78, 78, 78, 78, 78, 78,
            78, 78, 78, 78, 78, 78, 78, 78 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 282, 282, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 283, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 283, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 284, 284, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 285, 285,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            286, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 287, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 287, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 80, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( 1, 81, 81, 81, 81, 81, 81, 81,
            81, 81, 81, 81, 81, 81, 81, 81,
            81, 81, 81, 81, 81, 81, 118, 81,
            81, 81, 81, 81, 81, 81, 81, 81,
            81, 81, 81, 81, 81, 81, 81, 81,
            81, 81, 81, 81, 81, 81, 81, 81,
            81, 81, 81, 81, 81, 81, 81, 81 ),
        array( -1, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, -1,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119,
            119, 119, 119, 119, 119, 119, 119, 119 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 84, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 86, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130 ),
        array( -1, 121, 3, 3, 3, 3, 3, 3,
            131, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, -1, 133, 3,
            3, 3, 3, -1, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3 ),
        array( -1, -1, -1, 294, -1, -1, 294, 267,
            -1, -1, -1, -1, -1, -1, -1, -1,
            274, 294, 294, 294, 294, -1, -1, -1,
            337, -1, 294, -1, -1, -1, -1, -1,
            -1, 294, 294, 294, 294, 294, 294, 294,
            294, 294, -1, -1, 294, 294, 294, 294,
            294, 294, 294, 294, -1, 294, 294, 294 ),
        array( -1, -1, -1, -1, -1, -1, -1, 267,
            -1, -1, -1, -1, -1, -1, -1, -1,
            274, -1, -1, -1, -1, -1, -1, -1,
            337, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 156, 142, 142, 142,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 298, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, 201, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 201, 201, 201, 201, -1, -1, -1,
            -1, -1, 201, -1, -1, -1, -1, -1,
            -1, 201, 201, 201, 201, 201, 201, 201,
            201, 201, -1, -1, 201, -1, 201, 201,
            201, 201, 201, 201, -1, 201, 201, -1 ),
        array( -1, -1, -1, -1, -1, -1, 259, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 259, 259, 259, 259, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 259, 259, 259, 259, 259, 259, 259,
            259, 259, -1, -1, 259, -1, 259, 259,
            259, 259, 259, 259, -1, 259, 259, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 295, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 295,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 163,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 304, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 172, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 307, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 142, 189, -1, 142, 142, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 310, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 312, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 314, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 316, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 318, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 320, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 322, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 324, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 142, 142, -1, 303, 329, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 305, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 317, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 223 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 306, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 308, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 309, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 311, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 313, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 319, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 321, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 323, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 325, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 331, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 338, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 339, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            142, 142, 142, 142, -1, 142, 142, 142 ),
        array( -1, -1, -1, 142, -1, -1, 142, 152,
            -1, -1, 153, -1, -1, -1, -1, -1,
            154, 142, 142, 142, 142, -1, -1, -1,
            155, -1, 142, -1, -1, -1, -1, -1,
            -1, 142, 142, 142, 142, 142, 142, 142,
            142, 142, 18, 19, 142, 142, 142, 142,
            340, 142, 142, 142, -1, 142, 142, 142 )
        );


    function  yylex()
    {
        $yy_lookahead = '';
        $yy_anchor = YY_NO_ANCHOR;
        $yy_state = $this->yy_state_dtrans[$this->yy_lexical_state];
        $yy_next_state = YY_NO_STATE;
         $yy_last_accept_state = YY_NO_STATE;
        $yy_initial = true;
        $yy_this_accept = 0;
        
        $this->yy_mark_start();
        $yy_this_accept = $this->yy_acpt[$yy_state];
        if (YY_NOT_ACCEPT != $yy_this_accept) {
            $yy_last_accept_state = $yy_state;
            $this->yy_buffer_end = $this->yy_buffer_index;
        }
        while (true) {
            if ($yy_initial && $this->yy_at_bol) {
                $yy_lookahead =  YY_BOL;
            } else {
                $yy_lookahead = $this->yy_advance();
            }
            $yy_next_state = $this->yy_nxt[$this->yy_rmap[$yy_state]][$this->yy_cmap[$yy_lookahead]];
            if (YY_EOF == $yy_lookahead && $yy_initial) {
                return false;            }
            if (YY_F != $yy_next_state) {
                $yy_state = $yy_next_state;
                $yy_initial = false;
                $yy_this_accept = $this->yy_acpt[$yy_state];
                if (YY_NOT_ACCEPT != $yy_this_accept) {
                    $yy_last_accept_state = $yy_state;
                    $this->yy_buffer_end = $this->yy_buffer_index;
                }
            } else {
                if (YY_NO_STATE == $yy_last_accept_state) {
                    $this->yy_error(1,1);
                } else {
                    $yy_anchor = $this->yy_acpt[$yy_last_accept_state];
                    if (0 != (YY_END & $yy_anchor)) {
                        $this->yy_move_end();
                    }
                    $this->yy_to_mark();
                    if ($yy_last_accept_state < 0) {
                       if ($yy_last_accept_state < 342) {
                           $this->yy_error(YY_E_INTERNAL, false);
                       }
                    } else {

                        switch ($yy_last_accept_state) {
case 2:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 3:
{
    //abcd -- data characters  
    // { and ) added for flexy
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 4:
{
    // &abc;
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 5:
{
    //<name -- start tag */
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    $this->tagName = trim(substr($this->yytext(),1));
    $this->tokenName = 'Tag';
    $this->value = '';
    $this->attributes = array();
    $this->yybegin(IN_ATTR);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 6:
{  
    // <> -- empty start tag */
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    return $this->raiseError("empty tag"); 
}
case 7:
{ 
    /* <? php start.. */
    //echo "STARTING PHP?\n";
    $this->yyPhpBegin = $this->yy_buffer_start;
    $this->yybegin(IN_PHP);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 8:
{
    // &#123;
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 9:
{
    // &#abc;
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 10:
{
    /* </title> -- end tag */
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    $this->tagName = trim(substr($this->yytext(),1));
    $this->tokenName = 'EndTag';
    $this->yybegin(IN_ENDTAG);
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 11:
{
    /* </> -- empty end tag */  
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    return $this->raiseError("empty end tag not handled");
}
case 12:
{
    /* <!DOCTYPE -- markup declaration */
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    $this->value = HTML_Template_Flexy_Token::factory('Doctype',$this->yytext(),$this->yyline);
    $this->yybegin(IN_MD);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 13:
{
    /* <!> */
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    return $this->raiseError("empty markup tag not handled"); 
}
case 14:
{
    /* <![ -- marked section */
    return $this->returnSimple();
}
case 15:
{ 
    /* eg. <?xml-stylesheet, <?php ... */
    $t = $this->yytext();
    $tagname = trim(strtoupper(substr($t,2)));
   // echo "STARTING XML? $t:$tagname\n";
    if ($tagname == 'PHP') {
        $this->yyPhpBegin = $this->yy_buffer_start;
        $this->yybegin(IN_PHP);
        return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
    }
    // not php - it's xlm or something...
    // we treat this like a tag???
    // we are going to have to escape it eventually...!!!
    $this->tagName = trim(substr($t,1));
    $this->tokenName = 'Tag';
    $this->value = '';
    $this->attributes = array();
    $this->yybegin(IN_ATTR);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 16:
{
    $this->value = HTML_Template_Flexy_Token::factory('GetTextEnd','',$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 17:
{ 
    /* ]]> -- marked section end */
    return $this->returnSimple();
}
case 18:
{
    $this->value =  '';
    $this->flexyMethod = substr($this->yytext(),1,-1);
    $this->flexyArgs = array();
    $this->yybegin(IN_FLEXYMETHOD);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 19:
{
    $t =  $this->yytext();
    $t = substr($t,1,-1);
    $this->value = HTML_Template_Flexy_Token::factory('Var'  , $t, $this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 20:
{
    $this->value = HTML_Template_Flexy_Token::factory('GetTextStart','',$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 21:
{
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    /* </name <  -- unclosed end tag */
    return $this->raiseError("Unclosed  end tag");
}
case 22:
{
    /* <!--  -- comment declaration */
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    $this->yyCommentBegin = $this->yy_buffer_end;
    //$this->value = HTML_Template_Flexy_Token::factory('Comment',$this->yytext(),$this->yyline);
    $this->yybegin(IN_COM);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 23:
{
    $this->value =  '';
    $this->flexyMethod = substr($this->yytext(),1,-1);
    $this->flexyArgs = array();
    $this->yybegin(IN_FLEXYMETHOD);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 24:
{
    $this->value = HTML_Template_Flexy_Token::factory('If',substr($this->yytext(),4,-1),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 25:
{
    $this->value = HTML_Template_Flexy_Token::factory('End', '',$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 26:
{
    $this->value = HTML_Template_Flexy_Token::factory('Else', '',$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 27:
{
    /* <![ -- marked section */
    $this->yybegin(IN_CDATA);
    $this->yyCdataBegin = $this->yy_buffer_end;
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 28:
{
    return $this->raiseError('invalid sytnax for Foreach','',true);
}
case 29:
{
    $this->value = HTML_Template_Flexy_Token::factory('Foreach', explode(',',substr($this->yytext(),9,-1)),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 30:
{
    $this->value = HTML_Template_Flexy_Token::factory('Foreach',  explode(',',substr($this->yytext(),9,-1)),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 31:
{
    $this->attrVal[] = $this->yytext();
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 32:
{
    $this->attrVal[] = "'";
     //var_dump($this->attrVal);
    $s = "";
    foreach($this->attrVal as $v) {
        if (!is_string($v)) {
            $this->attributes[$this->attrKey] = $this->attrVal;
            $this->yybegin(IN_ATTR);
            return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
        }
        $s .= $v;
    }
    $this->attributes[$this->attrKey] = $s;
    $this->yybegin(IN_ATTR);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 33:
{
    $this->value =  '';
    $n = $this->yytext();
    if ($n{0} != "{") {
        $n = substr($n,2);
    }
    $this->flexyMethod = substr($n,1,-1);
    $this->flexyArgs = array();
    $this->flexyMethodState = $this->yy_lexical_state;
    $this->yybegin(IN_FLEXYMETHODQUOTED);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 34:
{
    $n = $this->yytext();
    if ($n{0} != '{') {
        $n = substr($n,3);
    } else {
        $n = substr($n,1);
    }
    if ($n{strlen($n)-1} != '}') {
        $n = substr($n,0,-3);
    } else {
        $n = substr($n,0,-1);
    }
    $this->attrVal[] = HTML_Template_Flexy_Token::factory('Var'  , $n, $this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 35:
{
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 36:
{
    // <foo^<bar> -- unclosed start tag */
    return $this->raiseError("Unclosed tags not supported"); 
}
case 37:
{
    $this->value = HTML_Template_Flexy_Token::factory($this->tokenName,
        array($this->tagName,$this->attributes),
        $this->yyline);
    if (strtoupper($this->tagName) == 'SCRIPT') {
        $this->yybegin(IN_SCRIPT);
        return HTML_TEMPLATE_FLEXY_TOKEN_OK;
    }
    $this->yybegin(YYINITIAL);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 38:
{
    // <img src="xxx" ...ismap...> the ismap */
    $this->attributes[trim($this->yytext())] = true;
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 39:
{
    // <em^/ -- NET tag */
    $this->yybegin(IN_NETDATA);
    $this->attributes["/"] = true;
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 40:
{
   // <a ^href = "xxx"> -- attribute name 
    $this->attrKey = substr(trim($this->yytext()),0,-1);
    $this->yybegin(IN_ATTRVAL);
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 41:
{
    // <em^/ -- NET tag */
    $this->attributes["/"] = true;
    $this->value = HTML_Template_Flexy_Token::factory($this->tokenName,
        array($this->tagName,$this->attributes),
        $this->yyline);
    $this->yybegin(YYINITIAL);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 42:
{
    // <em^/ -- NET tag */
    $this->attributes["?"] = true;
    $this->value = HTML_Template_Flexy_Token::factory($this->tokenName,
        array($this->tagName,$this->attributes),
        $this->yyline);
    $this->yybegin(YYINITIAL);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 43:
{
    // <a href = ^http://foo/> -- unquoted literal HACK */                          
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    //   $this->raiseError("attribute value needs quotes");
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 44:
{
    // <a name = ^12pt> -- number token */
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 45:
{
    // <em^/ -- NET tag */
    return $this->raiseError("attribute value missing"); 
}
case 46:
{ 
    return $this->raiseError("Tag close found where attribute value expected"); 
}
case 47:
{
	//echo "STARTING SINGLEQUOTE";
    $this->attrVal = array( "'");
    $this->yybegin(IN_SINGLEQUOTE);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 48:
{
    //echo "START QUOTE";
    $this->attrVal =array("\"");
    $this->yybegin(IN_DOUBLEQUOTE);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 49:
{ 
    // whitespace switch back to IN_ATTR MODE.
    $this->value = '';
    $this->yybegin(IN_ATTR);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 50:
{ 
    return $this->raiseError("extraneous character in end tag"); 
}
case 51:
{ 
    $this->value = HTML_Template_Flexy_Token::factory($this->tokenName,
        array($this->tagName),
        $this->yyline);
        array($this->tagName);
    $this->yybegin(YYINITIAL);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 52:
{
    //echo "GOT DATA:".$this->yytext();
    $this->attrVal[] = $this->yytext();
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 53:
{
    //echo "GOT END DATA:".$this->yytext();
    $this->attrVal[] = "\"";
    $s = "";
    foreach($this->attrVal as $v) {
        if (!is_string($v)) {
            $this->attributes[$this->attrKey] = $this->attrVal;
            $this->yybegin(IN_ATTR);
            return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
        }
        $s .= $v;
    }
    $this->attributes[$this->attrKey] = $s;
    $this->yybegin(IN_ATTR);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 54:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('WhiteSpace',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 55:
{
    return $this->raiseError("illegal character in markup declaration");
}
case 56:
{   
    $this->value = HTML_Template_Flexy_Token::factory('Number',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 57:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('Name',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 58:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('NameT',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 59:
{   
    $this->value = HTML_Template_Flexy_Token::factory('CloseTag',$this->yytext(),$this->yyline);
    $this->yybegin(YYINITIAL); 
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 60:
{
    // <!doctype foo ^[  -- declaration subset */
    $this->value = HTML_Template_Flexy_Token::factory('BeginDS',$this->yytext(),$this->yyline);
    $this->yybegin(IN_DS);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 61:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('NumberT',$this->yytext(),$this->yyline);    
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 62:
{
    // <!entity ^% foo system "..." ...> -- parameter entity definition */
    $this->value = HTML_Template_Flexy_Token::factory('EntityPar',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 63:
{
    // <!doctype ^%foo;> -- parameter entity reference */
    $this->value = HTML_Template_Flexy_Token::factory('EntityRef',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 64:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('Literal',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 65:
{
    // inside a comment (not - or not --
    // <!^--...-->   -- comment */   
    //$this->value = HTML_Template_Flexy_Token::factory('Comment',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 66:
{
	// inside comment -- without a >
	return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 67:
{   
    $this->value = HTML_Template_Flexy_Token::factory('Comment',
        '<!--'. substr($this->yy_buffer,$this->yyCommentBegin ,$this->yy_buffer_end - $this->yyCommentBegin),
        $this->yyline
    );
    $this->yybegin(YYINITIAL); 
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 68:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('Declaration',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 69:
{ 
    // ] -- declaration subset close */
    $this->value = HTML_Template_Flexy_Token::factory('DSEndSubset',$this->yytext(),$this->yyline);
    $this->yybegin(IN_DSCOM); 
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 70:
{
    // ]]> -- marked section end */
     $this->value = HTML_Template_Flexy_Token::factory('DSEnd',$this->yytext(),$this->yyline);
    $this->yybegin(YYINITIAL);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 71:
{
    $t = $this->yytext();
    if ($t{strlen($t)-1} == ",") {
        // add argument
        $this->flexyArgs[] = substr($t,0,-1);
        return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
    }
    $this->flexyArgs[] = $t;
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 72:
{
    $t = $this->yytext();
    if ($t{strlen($t)-1} == ",") {
        // add argument
        $this->flexyArgs[] = substr($t,0,-1);
        return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
    }
    if ($c = strpos($t,':')) {
        $this->flexyMethod .= substr($t,$c,-1);
        $t = substr($t,0,$c-1);
    } else {
        $t = substr($t,0,-2);
    }
    $this->flexyArgs[] = $t;
    $this->value = HTML_Template_Flexy_Token::factory('Method'  , array($this->flexyMethod,$this->flexyArgs), $this->yyline);
    $this->yybegin(YYINITIAL);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 73:
{
    $t = $this->yytext();
    if ($t{1} == ':') {
        $this->flexyMethod .= substr($t,1,-1);
    }
    $this->value = HTML_Template_Flexy_Token::factory('Method'  , array($this->flexyMethod,$this->flexyArgs), $this->yyline);
    $this->yybegin(YYINITIAL);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 74:
{
    $t = $this->yytext();
    // add argument
    $this->flexyArgs[] = $t;
    $this->yybegin(IN_FLEXYMETHODQUOTED_END);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 75:
{
    $t = $this->yytext();
    $this->flexyArgs[] =$t;
    $this->yybegin(IN_FLEXYMETHODQUOTED_END);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 76:
{
    $t = $this->yytext();
    if ($p = strpos($t,':')) {
        $this->flexyMethod .= substr($t,$p,2);
    }
    $this->attrVal[] = HTML_Template_Flexy_Token::factory('Method'  , array($this->flexyMethod,$this->flexyArgs), $this->yyline);    
    $this->yybegin($this->flexyMethodState);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 77:
{
    $this->yybegin(IN_FLEXYMETHODQUOTED);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 78:
{
    // general text in script..
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 79:
{
    // just < .. 
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 80:
{
    // </script>
    $this->value = HTML_Template_Flexy_Token::factory('EndTag',
        array('/script'),
        $this->yyline);
    $this->yybegin(YYINITIAL);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 81:
{ 
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 82:
{ 
    /* ]]> -- marked section end */
    $this->value = HTML_Template_Flexy_Token::factory('Cdata',
        substr($this->yy_buffer,$this->yyCdataBegin ,$this->yy_buffer_end - $this->yyCdataBegin - 3 ),
        $this->yyline);
    $this->yybegin(YYINITIAL);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 83:
{
    // inside a comment (not - or not --
    // <!^--...-->   -- comment */   
    $this->value = HTML_Template_Flexy_Token::factory('DSComment',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 84:
{   
    $this->value = HTML_Template_Flexy_Token::factory('DSEnd', $this->yytext(),$this->yyline);
    $this->yybegin(YYINITIAL); 
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 85:
{     
    /* anything inside of php tags */
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 86:
{ 
    /* php end */
    if ($this->ignorePHP) {
        $this->yybegin(YYINITIAL);
        return HTML_TEMPLATE_FLEXY_TOKEN_NONE;    
    }
    $this->value = HTML_Template_Flexy_Token::factory('Php',
        substr($this->yy_buffer,$this->yyPhpBegin ,$this->yy_buffer_end - $this->yyPhpBegin ),
        $this->yyline);
    $this->yybegin(YYINITIAL);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 88:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 89:
{
    //abcd -- data characters  
    // { and ) added for flexy
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 90:
{
    // &abc;
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 91:
{
    //<name -- start tag */
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    $this->tagName = trim(substr($this->yytext(),1));
    $this->tokenName = 'Tag';
    $this->value = '';
    $this->attributes = array();
    $this->yybegin(IN_ATTR);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 92:
{ 
    /* <? php start.. */
    //echo "STARTING PHP?\n";
    $this->yyPhpBegin = $this->yy_buffer_start;
    $this->yybegin(IN_PHP);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 93:
{
    // &#123;
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 94:
{
    // &#abc;
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 95:
{
    /* </title> -- end tag */
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    $this->tagName = trim(substr($this->yytext(),1));
    $this->tokenName = 'EndTag';
    $this->yybegin(IN_ENDTAG);
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 96:
{
    /* <!DOCTYPE -- markup declaration */
    if ($this->ignoreHTML) {
        return $this->returnSimple();
    }
    $this->value = HTML_Template_Flexy_Token::factory('Doctype',$this->yytext(),$this->yyline);
    $this->yybegin(IN_MD);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 97:
{
    /* <![ -- marked section */
    return $this->returnSimple();
}
case 98:
{ 
    /* eg. <?xml-stylesheet, <?php ... */
    $t = $this->yytext();
    $tagname = trim(strtoupper(substr($t,2)));
   // echo "STARTING XML? $t:$tagname\n";
    if ($tagname == 'PHP') {
        $this->yyPhpBegin = $this->yy_buffer_start;
        $this->yybegin(IN_PHP);
        return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
    }
    // not php - it's xlm or something...
    // we treat this like a tag???
    // we are going to have to escape it eventually...!!!
    $this->tagName = trim(substr($t,1));
    $this->tokenName = 'Tag';
    $this->value = '';
    $this->attributes = array();
    $this->yybegin(IN_ATTR);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 99:
{
    $this->attrVal[] = $this->yytext();
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 100:
{
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 101:
{
    // <foo^<bar> -- unclosed start tag */
    return $this->raiseError("Unclosed tags not supported"); 
}
case 102:
{
    // <img src="xxx" ...ismap...> the ismap */
    $this->attributes[trim($this->yytext())] = true;
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 103:
{
    // <a href = ^http://foo/> -- unquoted literal HACK */                          
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    //   $this->raiseError("attribute value needs quotes");
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 104:
{
    // <a name = ^12pt> -- number token */
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 105:
{
    //echo "GOT DATA:".$this->yytext();
    $this->attrVal[] = $this->yytext();
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 106:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('WhiteSpace',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 107:
{
    return $this->raiseError("illegal character in markup declaration");
}
case 108:
{   
    $this->value = HTML_Template_Flexy_Token::factory('Number',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 109:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('Name',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 110:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('NameT',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 111:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('NumberT',$this->yytext(),$this->yyline);    
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 112:
{
    // <!doctype ^%foo;> -- parameter entity reference */
    $this->value = HTML_Template_Flexy_Token::factory('EntityRef',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 113:
{ 
    $this->value = HTML_Template_Flexy_Token::factory('Literal',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK; 
}
case 114:
{
    // inside a comment (not - or not --
    // <!^--...-->   -- comment */   
    //$this->value = HTML_Template_Flexy_Token::factory('Comment',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 115:
{
	// inside comment -- without a >
	return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 116:
{
    $t = $this->yytext();
    if ($t{strlen($t)-1} == ",") {
        // add argument
        $this->flexyArgs[] = substr($t,0,-1);
        return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
    }
    $this->flexyArgs[] = $t;
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 117:
{
    $t = $this->yytext();
    // add argument
    $this->flexyArgs[] = $t;
    $this->yybegin(IN_FLEXYMETHODQUOTED_END);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 118:
{ 
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 119:
{
    // inside a comment (not - or not --
    // <!^--...-->   -- comment */   
    $this->value = HTML_Template_Flexy_Token::factory('DSComment',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 120:
{     
    /* anything inside of php tags */
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 122:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 123:
{
    //abcd -- data characters  
    // { and ) added for flexy
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 124:
{
    $this->attrVal[] = $this->yytext();
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 125:
{
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 126:
{
    // <a name = ^12pt> -- number token */
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 127:
{
    //echo "GOT DATA:".$this->yytext();
    $this->attrVal[] = $this->yytext();
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 128:
{
    return $this->raiseError("illegal character in markup declaration");
}
case 129:
{ 
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 130:
{     
    /* anything inside of php tags */
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 132:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 133:
{
    //abcd -- data characters  
    // { and ) added for flexy
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 134:
{
    return $this->raiseError("illegal character in markup declaration");
}
case 136:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 137:
{
    return $this->raiseError("illegal character in markup declaration");
}
case 139:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 141:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 143:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 145:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 147:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 149:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 151:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 292:
{
    //abcd -- data characters  
    // { and ) added for flexy
    $this->value = HTML_Template_Flexy_Token::factory('Text',$this->yytext(),$this->yyline);
    return HTML_TEMPLATE_FLEXY_TOKEN_OK;
}
case 293:
{
    // <a name = ^12pt> -- number token */
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    $this->value = '';
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 294:
{
    $t = $this->yytext();
    // add argument
    $this->flexyArgs[] = $t;
    $this->yybegin(IN_FLEXYMETHODQUOTED_END);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}
case 295:
{
    $t = $this->yytext();
    // add argument
    $this->flexyArgs[] = $t;
    $this->yybegin(IN_FLEXYMETHODQUOTED_END);
    return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
}

                        }
                    }
                    $yy_initial = true;
                    $yy_state = $this->yy_state_dtrans[$this->yy_lexical_state];
                    $yy_next_state = YY_NO_STATE;
                    $yy_last_accept_state = YY_NO_STATE;
                    $this->yy_mark_start();
                    $yy_this_accept = $this->yy_acpt[$yy_state];
                    if (YY_NOT_ACCEPT != $yy_this_accept) {
                        $yy_last_accept_state = $yy_state;
                        $this->yy_buffer_end = $this->yy_buffer_index;
                    }
                }
            }
        }
        return HTML_TEMPLATE_FLEXY_TOKEN_NONE;
    }
}
