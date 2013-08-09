<?php
define('name', 'name');
class UrbanZone {

	public static $name = __CLASS__;

	public $streetname;
	public $data;
	public $neighbourname;
	public $intersections;

	private $uuid;
	private $_objquery;
	private $_array_names;

	public function __construct($db) {
        $this->uuid = __()->uniqueId(self::$name . '_');
		$this->db = $db;
	}

	public function change_query($query) {
		$this->_objquery = $this->db->prepare($query);
	}

	public function cleanDouble() {
		$this->_array_names = array();
		$this->intersections = array();
		$cloned_array = array();
		foreach ($this->data as $row) {
			$name = PGSQLUtils::unescape_hstore($row['nombrevia'], 'name');
			if(!isset($this->streetname)) {
				$this->streetname = $name;
			}
			if(!in_array($name, $this->_array_names)) {
				if(!empty($this->streetname) 
					&& $name != $this->streetname 
					&& count($this->intersections)<2) {
					$this->intersections[] = $name;
				}
				$this->_array_names[] = $name;
				$cloned_array[] = $row;
			}
		}
		$this->data = $cloned_array;
	}

	public function getReadableAdress() {
		$output = "";
		if(!$this->_array_names){
			return $output;
		}
		if(!empty($this->streetname)
			&& $this->_array_names[0] == $this->streetname){
			$this->_array_names = array_slice($this->_array_names, 1);
			$output = $this->streetname;
		}
		$output .= " entre " . join(" y ", array_slice($this->_array_names, 0, 2));
		$output .= ", ";
		$this->neighbourname = PGSQLUtils::unescape_hstore($this->data[0]['nombreparent'], 'name');
		$output .= $this->neighbourname;
		$output .= ", " . $this->cleanNames($this->data[0]['isin']);
		return $output;
	}

	public function cleanNames($name) {
		$arr = explode(',', $name);
		if(count($arr) == 3) {
			$arr = array_slice($arr, 0, 2);
		}
		return join(", ", $arr);
	}

	public function fetchAll () {
		$this->data = $this->_objquery->fetchAll(PDO::FETCH_ASSOC);
	}

	public function fetchData () {
		$this->data = $this->_objquery->fetch(PDO::FETCH_ASSOC);
	}

	public function execute($bool = FALSE) {
		$this->data = null;
		if($this->_objquery->execute()) {
			if($bool) {
				$this->fetchAll();
			}else{
				$this->fetchData();
			}
		}
		return $this->data;
	}

}