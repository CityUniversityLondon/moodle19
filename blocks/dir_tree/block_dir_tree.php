<?php
/**
 * BLOCK: dir_tree
 * AUTHOR: Dean Stringer (University of Waikato, NZ)
 * DESCRIPTION:
 * 
 * Moodle block to handle display of directory resources in a YUI TreeView widget
 * Includes a bunch of config settings that allow a teacher/admin to specify:
 *  - which folder from their resources directory should be displayed
 *  - how deeply the folders should be crawled
 *  - what the name of the block should be
 * 
 * After fetching the directory contents the data is echod out to the browser 
 * encoded in JSON format so that the client can invoke and render the tree 
 * using the YUI JS libraries.
 * 
 * The client needs to load dir_tree.js and the YUI JS libs, and also needs a <DIV>
 * element to pour the tree into. The get_content() sub following handles this.
 * dir_tree.js is loaded with a 'defer' switch and boot-straps itself when the
 * YAHOO.util.Event.onDOMReady event fires which then builds the tree in the browser 
 */

require_once ('dir_tree.php');
require_js(array('yui_yahoo', 'yui_dom', 'yui_event', 'yui_treeview'));

class block_dir_tree extends block_base {

	function init() {
		global $CFG;
		$this->title = get_string("title", "block_dir_tree");
		$this->version = 2008110601;
	}

	function instance_allow_config() {
		return true;
	}

    function specialization() {
        global $COURSE;
        // load userdefined title and make sure it's never empty
        if (empty($this->config->conf_blocklabel)) {
            $this->config->conf_blocklabel = '';
        }

        if (empty($this->config->conf_dirname)) {
            $this->config->conf_dirname = '';
        }

        if (empty($this->config->conf_maxdepth)) {
            $this->config->conf_maxdepth = 3;
        }
    }

	function get_content() {

		global $USER;
		global $COURSE;
		global $CFG;

        if(!isset($this->config)) {
            $this->config = get_config('blocks/block_dir_tree');
        }

		if ($this->config->conf_blocklabel <> '') {
			$this->title = $this->config->conf_blocklabel;
		}
		if ($this->content !== NULL) {
			return $this->content;
		}

		$this->content = new stdClass;

        $dirTree = new dir_tree;
        $dirTree->wwwroot = $CFG->wwwroot;
        $dirTree->dataroot = $CFG->dataroot;
        $dirTree->dirroot = $CFG->dirroot;
		$this->content->text .= 
		        $dirTree->java_script_link() .
                $dirTree->css_link() .
		        "<style type='text/css'> 
		        #" . $dirTree->dirName . " { background: #fff; padding:1em; margin-top:1em; padding-left: 0px; padding-top: 0px; margin-top: 0px; padding-right: 0px; } 
		        </style> 
		        <div id=\"" . $dirTree->dirName . "\"></div>";
		$this->content->footer = '';

		$relativepath = $COURSE->id;
		if ($this->config->conf_dirname <> '') {
			$relativepath = $COURSE->id . '/' . $this->config->conf_dirname;
		}
        $dirTree->crawl_directory($relativepath, $this->config->conf_maxdepth);
        $this->content->text .= $dirTree->render_static_tree();
        $this->content->text .= $dirTree->java_script_inline();

	}

}
?>