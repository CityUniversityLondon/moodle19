<?php

// dirty file manager wrapper!
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
$fname=dirname(dirname(dirname(__FILE__))).'/files/index.php';
$fcontents=file_get_contents  ($fname);
$fcontents=str_ireplace('require(\'../config.php\');', '', $fcontents);
$fcontents=str_ireplace(
    '$navlinks[] = array(\'name\' => $strfiles, \'link\' => null, \'type\' => \'misc\');',
    '$navlinks[] = array(\'name\' => get_string(\'modulenameplural\', \'enhancedfile\'), \'link\' => null, \'type\' => \'misc\');
    ',
    $fcontents);
$fcontents='?>'.$fcontents; // bug pointed out by Anis Jradah - page was not working
eval ($fcontents);
?>
