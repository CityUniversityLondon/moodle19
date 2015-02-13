<?php
session_start();

if (!isset($_SESSION['portfolios'])) {
	$simplexml = simplexml_load_file('http://www.pebblepad.co.uk/portfoliodirectory/directory.asmx/GetList');

	function simplexml_to_array($simplexml) {
		$array = array();
		foreach ($simplexml->Portfolio as $portfolio) {
			$array[] = array('name' => (string)$portfolio->InstitutionName, 'url' => (string)$portfolio->url);
		}
		$array[] = array('name' => 'PebblePad Leap2a', 'url' => 'http://pebblepad.co.uk/leap2a/');
		return $array;
	}
	
	$xml = simplexml_to_array($simplexml);
	$simplexml = '';
	$_SESSION['portfolios'] = $xml;
}

if ($_GET) {

	$text = $_GET['str'];
	$xml = $_SESSION['portfolios'];
	
	$output = '';
	
	function insert_string($string, $new_before, $new_after, $pos) {
		return str_replace($pos, $new_before.$pos.$new_after, $string);
	}

	foreach ($xml as $inst) {
		if (substr_count(strtolower($inst['name']), strtolower($text)) > 0
		|| substr_count(strtolower($inst['url']), strtolower($text)) > 0) {
			$name = $inst['name'];
			$url = $inst['url'];
			$output .= '
			<tr>
				<td>'.$name.' | '.$url.'</td>
			</tr>';
		}
	}
	
	if ($output != '') {
		echo $output;
	}
}
	
?>
