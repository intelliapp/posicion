<?php
define('fieldname', 'name');
class RoadZone {
	public static $name = __CLASS__;

	public $country;
	public $data;
	public $way_query;
	public $roadname;
	public $optional_names;
	public $administrative_names;
	public $types = array(
		'secondary',
		'administrative',
		'yes',
		'trunk',
		'fuel',
		'grave_yard',
		'convenience',
		'water',
		'riverbank',
		'river',
		'industrial',
		'residential',
		'tertiary',
		'footway',
		'unclassified',
		'police',
		'neighbourhood',
		'hamlet',
		'place_of_worship',
		'village',
	);
	public $wkt_point;
	public $place_id;
	private $_clases;
	private $uuid;
	public $recordset;
	public $precision_enable;

	public function __construct($db) {
		$this->uuid = __()->uniqueId(self::$name . '_');
		$this->db = $db;
		$this->_clases = Priority::getPriority();
	}

	public function init($config = array()) {
		$this->precision_enable = (isset($config['precision_enable']) && $config['precision_enable']);
		if(isset($config['wkt_point'])) {
			$this->wkt_point = $config['wkt_point'];
		}
		if(isset($config['country_name'])) {
			$this->country = $config['country_name'];
		}
	}

	public function validOutsideZone($query, $count_validzone) {
		$query_obj=$this->db->prepare($query);
		$this->exec_querys++;
		$query_obj->execute();
		$data = $query_obj->fetch(PDO::FETCH_ASSOC);
		return ($data && isset($data['count']) 
				&& $data['count'] == $count_validzone);
	}

	public function lookForMapObjects($query) {
		$obj_query = $this->db->prepare($query);
		$obj_query->execute();
		$this->exec_querys++;
		$data = $obj_query->fetch(PDO::FETCH_ASSOC);
		return $data;
	}

	public function setPlaceId($placeid) {
		$this->place_id = $placeid;
	}

	public function getAllNames() {
		$result = join(",",array_unique(array_map(create_function('$w', 'return $w[fieldname];'), $this->data)));
		return $result;
	}

	public function getRoadName($data) {
		$result = '';
		foreach ($data as $row) {
			if(mb_strpos($row[fieldname], "vía") !== false) {
				$value = PGSQLUtils::unescape_hstore($row[fieldname], fieldname);
				$result .= $value . ',';
			}
		}
		if(!empty($result)) {
			$arr = explode(',', $result);
			$this->roadname = $arr[0];
		}
	}

	public function queryOptionalNames($qclasses) {
		$this->recordset = array();
		foreach ($this->_clases as $value) {
			$query = sprintf($qclasses, $this->wkt_point, $value, $this->place_id, $this->wkt_point);
			$obj_query = $this->db->prepare($query);
			$obj_query->execute();
			$data = $obj_query->fetchAll(PDO::FETCH_ASSOC);
			if($data){
				$values = __($data)->chain()->pluck('name')->value();
				foreach ($values as $row) {
					if(mb_strpos($row, "name")) {
						$this->optional_names = PGSQLUtils::unescape_hstore($row, fieldname);
					}
				}
			}
			$this->recordset[] = $data;
			
		}
	}

	public function getAdministrativeNames() {
		$result = '';
		foreach ($this->data as $row) {
			if($row['type'] == 'administrative') {
				$result .= PGSQLUtils::unescape_hstore($row['name'], 'name');
				$result .= ',';
			}
		}
		$arr = explode(',', $result);
		$clone = array_pop($arr);
		if(!$this->precision_enable) {
			$arr = array_slice($arr, 0, 2);
		}
		$arr = array_reverse($arr, TRUE);
		// if(!in_array($this->country, $arr)) {
		// 	$arr[] = $this->country;
		// }
		$this->administrative_names = join(", ", $arr);
	}

	public function execute($query) {
		$this->way_query = $this->db->prepare($query);
		if($this->way_query->execute()) {
			$this->data = $this->way_query->fetchAll(PDO::FETCH_ASSOC);
			$this->getRoadName($this->data);
			if(empty($this->roadname)) {
				$this->getAdministrativeNames();
			}
			// if(!mb_strpos(strtolower($this->roadname), strtolower($this->country))) {
			// }
			return TRUE;	
		}
		return FALSE;
	}

}