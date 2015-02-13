<?php



function uow_truncate_html($text, $length = 100, $ending = '...') {
    
    // if the plain text is shorter than the maximum length, return the whole text
    if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
        return $text;
    }
    
    // splits all html-tags to scanable lines
    preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

    $totallength = strlen($ending);
    $opentags = array();
    $truncate = '';
    
    foreach ($lines as $linematchings) {
        // if there is any html-tag in this line, handle it and add it (uncounted) to the output
        if (!empty($linematchings[1])) {
            // if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
            if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $linematchings[1])) {
                // do nothing
             // if tag is a closing tag (f.e. </b>)
            } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $linematchings[1], $tagmatchings)) {
                // delete tag from $opentags list
                $pos = array_search($tagmatchings[1], $opentags);
                if ($pos !== false) {
                    unset($opentags[$pos]);
                }
            // if tag is an opening tag (f.e. <b>)
            } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $linematchings[1], $tagmatchings)) {
                // add tag to the beginning of $open_tags list
                array_unshift($opentags, strtolower($tagmatchings[1]));
            }
            // add html-tag to $truncate'd text
            $truncate .= $linematchings[1];
        }
        
        // calculate the length of the plain text part of the line. handle entities as one character
        $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $linematchings[2]));
        if ($totallength+$content_length > $length) {
            // the number of characters which are left
            $left = $length - $totallength;
            $entitieslength = 0;
            // search for html entities
            if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $linematchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                // calculate the real length of all entities in the legal range
                foreach ($entities[0] as $entity) {
                    if ($entity[1]+1-$entitieslength <= $left) {
                        $left--;
                        $entitieslength += strlen($entity[0]);
                    } else {
                        // no more characters left
                        break;
                    }
                }
            }
            $truncate .= substr($linematchings[2], 0, $left+$entitieslength);
            // maximum length is reached
            break;
        } else {
            $truncate .= $linematchings[2];
            $totallength += $content_length;
        }

        if($totallength >= $length) {
            break;
        }
    }

    // search the last occurance of a space
    $spacepos = strrpos($truncate, ' ');
    if (isset($spacepos)) {
        // ...and cut the text in this position
        $truncate = substr($truncate, 0, $spacepos);
    }

    // add the defined ending to the text
    $truncate .= $ending;
    
    // close all unclosed html-tags
    foreach ($opentags as $tag) {
        $truncate .= '</' . $tag . '>';
    }
        
    return $truncate;
    
}


/**
Utility base32 SHA1 class for PHP5
Copyright (C) 2006  Karl Magdsick (original author for Python)
                    Angel Leon (ported to PHP5)
                    Lime Wire LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
class SHA1 {
  static $BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

  /** Given a file it creates a magnetmix */
  static function fileSHA1($file) {
    $raw = sha1_file($file,true);
    return SHA1::base32encode($raw);
  } //fileSHA1

  /** Takes raw input and converts it to base32 */
  static function base32encode($input) {
    $output = '';
    $position = 0;
    $storedData = 0;
    $storedBitCount = 0;
    $index = 0;

    while ($index < strlen($input)) {
      $storedData <<= 8;
      $storedData += ord($input[$index]);
      $storedBitCount += 8;
      $index += 1;

      //take as much data as possible out of storedData
      while ($storedBitCount >= 5) {
        $storedBitCount -= 5;
        $output .= SHA1::$BASE32_ALPHABET[$storedData >> $storedBitCount];
        $storedData &= ((1 << $storedBitCount) - 1);
      }
    } //while

    //deal with leftover data
    if ($storedBitCount > 0) {
      $storedData <<= (5-$storedBitCount);
      $output .= SHA1::$BASE32_ALPHABET[$storedData];
    }

    return $output;
  } //base32encode

}

function uow_assignment_reciept($file, $submissiondate) {
    $sha1 = new SHA1;
    $reciept = '';
    
    $hash = $sha1->fileSHA1($file);
    $date = date('Ymd', $submissiondate);
    
    for($i=0;$i<8;$i++) {
        $reciept .= substr($hash, $i*4, 4).$date[7-$i].'-';
    }
    $reciept = substr($reciept, 0, -1);
    
    return($reciept);
}

function uow_assignment_coversheet($submission, $outputfile) {
    
    //uow_generate_rtf($templatefile, $fields)
}

/**
 * Create a RTF document based on teplate RTF file.
 * 
 * Will search and replace place hold fields and translate basic HTML tags to RFT formatting
 *
 * @param string $templatefile
 * @param array $fields
 * @return string
 */
function uow_generate_rtf($templatefile, $fields) {

    $tr = array();

    $tr['<p>']      = '\par \pard\plain \intbl\ltrpar\s14\ql\rtlch\af7\afs22\lang255\ltrch\dbch\af8\langfe255\hich\f7\fs22\lang2057\loch\f7\fs22\lang2057 {\rtlch \ltrch\loch\f7\fs22\lang2057\i0\b0 ';
    $tr['</p>']     = '}\par';
    $tr['<br />']   = '\par ';
    $tr['<b>']      = '{\rtlch\ltrch\dbch\hich\b\loch\b ';
    $tr['</b>']     = '}';
    $tr['<u>']      = '{\ul\ulc0 ';
    $tr['</u>']     = '}';
    $tr['<h1>']     = '{\rtlch \ltrch\loch\f3\fs24\lang2057\i0\b ';
    $tr['</h1>']    = '}\par\par';
    $tr['<h2>']     = '{\rtlch\ltrch\dbch\hich\b\loch\b ';
    $tr['</h2>']    = '}\par \pard\plain \intbl\ltrpar\s14\ql\rtlch\af7\afs22\lang255\ltrch\dbch\af8\langfe255\hich\f7\fs22\lang2057\loch\f7\fs22\lang2057 {\rtlch \ltrch\loch\f7\fs22\lang2057\i0\b0 }';
    $tr['<tr>']     = '\trowd\trql\trpaddft3\trpaddt55\trpaddfl3\trpaddl55\trpaddfb3\trpaddb55\trpaddfr3\trpaddr55\clbrdrt\brdrs\brdrw1\brdrcf1\clbrdrl\brdrs\brdrw1\brdrcf1\clbrdrb\brdrs\brdrw1\brdrcf1\cellx3315\clbrdrt\brdrs\brdrw1\brdrcf1\clbrdrl\brdrs\brdrw1\brdrcf1\clbrdrb\brdrs\brdrw1\brdrcf1\clbrdrr\brdrs\brdrw1\brdrcf1\cellx9637'; 
    $tr['</tr>']    = '\row';
    $tr['<th>']     = '\pard\intbl {\rtlch \ltrch\loch\f3\fs24\lang2057\i0\b ';
    $tr['</th>']    = '} \cell';
    $tr['<td>']     = '\pard\intbl {\rtlch \ltrch\loch\f7\fs22\lang2057\i0\b0 ';
    $tr['</td>']    = '} \cell';


    foreach ($fields as $idx => $field) {
        $field = purify_html($field);
        $fields[$idx] = strip_tags(strtr($field, $tr));
    }

    if (!$template = file_get_contents($templatefile)) {
        return(false);
    }
    $template = strtr($template, $fields);
    return($template);
}

?>