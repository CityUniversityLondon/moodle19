<?php
/*function set_id3($filename, $title = "", $author = "", $album = "", $year = "", $comment = "", $genre_id = 0) { 
            $this->error = false; 
            $this->wfh = fopen($this->file,"a"); 
            fseek($this->wfh, -128, SEEK_END); 
            fwrite($this->wfh, pack("a3a30a30a30a4a30C1", "TAG", $title, $author, $album, $year, $comment, $genre_id), 128); 
            fclose($this->wfh); 
        }*/
 
/////////////////////////////////////////////////
//Get all id3 information and decode to an array
//Takes a single parameter a full local path to file as $filename
//returns array $id3
/////////////////////////////////////////////////
function get_id3($filename) {
	$filehandle = NULL;

	$id3_genres_array = array( 
		'Blues', 'Classic Rock', 'Country', 'Dance', 'Disco', 'Funk', 'Grunge', 'Hip-Hop', 'Jazz', 'Metal', 'New Age', 'Oldies', 'Other', 'Pop', 'R&B', 'Rap', 'Reggae', 'Rock', 'Techno', 'Industrial', 
		'Alternative', 'Ska', 'Death Metal', 'Pranks', 'Soundtrack', 'Euro-Techno', 'Ambient', 'Trip-Hop', 'Vocal', 'Jazz+Funk', 'Fusion', 'Trance', 'Classical', 'Instrumental', 'Acid', 'House', 
		'Game', 'Sound Clip', 'Gospel', 'Noise', 'AlternRock', 'Bass', 'Soul', 'Punk', 'Space', 'Meditative', 'Instrumental Pop', 'Instrumental Rock', 'Ethnic', 'Gothic', 'Darkwave', 
		'Techno-Industrial', 'Electronic', 'Pop-Folk', 'Eurodance', 'Dream', 'Southern Rock', 'Comedy', 'Cult', 'Gangsta', 'Top 40', 'Christian Rap', 'Pop/Funk', 'Jungle', 'Native American', 'Cabaret', 
		'New Wave', 'Psychadelic', 'Rave', 'Showtunes', 'Trailer', 'Lo-Fi', 'Tribal', 'Acid Punk', 'Acid Jazz', 'Polka', 'Retro', 'Musical', 'Rock & Roll', 'Hard Rock', 'Folk', 'Folk/Rock', 'National Folk', 
		'Swing', 'Fast Fusion', 'Bebob', 'Latin', 'Revival', 'Celtic', 'Bluegrass', 'Avantgarde', 'Gothic Rock', 'Progressive Rock', 'Psychedelic Rock', 'Symphonic Rock', 'Slow Rock', 'Big Band', 
		'Chorus', 'Easy Listening', 'Acoustic', 'Humour', 'Speech', 'Chanson', 'Opera', 'Chamber Music', 'Sonata', 'Symphony', 'Booty Bass', 'Primus', 'Porn Groove', 'Satire', 'Slow Jam', 'Club', 'Tango', 'Samba', 
		'Folklore', 'Ballad', 'Power Ballad', 'Rhythmic Soul', 'Freestyle', 'Duet', 'Punk Rock', 'Drum Solo', 'Acapella', 'Euro-house', 'Dance Hall' 
		);

	$id3 = array(); 

	if (file_exists($filename)) { 
		$filehandle = fopen($filename,"r"); 
	} else { 
		return false;
	}         
	fseek($filehandle, -128, SEEK_END); 
	$line = fread($filehandle, 10000); 
	if (preg_match("/^TAG/", $line)) {
		$id3 = unpack("a3tag/a30title/a30author/a30album/a4year/a30comment/C1genre_id", $line); 
		$id3["genre"] = $id3_genres_array[$id3["genre_id"]]; 
		return $id3; 
	} else { 
		return false; 
	} 
	fclose($filehandle); 
} 

/////////////////////////////////////////////////
//Calculate mp3 file lendth in play time
//Used for <itunes:duration> tag
/////////////////////////////////////////////////
function calculate_length($size, $bitrate, $id3v2_tagsize = 0) { 
	$length = floor(($size - $id3v2_tagsize) / $bitrate * 0.008); 
	//Need to add hours here
	$min = floor($length / 60); 
	$min = strlen($min) == 1 ? "0$min" : $min; 
	$sec = $length % 60; 
	$sec = strlen($sec) == 1 ? "0$sec" : $sec; 
	return("$min:$sec"); 
} 

/////////////////////////////////////////////////
//Get all mpeg audio header information and decode to an array
//Takes a single parameter a full local path to file as $filename
//returns array 
/////////////////////////////////////////////////
function get_mp3_info($filename) { 
	$filehandle = NULL;
	$info = array();
	$info = NULL;	
	
	$info_bitrates = array( 
		1    =>    array( 
			1    =>    array( 0 => 0, 16 => 32, 32 => 64, 48 => 96, 64 => 128, 80 => 160, 96 => 192, 112 => 224, 128 => 256, 144 => 288, 160 => 320, 176 => 352, 192 => 384, 208 => 416, 224 => 448, 240 => false), 
			2    =>    array( 0 => 0, 16 => 32, 32 => 48, 48 => 56, 64 =>  64, 80 =>  80, 96 =>  96, 112 => 112, 128 => 128, 144 => 160, 160 => 192, 176 => 224, 192 => 256, 208 => 320, 224 => 384, 240 => false), 
			3    =>    array( 0 => 0, 16 => 32, 32 => 40, 48 => 48, 64 =>  56, 80 =>  64, 96 =>  80, 112 =>  96, 128 => 112, 144 => 128, 160 => 160, 176 => 192, 192 => 224, 208 => 256, 224 => 320, 240 => false) 
			), 
		2    =>    array( 
			1    =>    array( 0 => 0, 16 => 32, 32 => 48, 48 => 56, 64 =>  64, 80 => 80, 96 => 96, 112 => 112, 128 => 128, 144 => 144, 160 => 160, 176 => 176, 192 => 192, 208 => 224, 224 => 256, 240 => false), 
			2    =>    array( 0 => 0, 16 =>  8, 32 => 16, 48 => 24, 64 =>  32, 80 => 40, 96 => 48, 112 =>  56, 128 =>  64, 144 =>  80, 160 =>  96, 176 => 112, 192 => 128, 208 => 144, 224 => 160, 240 => false), 
			3    =>    array( 0 => 0, 16 =>  8, 32 => 16, 48 => 24, 64 =>  32, 80 => 40, 96 => 48, 112 =>  56, 128 =>  64, 144 =>  80, 160 =>  96, 176 => 112, 192 => 128, 208 => 144, 224 => 160, 240 => false) 
			) 
	);
 
	$info_versions = array(0 => "reserved", 1 => "MPEG Version 1", 2 => "MPEG Version 2", 2.5 => "MPEG Version 2.5"); 
	$info_layers = array("reserved", "Layer I", "Layer II", "Layer III"); 
	$info_sampling_rates = array( 
		0        =>    array(0 => false, 4 => false, 8 => false, 12 => false), 
		1        =>    array(0 => "44100 Hz", 4 => "48000 Hz", 8 => "32000 Hz", 12 => false), 
		2        =>    array(0 => "22050 Hz", 4 => "24000 Hz", 8 => "16000 Hz", 12 => false), 
		2.5    =>    array(0 => "11025 Hz", 4 => "12000 Hz", 8 => "8000 Hz", 12 => false) 
		); 
	$info_channel_modes = array(0 => "stereo", 64 => "joint stereo", 128 => "dual channel", 192 => "single channel"); 

	if (file_exists($filename)) { 
		$filehandle = fopen($filename,"r"); 
	} else { 
		return false;
	}  

	$finished = false; 
	rewind($filehandle); 
	while (!$finished) { 
		$skip = ord(fread($filehandle, 1)); 
		while ($skip != 255 && !feof($filehandle)) { 
			$skip = ord(fread($filehandle, 1)); 
		} 
		if (feof($filehandle)) { 
			echo "no info header found"; 
		} 
		$second = ord(fread($filehandle, 1)); 
      if ($second >= 225) { 
      	$finished = true; 
		} else if (feof($filehandle)) { 
			echo"no info header found"; 
		} 
	} 

   $third = ord(fread($filehandle, 1)); 
   $fourth = ord(fread($filehandle, 1)); 
   $info->version_id = ($second & 16) > 0 ? ( ($second & 8) > 0 ? 1 : 2 ) : ( ($second & 8) > 0 ? 0 : 2.5 ); 
   $info->version = $info_versions[ $info->version_id ]; 
   $info->layer_id = ($second & 4) > 0 ? ( ($second & 2) > 0 ? 1 : 2 ) : ( ($second & 2) > 0 ? 3 : 0 );     ; 
   $info->layer = $info_layers[ $info->layer_id ]; 
   $info->protection = ($second & 1) > 0 ? "no CRC" : "CRC"; 
   $info->bitrate = $info_bitrates[ $info->version_id ][ $info->layer_id ][ ($third & 240) ]; 
   $info->sampling_rate = $info_sampling_rates[ $info->version_id ][ ($third & 12)]; 
   $info->padding = ($third & 2) > 0 ? "on" : "off"; 
   $info->private = ($third & 1) > 0 ? "on" : "off"; 
   $info->channel_mode = $info_channel_modes[$fourth & 192]; 
   $info->copyright = ($fourth & 8) > 0 ? "on" : "off"; 
   $info->original = ($fourth & 4) > 0 ? "on" : "off"; 
   $info->size = filesize($filename); 
   $info->length = calculate_length($info->size,$info->bitrate, 0);
	fclose($filehandle); 
	return $info;
} 

?>