<?php
class Priority {
	static $priority_level = array(
	array('highway', 1),
	array('amenity', 2),
	);
	static public function getPriority() {
		$sorted_array = ArraysUtils::aasort(self::$priority_level, 1);
		$merged_array = array();
		array_walk_recursive($sorted_array, function($a) use (&$merged_array) { 
			if(!is_int($a)) {
				$merged_array[] = $a; 
			}
		});
		return $merged_array;
	}
}