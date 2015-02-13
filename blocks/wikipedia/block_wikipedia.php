<?php // $Id: block_wikipedia.php,v 1.10 2007/08/13 16:58:02 mchurch Exp $ 

/**
 * Wikipedia Block is a Moodle block to search in the Wikipedia.
 * http://dix.osola.com
 * 
 * Based on initial work of Aggelos Panagiotakis
 * Thanks to Mitsuhiro Yoshida for the Japanese Wikipedia logo
 * 
 * @author David Horat
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package contrib/plugins/blocks/wikipedia
 */

class block_wikipedia extends block_base {
	
    /**
     * Initialize the block
     */
    function init() {
            $this->title = 'Wikipedia';
            $this->content_type = BLOCK_TYPE_TEXT;
            $this->version = 2007032401;
    }
	
    /**
     * Makes the content accesible for Moodle
     */
    function get_content() {
        global $CFG;
        
        if ($this->content !== NULL) {
            return $this->content;
        }
		
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
		
        // Select the logo to show
	//	if (file_exists($CFG->dirroot.'/blocks/wikipedia/img/wikipedia-'.$lang.'.gif')) 
	//		$logofile = $CFG->wwwroot.'/blocks/wikipedia/img/wikipedia-'.$lang.'.gif';
	//	else
			$logofile = $CFG->wwwroot.'/blocks/wikipedia/img/wikipedia-default.gif';
            
        $wikilogo = '<img src="'.$logofile.'" alt="Wikipedia" width="122" height="36" />';
		
		$form = '<form action="http://www.wikipedia.org/search-redirect.php" id="searchform">';
        $form .= '<div>';
        $form .= '<input type="text" name="search" accesskey="f" value=""/>';
        $form .= '<select id="language" name="language" >';
        
        $searchlang = array('de','en','el','es','fr','it','ja','nl','no','pl','pt','ru','fi','sv','zh');
        $form .= $this->language_options($searchlang);
        
        $form .= '</select>';
        $form .= '<input type="submit" name="go" class="searchButton" id="searchGoButton" value=">" />';
        $form .= '</div>';
        $form .= '</form>';

        $this->content->text = $wikilogo.$form;
       
        return $this->content;
    }

    /**
     * This method returns an html option tag with the corresponding language
     * value for each of the values in the array passed by parameter. 
     * In case that the default searching language is the same as the
     * current searching language that we are processing, then the output tag
     * will have the selected attribute with value "selected".
     * 
     * @param array $searchlang The languages
     * @return string
     */
    private function language_options($searchlang) {
        include('language_names.php');
        $defaultsearchlang = substr(current_language(), 0, 2);
        $output = '';
        
        foreach($searchlang as $value) {
            $output .= '<option value="'.$value.'" lang="'.$value
                .'" xml:lang="'.$value;
        
            if($value == $defaultsearchlang) {
                $output .= '" selected="selected">';
            } else {
                $output .= '">';
            }
                    
            $output .= $wikipedialangname[$value];    
            $output .= '</option>';
        }
        
        return $output;        
    }

}

?>