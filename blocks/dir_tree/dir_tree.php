<?php

/**
 * Some functions used to crawl and inspect folders to build data for display 
 * in a YUI TreeView widget by both the narrow view /block/dir_tree block 
 * and the wider view mod made to the mod/resource/type/directory class.    
 */
 
require_once ($CFG->libdir . '/json/JSON.php');
require_once ($CFG->libdir . '/filelib.php');


class dir_tree {

    // some base constants, Moodle specific, overriden if neccesary by instantiator
    // using the public properties
    const MAXITEMS_DEFAULT = 20;
    const CSS_INCLUDE = '/blocks/dir_tree/style.css';
    const JS_INCLUDE = '/blocks/dir_tree/dir_tree.js?ver=20081106';
    const ICON_PATH = '/pix/f/';
    const NAME_LENGTH_NARROW = 17;
    
    public $dirName;       // div to hold the rendered YUI widget
    public $staticDirName; // div to hold a static non JS version of the tree
    public $dirItems;      // data structure holding crawled docs/folders
    public $width;         // wide (page) or narrow (portlet/block)
    public $maxItems;
    public $wwwroot;       // web browseable root location
    public $dataroot;      // file system location of files to be crawled 
    public $dirroot;       // file system location of web root
    public $namelen_narrow;   // narrow/portlet/block max name length
    public $path_icons;     // relative path to pix/icons
    
    /**
    * Constructor for the dir_tree class
    */
    function  __construct() {
        // set some defaults, will probably want to set these in the calling script
        $randIntID = rand(); // so we dont get a collision if more than one on a page
        $this->dirName = "dirTreeYUI$randIntID";
        $this->staticDirName = "dirTreeStatic$randIntID";
        $this->width = 'narrow';
        $this->namelen_narrow = dir_tree::NAME_LENGTH_NARROW;
        $this->path_icons = dir_tree::ICON_PATH;
    }

    /**
     * Render the directory items out as a JSON encoded text string for the browser
     * @return string   JSON encoded data
     */
    function JSON() {
        $json = new Services_JSON();
        return $json->encode($this->dirItems);
    }

    /**
     * This function crawls a dataroot folder based on a start path and returns an
     * associative array containing folders, files and their properties. It calls 
     * itself recursively to crawl into subdirectories too returning their properties
     * and can be limited in crawl depth by providign a value for $maxDepth
     * @param string $dirPath   - starting folder path (relative to dataroot)
     * @param integer $maxDepth - maximum folder depth to crawl (default 3)
     * @param integer $depth    - current folder depth
     * @param array $array      - current directory contetns, passed on thru recursion
     * @return assoc. array     - containing files, folders and their properties
     */
    function crawl_directory($dirPath, $maxDepth = 100, $depth=1, $array=array()) {
        $strftime = get_string('strftimedatetime');
        $dirItems=array();
        $handle = opendir($this->dataroot.'/'.$dirPath);
        while(false !== ($file = readdir($handle))) {
            if($file != "." && $file != "..") {
                $fullPath=$this->dataroot.'/'.$dirPath.'/'.$file;
                if( is_dir($fullPath)) {
                    // skip folders we dont want to expose
                    if (skip_folder($file)) {
                        continue;                   
                    }
                    // skip if we're already at the maximum crawl depth
                    if ($depth >= $maxDepth) {
                        continue;                   
                    }
                    // do a recursive crawl of a subdirectory
                    $subDirContent = $this->crawl_directory($dirPath.'/'.$file.'/', $maxDepth, $depth + 1, $array);
                    $thisItem = array();
                    $thisItem['name'] = $file;
                    $thisItem['path'] = '/mod/resource/view.php?id=' . '&subdir=/' . $file; 
                    $thisItem['type'] = "folder";
                    $thisItem['extension'] = 'folder';
                    $thisItem['children'] = $subDirContent;
                    $thisItem['size'] = display_size(get_directory_size("$this->dataroot/$dirPath/$file"));
                    $thisItem['modified'] = userdate(filemtime("$this->dataroot/$dirPath/$file"), $strftime);
                    array_push($dirItems,$thisItem);
                
                } else {
                    $pathinfo=pathinfo($this->dataroot.'/'.$dirPath.'/'.$file);
                    $tmp=substr($dirPath,strpos($dirPath,"/")+1);
                    if (! skip_folder($file)) { 
                        $thisItem = array();
                        $thisItem['name'] = $file;
                        $thisItem['path'] = '/file.php/'.$dirPath.'/'.$file;
                        $thisItem['extension'] = $pathinfo['extension'];
                        $thisItem['type'] = "file";
                        $thisItem['size'] = display_size(filesize("$this->dataroot/$dirPath/$file"));
                        $thisItem['modified'] = userdate(filemtime("$this->dataroot/$dirPath/$file"), $strftime);
                        array_push($dirItems,$thisItem);
                    }
                }
            }
        }
        closedir($handle);
        if ($depth > 1) {   // we're recursing, return the items to self
            return $dirItems;
        } else { // we've finished, set the internal property
        	$this->dirItems = $dirItems;
        }
    }

    /**
     * Output the JavaScript content needed to declare JS vars that will be used
     * client-side to render the tree
     * @return string   JS content wrapped in <script> container
     */
    function java_script_inline() {
        return "<script type=\"text/javascript\">\n" .
            "//<![CDATA[\n" .
            "var dirTreeYUIName = '" . $this->dirName . "';\n" .
            "var dirTreeStaticName = '" . $this->staticDirName . "';\n" .
            "var dirTreeWidth = '" . $this->width . "';\n" .
            "var dirTreeBaseURL = '" . $this->wwwroot . "';\n" .
            "var treeJSONData = " . $this->JSON() . "\n" .
            "//]]>\n" .
            "</script>\n";
    }

    /**
     * Output the JavaScript link needed to declare JS vars that will be used
     * client-side to render the tree
     * @return string   HTML <script> element
     */
    function java_script_link() {
          return "<script type='text/javascript' defer='defer' src='" . $this->wwwroot . dir_tree::JS_INCLUDE . "'></script>";
    }

    /**
     * Return a HTML link to the stylesheet location
     * @return string   <link> tag
     */
    function css_link() {
        return "<link rel='stylesheet' href='" . $this->wwwroot . dir_tree::CSS_INCLUDE . "'></link>";
    }

    /**
     * Build a static directory tree using YUI css classes so it looks similar
     * in non-JS browsers. The tree wont expand but the items will be linked
     * @return string   HTML rendering of a directory tree
     */
    function render_static_tree() {
        $thisContent = '';
        $i = 0;
        foreach ($this->dirItems as $file) {
            if ($this->maxItems && ($i >= $this->maxItems)) {
                break;
            }
            $i++;
            $iconClass = 'icon-unknown';
            if (isset($file['extension'])) {
                if ($file['extension'] == 'folder') {
                    $iconClass = 'icon-folder';
                } else {
                    $iconClass = 'icon-' . str_replace('.gif','',mimeinfo("icon",$file['name']));
                }
            }
            $thisContent .= '<div class="ygtvitem" id="ygtv' . $i . '">
                            <table border="0" cellpadding="0" cellspacing="0"><tbody>
                            <tr><td id="ygtvt' . $i . '" class="ygtvtn"><div class="ygtvspacer"></div></td>
                                <td><a id="ygtvlabelel' . $i . '" title="' . $file['name'] . '" class="' . $iconClass . '" href="' . $this->wwwroot . $file['path'] . '">' .
                                dir_tree::fit_string($file['name'], $this->namelen_narrow) . '</a></td></tr>
                            </tbody></table>
                            </div>';
        }
        return "<div id='" . $this->staticDirName . "'>" . $thisContent . '</div>';
    }


    /**
     * This function cuts the middle out of a string longer than $length and replaces
     * it with '...' 
     * @param string $string - name of the capability (or debugcache or clearcache)
     * @param integer $length - max length of the display string
     * @return string
     */
    public static function fit_string($string, $length) {
        if(strlen($string) <= $length) {
            return $string;
        }
        $str1 = substr($string, 0, $length-8);
        $str2 = substr($string, -5);
        return $str1 . "..." . $str2;
    }


}


/**
 * Some folders we dont want to display to users, return true if this is one
 * of them. 
 * @param string $foldername - folder name to check
 * @return bool
 */
function skip_folder($foldername) {
    if ((substr($foldername,0,7) == "moddata")
     || (substr($foldername,0,10) == "backupdata")
     || (substr($foldername,0,10) == "ipodcast")
     ) {
        return true;
    } else {
        return false;
    }
}

?>
