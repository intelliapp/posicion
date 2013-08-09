<?php
class PGSQLUtils{
	public static function unescape_hstore($hstore, $column) {
		$arr = json_decode('{' . str_replace('"=>"', '":"', $hstore) . '}', true);
		return ($arr && isset($arr[$column]))? $arr[$column] : null;
	}
}
class GeoUtils{
	public static function tranformPosition($str) {
		$move = '';
		switch ($str) {
			case 'N':
				$move = "de Sur a Norte";
				break;
			case 'O':
				$move = "de Este a Oeste";
				break;
			case 'E':
				$move = "de Oeste a Este";
				break;
			case 'S':
				$move = "de Norte a Sur";
				break;
			case 'NO':
				$move = "de Sur/Este a Norte/Oeste";
				break;
			case 'NE':
				$move = "de Sur/Oeste a  Norte/Este";
				break;
			case 'SE':
				$move = "de Norte/Oeste a Sur/Este";
				break;
			case 'SO':
				$move =  "de Norte/Este a Sur/Oeste";
				break;
			default:
				$move = $str;
				break;
		}
		return "Sentido $move";
	}

}
class ArraysUtils {
	public static function aasort ($array, $key) {
	    $sorter=array();
	    $ret=array();
	    reset($array);
	    foreach ($array as $ii => $va) {
	        $sorter[$ii]=$va[$key];
	    }
	    asort($sorter);
	    foreach ($sorter as $ii => $va) {
	        $ret[$ii]=$array[$ii];
	    }
	    return $ret;
	}
	public static function flatten($sorted_array) {
		$merged_array = array();
		array_walk_recursive($sorted_array, function($a) use (&$merged_array) { 
			$merged_array[] = $a; 
		});
		return $merged_array;
	}
}