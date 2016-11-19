<?php

// XML Entity Mandatory Escape Characters
// This is expensive, is there a way to cache?
function xmlentities($string) {

  $string = htmlentities2unicodeentities($string);
  $string = str_replace ( array ( '&', '"', "'", '<', '>', '?'), array
                                 ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '' ),
             $string );
  $string = str_replace('E##E', '&#', $string);
  # Strip all non-unicode chars
  # TODO: s/([^\x20-\x7f])/sprintf("&#%d;", ord($1)/eg;
  $string = preg_replace('/[^\x01-\x7f]/e', '', $string);

  return $string;
}

function htmlentities2unicodeentities ($input) {
  static $htmlEntities = NULL;
  static $utf8Entities = NULL;

  if (empty($htmlEntities)) {
    $htmlEntities = array_values (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
    $entitiesDecoded = array_keys  (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
    $num = count ($entitiesDecoded);
    for ($u = 0; $u < $num; $u++) {
      $utf8Entities[$u] = 'E##E'.ord($entitiesDecoded[$u]).';';
    }
  }

  return str_replace ($htmlEntities, $utf8Entities, $input);
}

/*
function xmlentities($string, $quote_style=ENT_QUOTES)
{
   static $trans;
   if (!isset($trans)) {
       $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);
       foreach ($trans as $key => $value)
           $trans[$key] = '&#'.ord($key).';';
       // dont translate the '&' in case it is part of &xxx;
       $trans[chr(38)] = '&';
   }
   // after the initial translation, _do_ map standalone '&' into '&#38;'
   return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,3};)/","&#38;" , strtr($string, $trans));
}

# replace all high order bytes with Unicode equivalents
$text = preg_replace('/([\xc0-\xdf].)/se', "'&#' . ((ord(substr('$1', 0, 1)) - 192) * 64 + (ord(substr('$1', 1, 1)) - 128)) . ';'", $text);
$text = preg_replace('/([\xe0-\xef]..)/se', "'&#' . ((ord(substr('$1', 0, 1)) - 224) * 4096 + (ord(substr('$1', 1, 1)) - 128) * 64 + (ord(substr('$1', 2, 1)) - 128)) . ';'", $text);


*/
?>
