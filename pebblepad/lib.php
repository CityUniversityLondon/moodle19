<?php
include_once('pp_mime_type.php');

// leap2a builder class ////////////////////////////////////////////////
class leap2a {
	
	// construct object //////////
	public function __construct() {
		global $CFG, $USER;
		$this->portfolio_url            = $CFG->wwwroot;
		$this->data_root		= $CFG->dataroot;
		$this->pebble_root		= $CFG->pebbleroot;
		$this->dirroot                  = $CFG->dirroot;
		$this->shared_secret            = $CFG->sharedsecret;
		$this->pebble_user		= $USER->username;
		$this->user_id 			= $USER->id;
		$this->author_name 		= $USER->firstname.' '.$USER->lastname;
		$this->author_email             = $USER->email;
		$this->xml			= '';
		$this->type			= '';
		$this->file_array		= array();
		$this->selection_array          = array();
		$this->objects_array            = array();
		
		// header
		$this->header = '
		<feed xmlns:portfolio="'.$this->get_portfolio_url().'/'.$this->get_user_id().'/" 
		xmlns:localschemes="http://www.pebblepad.co.uk/leap2a#" 
		xmlns:leaptype="http://wiki.cetis.ac.uk/2009-03/LEAP2A_types#" 
		xmlns:leap="http://wiki.cetis.ac.uk/2009-03/LEAP2A_predicates#" 
		xmlns:categories="http://wiki.cetis.ac.uk/2009-03/LEAP2A_categories/" 
		xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
		xmlns:thr="http://purl.org/syndication/thread/1.0" 
		xmlns="http://www.w3.org/2005/Atom">
			<title type="text">Portfolio Items</title>
			<id>'.$this->get_portfolio_url().'/'.$this->get_user_id().'/</id>
			<updated>'.get_rfc3339_datetime().'</updated>
			<author>
				<name>'.$this->get_author_name().'</name>
				<email>'.$this->get_author_email().'</email>
			</author>';
		
		// footer
		$this->footer = '
		</feed>';
	}

        function get_dir_root() {
            return $this->dirroot;
        }

	// portfolio url //////////
	function get_portfolio_url() {
		return $this->portfolio_url;
	}
	
	// data root //////////
	function get_data_root() {
		return $this->data_root;
	}
	
	// pebble root //////////
	function get_pebble_root() {
		return $this->pebble_root;
	}
	
	// shared secret //////////
	function get_shared_secret() {
		return $this->shared_secret;
	}
	
	// pebble user //////////
	function get_pebble_user() {
		return $this->pebble_user;
	}
	
	// user id //////////
	function get_user_id() {
		return $this->user_id;
	}
	
	// author name //////////
	function get_author_name() {
		return $this->author_name;
	}
	
	// author email //////////
	function get_author_email() {
		return $this->author_email;
	}
	
	// xml header and footer //////////
	function get_header() {
		return $this->header;
	}
	
	function get_footer() {
		return $this->footer;
	}
	
	// xml functions //////////
	function add_to_xml($xml) {
		$this->xml .= $xml;
	}
	
	function get_xml() {
		return $this->xml;
	}
	
	/**
        * @param $type  asset type as string
        */
	function set_type($type) {
		$this->type = $type;
	}
	
	function get_type() {
		return $this->type;
	}
	
	// files //////////
	function add_file($file_path, $file_name, $unlink) {
		$this->file_array[$file_path] = array(
			'file_name' => $file_name,
			'unlink'	=> $unlink
		);
	}
	
	function get_file_array() {
		return $this->file_array;
	}
	
	// entries //////////
	function set_objects($objects_array, $type, $level = 2) {
        
            if (!is_array($objects_array)) {
                            $objects_array = array($objects_array);
            }
        
            if (array_key_exists($type, $this->objects_array)) {
                    foreach ($objects_array as $object) {
                            array_push($this->objects_array[$type], $object);
                            array_push($this->selection_array[$type], $object);
                    }
            } else {
                    $this->objects_array[$type] = $objects_array;
                    $this->selection_array[$type] = $objects_array;
            }

            foreach ($this->selection_array as $obj_type => $obj_objects_array) {
                    foreach ($obj_objects_array as $obj_object) {
                            if ($type == $obj_type) {
                                    $obj_object->leap_level = $level;
                            }
                    }
            }
	}
	
	function get_objects() {
		return $this->objects_array;
	}
	
        function get_object($object) {

            if (class_exists('leap2a_'.$this->get_type().'_entry')) {
                $class = 'leap2a_'.$this->get_type().'_entry';
            } else {
                $class = 'leap2a_standard_entry';
            }            

            $entry = new $class($object);

            $entry->add_to_xml($entry->get_header());
            $entry->set_type($this->get_type());

            // file attachments based on each entry
            if ($entry->get_file() && pebble_mime_content_type($entry->get_file_location().'/'.$entry->get_file())) {
                
                if ($this->get_type() != 'file') {
                  

                    $entry->append_to_content('
                        <link rel="related_by" href="portfolio:'.($this->get_type() ? $this->get_type().'/files/' : '').$entry->get_id().'" />');
                    $entry->add_to_xml($entry->get_entry_xml($this->selection_array));
                    $entry->add_to_xml($entry->get_footer());
                    $entry->add_to_xml($entry->get_header());
                    $entry->set_type($this->get_type().'/files');
                    $entry->set_title('<title>'.$entry->get_file().'</title>');
                    $entry->set_content('
                        <link rel="related" href="portfolio:'.($this->get_type() ? $this->get_type().'/' : '').$entry->get_id().'" />
                        <content type="'.pebble_mime_content_type($entry->get_file_location().'/'.$entry->get_file()).'" src="files/'.$entry->get_file().'" />
                        <rdf:type rdf:resource="leaptype:resource" />');

                    $entry->add_to_xml($entry->get_entry_xml());
                    $entry->add_to_xml($entry->get_footer());
                } else {

                    $entry->add_to_xml($entry->get_entry_xml());
                    $entry->add_to_xml($entry->get_footer());
                }

                // add files to array 
                $this->add_file($entry->get_file_location().$entry->get_file(), 'files/'.$entry->get_file(), false);
            } else {

                $entry->add_to_xml($entry->get_entry_xml($this->selection_array));
                $entry->add_to_xml($entry->get_footer());
            }

            if ($entry->get_file_array()) {
                foreach ($entry->get_file_array() as $file_location => $files) {
                    $this->add_file($file_location, $files['file_name'], $files['unlink']);
                }
            }

            return $entry->get_xml();
        }
	
	function build_zip($export_to_zip = false, $auto = 'false') {
		
		$zip = new ZipArchive();
		$user_dir = "";
                if ($user_dir = make_user_directory($this->get_user_id())){
                    $zip_name = $user_dir .'/export_'.$this->get_user_id().'_'.time().'.zip';
                }else{
                    return "Unable to create file storage. Please contact your Moodle administrator";
                }
                //$zip_name 	= $this->get_data_root().'/user/0/'.$this->get_user_id().'/export_'.$this->get_user_id().'_'.time().'.zip';
		$zip_url	= 'export_'.$this->get_user_id().'_'.time().'.zip';
		$zip->open($zip_name, ZIPARCHIVE::CREATE);

                if ($auto != 'true'){$auto = 'false';}

                $this->add_to_xml($this->get_header());
                
                $assetCnt = 0;

		// process entries
		foreach ($this->get_objects() as $type => $objects_array) {
                    $this->set_type($type);
                    foreach ($objects_array as $object) {
                        $assetCnt += 1;
                        $this->add_to_xml($this->get_object($object));
                    }
		}

		$this->add_to_xml($this->get_footer());
		
		// create xml file
		$zip->addFromString('leap2a.xml', $this->get_xml());
		
		// loop through files to add
		foreach ($this->get_file_array() as $location => $files) {
			$zip->addFile($location, $files['file_name']);
                        $assetCnt += 1;
		}
		
		$zip->close();
		
		// remove temp images - not necessary but can't hurt
		foreach ($this->get_file_array() as $location => $files) {
			if ($files['unlink'] == 1) {
				unlink($location);
			}
		}

                if ($assetCnt <= 10){
                   $auto = 'true';
                }
                
		// digest auth using curl
		if ($export_to_zip == 0) {
			
			$message = "";
			$sharedsecret = $this->get_shared_secret(); 
			$username = $this->get_pebble_user();
			$timestamp = time();	
			$md5_hash = md5($timestamp . $username . $sharedsecret);
			
			$path_to_cookie = $user_dir.'/ssoCookies.txt';
			
			$import_url = $this->get_pebble_root()."/pebblepad.aspx?username=$username&timestamp=$timestamp&MAC=$md5_hash";
			
			$ch = curl_init();
	
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_URL, $import_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $path_to_cookie);
                        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$http_result = curl_exec($ch);
			$http_error = curl_error($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			if ($http_code == 200 && ((strlen(strstr($http_result,'Login'))==0))){
				$data['auto'] = $auto;
				$data['fileupload'] = '@' . $zip_name . '';
				
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
							
				$neat_upload = rand(10,50);
				curl_setopt($ch, CURLOPT_URL, $this->get_pebble_root()."/interop/import.aspx?NeatUpload_PostBackID=" . $neat_upload);
				curl_setopt($ch, CURLOPT_COOKIEFILE, $path_to_cookie);
                                //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                
				$http_result = curl_exec($ch);
				$error = curl_error($ch);
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

				curl_close($ch);
				
				if ($auto == 'false' && $http_code == 200){
					$md5_hash = md5($timestamp . $username . $sharedsecret);
					$url = $this->get_pebble_root()."/pebblepad.aspx?username=$username&timestamp=$timestamp&MAC=$md5_hash";

                                        header("Location: " . $url . "&referer=".$this->get_pebble_root()."/interop/importselection.aspx");
				}elseif($http_code == 200){
                                    echo'<script>
                                            setTimeout("self.close()",400);
                                            window.alert("Asset saved! Press OK to continue.");
                                        </script>';
                                }else{
                                    $http_code = "Error! unable to connect to portfolio. code ".$http_code;
                                }
			}else{
                             $http_code = "Error! unable to connect to portfolio. User not found";
                        }
			unlink($zip_name);
		} else {
                        if (file_exists($this->get_dir_root().'/blocks/pebblepad/leap2azip.php')){
                           include_once($this->get_dir_root() . '/blocks/pebblepad/leap2azip.php');
                           $loc = 'Location: '.$this->get_portfolio_url().'/blocks/pebblepad/leap2azip.php?zp='.urlencode(base64_encode($zip_name)).'&zn='.urlencode(base64_encode('moodle_leap2a_export_'.time().'.zip'));
                           header($loc);                           
                           exit();
                        }
                }
		return $http_code;
	}
}

// standard entry class ////////////////////////////////////////////////
class leap2a_standard_entry extends leap2a {
	public function __construct($object) {
		parent::__construct();		
		
                $this->id               = $object->id;
		if (@$object->userid) {
                    $this->entry_user	= $object->userid;
		}			
		$this->file		= '';
		$this->file_location 	= '';
		$this->selection_id 	= '';
		$this->selection_type	= '';
		$this->child_type	= '';
		$this->course		= '';
		$this->when_added	= '';
		$this->format		= 'text';
		$this->leap_level	= $object->leap_level;
		$this->header = '
                    <entry>';
		$this->footer = '
                    </entry>';
}
	
        function get_entry_xml($objects_array = null) {
                        $xml = '
                            <id>portfolio:'.($this->get_type() ? $this->get_type().'/' : '').$this->get_id().'</id>'.
                        $this->get_title().
                            '<published>'.get_rfc3339_datetime($this->get_published()).'</published>'.
                        $this->get_updated().
                        $this->get_content();

                        // determine relationships
                        if ($objects_array) {
                                $xml .= $this->get_relationships($objects_array);
                        }

                        if (@$this->entry_user) {
                            
                                if ($this->get_user_id() != $this->entry_user && $this->leap_level == 3) {
                                        $xml .= '
                                        <author>
                                                <name>A.N. Other</name>
                                        </author>';
                                }
                        }

                return $xml;
        }
	
        // get relationships
        function get_relationships($objects_array) {
                
                $is_selection = false;
                $xml = '';
                foreach ($objects_array as $type => $object_array) {
                        foreach ($object_array as $object) {
                                
                                if ($type == 'blog_post') {
                                        $object->blog = 0;
                                }

                                if ( $this->get_selection_type() == "" ){
                                    switch ($type){
                                        case 'reasons':
                                            $xml .= '<link rel="leap:supported_by" href="portfolio:'.$type.'/'.$object->id.'" />';
                                            break;
                                        case 'impact':
                                            $xml .= '<link rel="leap:supported_by" href="portfolio:'.$type.'/'.$object->id.'" />';
                                            break;
                                        case 'gains':
                                            $xml .= '<link rel="leap:supported_by" href="portfolio:'.$type.'/'.$object->id.'" />';
                                            break;
                                        case 'evidence':
                                            $xml .= '<link rel="leap:has_evidence" href="portfolio:'.$type.'/'.$object->id.'" />';
                                            break;
                                        case 'reflection':
                                            $xml .= '<link rel="leap:reflected_on_by" href="portfolio:'.$type.'/'.$object->id.'" />';
                                            break;
                                    }
                                }

                                if ($this->get_selection_type() == $type && $this->get_selection_id() == $object->id) {
                                        if ($object->leap_level == 2) {
                                                $xml .= '
                                                <link leap:display_order="'.$this->get_id().'" leap:when_added="'.get_rfc3339_datetime($this->get_published()).'" rel="leap:is_part_of" href="portfolio:'.$this->get_selection_type().'/'.$this->get_selection_id().'" />';
                                        } else if ($object->leap_level == 3) {
                                                $xml .= '
                                                <thr:in-reply-to ref="portfolio:'.$this->get_selection_type().'/'.$this->get_selection_id().'" />';
                                        }

                                }

                                if ($this->get_child_type() != '' && $this->get_child_type() == $type) {
                                    if ($this->get_id() == $object->{$this->get_type()}) {
                                            if ($object->leap_level == 2) {
                                                    $xml .= '
                                                    <link leap:display_order="'.$object->id.'" leap:when_added="'.get_rfc3339_datetime($object->{$this->get_when_added()}).'" rel="leap:has_part" href="portfolio:'.$type.'/'.$object->id.'" />';
                                                    $is_selection = true;
                                            } else if ($object->leap_level == 3) {
                                                    $xml .= '
                                                    <link rel="replies" href="portfolio:'.$type.'/'.$object->id.'" />';
                                            }
                                    }
                                }

                        }
                }

                if ($is_selection) {
                        $xml .= '
                        <rdf:type rdf:resource="leaptype:selection" />';
                }

                return $xml;
        }
	
	// entry id //////////
	function set_id($id) {
		$this->id = $id;
	}
	
	function get_id() {
		return $this->id;
	}
	
	// entry title //////////
	function set_title($title) {
		$this->title = $title;
	}
	
	function get_title() {
		return $this->title;
	}
	
	// entry published //////////
	function set_published($published) {
		$this->published = $published;
	}
	
	function get_published() {
		return $this->published;
	}
	
	// entry updated //////////
	function set_updated($updated) {
		$this->updated = $updated;
	}
	
	function get_updated() {
		return $this->updated;
	}
	
	// entry content //////////
	function set_content($content) {
		$this->content = $content;
	}
	
	function append_to_content($content) {
		$this->content .= $content;
	}
	
	function get_content() {
		return $this->content;
	}
	
	// get attached file //////////
	function set_file($file) {
		$this->file = $file;
	}
	
	function get_file() {
		return $this->file;
	}
	
	function get_file_location() {
		return $this->file_location;
	}
	
	// entry selection id //////////
	function set_selection_id($selection_id) {
		$this->selection_id = $selection_id;
	}
	
	function get_selection_id() {
		return $this->selection_id;
	}
	
	// entry selection type //////////
	function set_selection_type($selection_type) {
		$this->selection_type = $selection_type;
	}
	
	function get_selection_type() {
		return $this->selection_type;
	}
	
	// entry child type //////////
	function set_child_type($child_type) {
		$this->child_type = $child_type;
	}
	
	function get_child_type() {
		return $this->child_type;
	}
	
	// entry course id //////////
	function set_course_id($course_id) {
		$this->course = $course_id;
	}
	
	function get_course_id() {
		return $this->course;
	}
	
	// entry course id //////////
	function set_when_added($datetime) {
		$this->when_added = $datetime;
	}
	
	function get_when_added() {
		return $this->when_added;
	}

        function set_localscheme($str){
            $this->localscheme->$str;
        }
        function get_localscheme(){
            return $this->localscheme;
        }

}

// blog entry class ////////////////////////////////////////////////////
class leap2a_blog_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);
		$this->child_type		= 'blog_post';
		$this->course			= $object->course;
		$this->when_added		= 'created';
		
		$this->title 			= '
			<title>'.$this->get_author_name().'\'s Blog</title>';
		$this->published 		= time();
		$this->updated 			= '
			<updated>'.get_rfc3339_datetime().'</updated>';
		$this->content 			= '
			<content>'.$this->get_author_name().'\'s Blog</content>';
	}
}

// blog_post entry class ///////////////////////////////////////////////
class leap2a_blog_post_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);
		$this->selection_id		= $object->courseid;
		$this->selection_type           = 'blog';
		
		$this->title 			= '
			<title>'.$object->subject.'</title>';
		$this->published 		= $object->created;
		$this->updated 			= '
			<updated>'.get_rfc3339_datetime($object->lastmodified).'</updated>';
		$this->content 			= '
			<content><![CDATA['.$object->summary.']]></content>';
		
		$this->file 			= $object->attachment;
		$this->file_location            = $this->get_data_root().'/blog/attachments/'.$this->get_id().'/';
	}
}

// discussion entry class //////////////////////////////////////////////
class leap2a_discussion_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);
		$this->selection_id		= $object->forum;
		$this->selection_type           = 'forum';
		$this->child_type		= 'post';
		$this->course			= $object->course;
		$this->when_added		= 'created';		
		
		$this->title 			= '
			<title>'.$object->name.'</title>';
		$this->published 		= $object->timemodified;
		$this->content 			= '
			<content>Forum Discussion</content>';
		$this->updated 			= '
			<updated>'.get_rfc3339_datetime($object->timemodified).'</updated>';
		
		$this->file 			= '';
		$this->file_location            = '';
	}
}

// forum post class ////////////////////////////////////////////////////
class leap2a_post_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);
		$this->selection_id		= $object->discussion;
		$this->selection_type           = 'discussion';
		$this->course			= $object->course;
		
		$this->title 			= '
			<title>'.$object->subject.'</title>';
		$this->published 		= $object->created;
		$this->content 			= '
			<content><![CDATA['.$object->message.']]></content>';
		$this->updated 			= '
			<updated>'.get_rfc3339_datetime($object->modified).'</updated>';
		
		$this->file 			= $object->attachment;
		$this->file_location            = $this->get_data_root().'/'.$this->get_course_id().'/moddata/forum/'.$object->forum.'/'.$this->get_id().'/';
	}
}

class leap2a_thought_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);

                $now                    = get_rfc3339_datetime();
                $this->id		= time();
		$this->type             = $object->type;
                $this->child_type	= 'reflection';
		$this->when_added	= 'created';
		$this->title 		= '
                    <title type="text">'.$object->title.'</title>';
		$this->published 	= $object->timemodified;
                $this->updated 		= '
                    <updated>'.$now.'</updated>';
		$this->content 		= '
                    <content type="text">'.$object->description.'</content>';
                if (!empty($object->points)){
                    $this->content .= '
                    <localschemes:points>'.$object->points.'</localschemes:points>';
                }
                if (!empty($object->hrs) || !empty($object->mins)){
                    $this->content .= '
                    <leap:activetime>PT'.$object->hrs.'H'.$object->mins.'M</leap:activetime>';
                }
                if ( !empty($object->startdate) ){
                    $this->content .= '
                    <leap:date leap:point="start">'.$object->startdate.'</leap:date>';
                }
                if ( !empty($object->enddate) ){
                    $this->content .= '
                    <leap:date leap:point="end">'.$object->enddate.'</leap:date>';
                }
	}
}

class leap2a_activity_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);

                $now                        = get_rfc3339_datetime();
                $this->id                   = time();
                $this->type                 = $object->type;
                $this->child_type           = 'reflection';
                $this->when_added           = 'created';
                $this->title                = '
                    <title type="text">'.$object->title.'</title>';
                $this->published            = $object->timemodified;
                $this->updated              = '
                    <updated>'.$now.'</updated>';
                $this->content              = '
                    <category term="Development" scheme="categories:life_area#" />';
                $this->content             .= '
                    <rdf:type rdf:resource="leaptype:activity" />';
                $this->content             .= '
                    <content type="text">'.$object->description.'</content>';

                if (!empty($object->points)){
                    $this->content .= '
                    <localschemes:points>'.$object->points.'</localschemes:points>';
                }
                if (!empty($object->hrs) || !empty($object->mins)){
                    $this->content .= '
                    <leap:activetime>PT'.$object->hrs.'H'.$object->mins.'M</leap:activetime>';
                }
                if ( !empty($object->startdate) ){
                    $this->content .= '
                    <leap:date leap:point="start">'.$object->startdate.'</leap:date>';
                }
                if ( !empty($object->enddate) ){
                    $this->content .= '
                    <leap:date leap:point="end">'.$object->enddate.'</leap:date>';
                }
	}
}

class leap2a_ability_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);

                $now                            = get_rfc3339_datetime();
                $this->id                       = time();
		$this->type                     = $object->type;
                $this->child_type		= 'reflection';
		$this->when_added		= 'created';

		$this->title 			= '
                    <title type="text">'.$object->title.'</title>';
		$this->published 		= $object->timemodified;
                $this->updated 			= '
                    <updated>'.$now.'</updated>';
		$this->content 			= '
                    <content type="text">'.$object->description.'</content>';
                $this->content                 .= '
                    <rdf:type rdf:resource="leaptype:ability" />';

                if (!empty($object->points)){
                    $this->content .= '
                        <localschemes:points>'.$object->points.'</localschemes:points>';
                }
                if (!empty($object->hrs) || !empty($object->mins)){
                    $this->content .= '
                        <leap:activetime>PT'.$object->hrs.'H'.$object->mins.'M</leap:activetime>';
                }
                if ( !empty($object->startdate) ){
                    $this->content .= '
                        <leap:date leap:point="start">'.$object->startdate.'</leap:date>';
                }
                if ( !empty($object->enddate) ){
                    $this->content .= '
                        <leap:date leap:point="end">'.$object->enddate.'</leap:date>';
                }
	}
}

class leap2a_reasons_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);

                $now                    =   get_rfc3339_datetime();
                $this->id               =   $object->get_id();
                $this->selection_type	=   $object->get_type();
                $this->title 		=   '<title type="text">'.$object->get_title().'</title>';
                $this->updated 		=   '<updated>'.$now.'</updated>';
                $this->content          =   '<link rel="leap:supports" href="portfolio:'.$object->get_selection_type().'/'.$object->id.'" />';
                $this->content         .=   '<category term="Reason" scheme="localschemes:cpd_details" />';
                $this->content         .=   '<content type="text">'.$object->get_text().'</content>';

	}
}

class leap2a_impact_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);

                $now                    =   get_rfc3339_datetime();
                $this->id               =   $object->get_id();
                $this->selection_type	=   $object->get_type();
                $this->title 		=   '<title type="text">'.$object->get_title().'</title>';
                $this->updated 		=   '<updated>'.$now.'</updated>';
                $this->content          =   '<link rel="leap:supports" href="portfolio:'.$object->get_selection_type().'/'.$object->id.'" />';
                $this->content         .=   '<category term="Impact" scheme="localschemes:cpd_details" />';
                $this->content         .=   '<content type="text">'.$object->get_text().'</content>';

        }
}

class leap2a_gains_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);

                $now                    =   get_rfc3339_datetime();
                $this->id               =   $object->get_id();
                $this->selection_type	=   $object->get_type();
                $this->title            =   '<title type="text">'.$object->get_title().'</title>';
                $this->updated 		=   '<updated>'.$now.'</updated>';
                $this->content          =   '<link rel="leap:supports" href="portfolio:'.$object->get_selection_type().'/'.$object->id.'" />';
                $this->content         .=   '<category term="Gain" scheme="localschemes:cpd_details" />';
                $this->content         .=   '<content type="text">'.$object->get_text().'</content>';

	}
}

class leap2a_evidence_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);

                $now                    =   get_rfc3339_datetime();
                $this->id               =   $object->get_id();
                $this->selection_type	=   $object->get_type();
                $this->title		=   '<title type="text">'.$object->get_title().'</title>';
                $this->updated		=   '<updated>'.$now.'</updated>';
                $this->content          =   '<link rel="leap:is_evidence_of" href="portfolio:'.$object->get_selection_type().'/'.$object->id.'" />';
                $this->content         .=   '<content type="text">'.$object->get_text().'</content>';

	}
}

class leap2a_reflection_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);

                $now                    =   get_rfc3339_datetime();
                $this->id               =   $object->id;
                $this->selection_type	=   $object->get_type();
                $this->title 		=   '<title type="text">'.$object->title.'</title>';
                $this->updated 		=   '<updated>'.$now.'</updated>';
                $this->content          =   '<link rel="leap:reflects_on" href="portfolio:'.$object->get_type().'/'.$object->id.'" />';
                $this->content         .=   '<content type="text">'.$object->text.'</content>';

	}
}

// file entry class ////////////////////////////////////////////////////
class leap2a_file_entry extends leap2a_standard_entry {
	public function __construct($object) {
		parent::__construct($object);

                $this->format 			= $object->type;
		$this->course			= $object->course;
		$this->title 			= '<title>'.$object->name.'</title>';
		$user_dir = make_user_directory($this->get_user_id()); //M.HUGHES this should be here so is available to all statements in the following if loop

		if ($this->format == 'file') {
                    if (empty($this->file)){
                        $this->file = $object->file;
                    }

                    //$user_dir = make_user_directory($this->get_user_id()); //M.HUGHES This needs to be outside the if loop
                    //try to get the mime type of file
                    $mime_type = pebble_mime_content_type($user_dir.'/'.$object->reference);

                    if (empty($mime_type)){ 

                        $mime_type = $object->file['file']['type'];

                        $this->content 		= '
                            <summary type="text">'.$object->description.'</summary>';
                        $this->content 		.= '
                            <content'.($mime_type ? ' type="'.$mime_type.'"' : '').' src="'.$object->file['file']['name'].'" />
                                        <rdf:type rdf:resource="leaptype:resource" />';
                    }else{
                        $this->content 		= '
                                        <content'.($mime_type ? ' type="'.$mime_type.'"' : '').' src="'.($mime_type ? 'files/' : '').$object->reference.'" />
                                        <rdf:type rdf:resource="leaptype:resource" />';
                    }

                    if (!empty($object->points)){
                        $this->content .= '
                            <localschemes:points>'.$object->points.'</localschemes:points>';
                    }
                    if (!empty($object->hrs) || !empty($object->mins)){
                        $this->content .= '
                            <leap:activetime>PT'.$object->hrs.'H'.$object->mins.'M</leap:activetime>';
                    }
                    if ( !empty($object->startdate) ){
                        $this->content .= '
                            <leap:date leap:point="start">'.$object->startdate.'</leap:date>';
                    }
                    if ( !empty($object->enddate) ){
                        $this->content .= '
                            <leap:date leap:point="end">'.$object->enddate.'</leap:date>';
                    }

				
		} elseif ($this->format == 'text') {
			$temp_file_name 	= $object->name.'.txt';
			$temp_file_location     = $user_dir.'/'.$temp_file_name;
			$tfp 			= fopen($temp_file_location, 'w');
			
			fwrite($tfp, $object->alltext);
			fclose($tfp);
			
			$this->add_file($user_dir.'/'.$temp_file_name, 'files/'.$temp_file_name, true);
			
			$this->content 		= '
				<content type="'.pebble_mime_content_type($temp_file_location).'" src="files/'.$temp_file_name.'" />
				<rdf:type rdf:resource="leaptype:resource" />';
					
		} elseif ($this->format == 'html') {
		
                        $dest_path = $user_dir.'/';
			$dest_file_array = array();
			
			$html_page = $object->name.'.html';
		
			if ($image_links = extract_images($this->get_portfolio_url(), $object->alltext)) {
                            foreach ($image_links as $value) {
                                $urlBits = parse_url ($value);

                                if (substr_count($urlBits['path'], 'file.php') > 0) {

                                    $file_path = explode('file.php', $urlBits['path']);
                                    $file_path = $this->get_data_root() . $file_path[1];
                                } elseif (substr_count($this->get_portfolio_url(), $urlBits['host']) > 0) {

                                    $file_path = substr_replace($urlBits['path'], '', 0, strpos($urlBits['path'], '/', 2));
                                    $file_path = explode('file.php', $file_path);
                                    $file_path = $this->get_dir_root() . $file_path[0];
                                } else {
                                    $file_path = $value;
                                }
                                
                                    $file_path = str_replace("\\", "/", $file_path);
                                    $file_name = explode('/', $file_path);

                                    if (copy($file_path, $dest_path.$file_name[sizeof($file_name) - 1])){
                                       $dest_file_array[$file_name[sizeof($file_name) - 1]] = $dest_path.$file_name[sizeof($file_name) - 1];
                                       $object->alltext = str_replace($value, $file_name[sizeof($file_name) - 1], $object->alltext);
                                
                                    }                               
                            }
                        }
		
			if ($dest_file_array) {
				$website_zip = new ZipArchive();
				$website_zip->open($dest_path.$object->name.'.zip', ZIPARCHIVE::CREATE);
				foreach ($dest_file_array as $file_name => $file_location) {
					$website_zip->addFile($file_location, $file_name);
				}
				$html_page = 'index.html';
			}
			
			$fp = fopen($dest_path.$html_page, 'w');
			fwrite($fp, $object->alltext);

			fclose($fp);
			
			if ($dest_file_array) {
				$website_zip->addFile($dest_path.$html_page, $html_page);
				$website_zip->close();
				unlink($dest_path.$html_page);
				foreach ($dest_file_array as $file_name => $file_location) {
					unlink($file_location);
				}
			}
			
			$this->add_file(($dest_file_array ? $dest_path.$object->name.'.zip' : $dest_path.$html_page), 'files/'.($dest_file_array ? $object->name.'.zip' : $html_page), true);
                
			$this->content 		= '
				<content type="'.pebble_mime_content_type(($dest_file_array ? $dest_path.$object->name.'.zip' : $dest_path.$html_page)).'" src="files/'.($dest_file_array ? $object->name.'.zip' : $html_page).'" />
				<rdf:type rdf:resource="leaptype:resource" />';
				
			if ($dest_file_array) {
				$this->content 	.= '
					<category scheme="localschemes:entry_type_hint" term="ZippedWebsite" />
                                        <category scheme="localschemes:entry_type_hint" term="InlineFile" />
                                        ';
			} else {
				$this->content 	.= '
					<category scheme="localschemes:entry_type_hint" term="InlineFile" />';
			}
		
		}
			
		$this->published 		= $object->timemodified;
		$this->updated 			= '
			<updated>'.get_rfc3339_datetime($object->timemodified).'</updated>';
		
		$this->file 			= $object->reference;
		$this->file_location	= $this->get_data_root().'/'.$this->get_course_id().'/';
	}
}

// leap2a moodle object class //////////////////////////////////////////
class leap2a_moodle_object {
	public function __construct($parameters) {		
        if ($parameters) {
			foreach ($parameters as $key => $val) {
				$this->{$key} = $val;
			}
		}
	}
}

// rfc 3339 date ///////////////////////////////////////////////////////
function get_rfc3339_datetime($timestamp = null) {
	if (empty($timestamp)) {
		$timestamp = time();
	}
	$date = date('Y-m-d\TH:i:s', $timestamp);
    
	$matches = array();
	if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) {
		$date .= $matches[1].$matches[2].':'.$matches[3];
	} else {
		$date .= 'Z';
	}

    return $date;
}

// extract images from webpage /////////////////////////////////////////
function extract_images($page, $file_contents) {

	$pattern = "/<img.*?src.*?=.*?(\"|')(.*?)(\"|')/i";
	preg_match_all($pattern, $file_contents, $images);

        if ($images) {
            return $images[2];
        } else {
                return false;
        }
	
}

?>
