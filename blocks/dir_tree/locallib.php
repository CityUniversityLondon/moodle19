<?php

function list_directories($dirPath) {
        global $CFG;
        global $USER;
        $dirItems=array();
        $handle = opendir($CFG->dataroot.'/'.$dirPath);
        while(false !== ($file = readdir($handle))) {
            if($file != "." && $file != "..") {
                $name=$CFG->dataroot.'/'.$dirPath.'/'.$file;
                if( is_dir($name)) { 
                    // do a recursive crawl of a subdirectory
                    if ( (substr($file,0,8) == "moddata") || (substr($file,0,10) == "backupdata")) {
                        continue;  
                    }
                    array_push($dirItems,$file);
                }
            }
        }
        return $dirItems;
}

function crawl_directory($dirPath, $maxDepth = 100, $depth=1, $array=array(), $foundFileTypes = array()) {
            
        global $CFG;
        $strftime = get_string('strftimedatetime');
        $dirItems=array();
        $handle = opendir($CFG->dataroot.'/'.$dirPath);

        while(false !== ($file = readdir($handle))) {
            if($file != "." && $file != "..") {
                $name=$CFG->dataroot.'/'.$dirPath.'/'.$file;
                if( is_dir($name)) { 
                    // do a recursive crawl of a subdirectory
                    if ( (substr($file,0,8) == "moddata") || (substr($file,0,10) == "backupdata")) {
                        continue;                       
                    }
                    if ($depth >= $maxDepth) {
                        continue;                       
                    }
                    list($subDirContent,$subFoundFiles) = crawl_directory($dirPath.'/'.$file.'/', $maxDepth, $depth + 1, $array, $foundFileTypes);
                    foreach (array_keys($subFoundFiles) as $thisType) {
                        $foundFileTypes[$thisType] = true;
                    }
                    $thisItem = array();
                    $thisItem['name'] = $file;
                    $thisItem['path'] = $name; 
                    $thisItem['type'] = "folder";
                    $thisItem['extension'] = 'folder';
                    $thisItem['children'] = $subDirContent;
                    $thisItem['size'] = display_size(get_directory_size("$CFG->dataroot/$dirPath/$file"));
                    $thisItem['modified'] = userdate(filemtime("$CFG->dataroot/$dirPath/$file"), $strftime);
                    array_push($dirItems,$thisItem);
                    $foundFileTypes['folder'] = true;
                } else {
                    $pathinfo=pathinfo($CFG->dataroot.'/'.$dirPath.'/'.$file);
                    $tmp=substr($dirPath,strpos($dirPath,"/")+1);
                    if( (substr($tmp,0,8) != "moddata/") && (substr($tmp,0,11) != "backupdata/")) { 
                        $thisItem = array();
                        $thisItem['name'] = $file;
                        $thisItem['path'] = '/file.php/'.$dirPath.'/'.$file;
                        $thisItem['extension'] = $pathinfo['extension'];
                        $thisItem['type'] = "file";
                        $thisItem['size'] = display_size(filesize("$CFG->dataroot/$dirPath/$file"));
                        $thisItem['modified'] = userdate(filemtime("$CFG->dataroot/$dirPath/$file"), $strftime);
                        array_push($dirItems,$thisItem);
                        $foundFileTypes[$pathinfo['extension']] = true;
                    }
                }
            }
        }
        closedir($handle);
        return array($dirItems, $foundFileTypes);
}

// Chop the middle out of any string longer than $length
function fit_string($string, $length) {
    if(strlen($string) <= $length) {
        return $string;
    }
    $str1 = substr($string, 0, $length-8);
    $str2 = substr($string, -5);
    return $str1 . "..." . $str2;
}

 ?>
