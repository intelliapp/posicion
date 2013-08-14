<?php
/**
 * Generic exception class
 */
if (!class_exists('PosicionException')) {
  class PosicionException extends Exception {
    // pass
  }
}

class Base {

	public function __construct($obj) {
		$this->db = $obj;
	}

}

class Distance extends Base{

	public static $name = __CLASS__;

	public function execute($query) {
		$obj_query = $this->db->prepare($query);
		$obj_query->execute();
		$this->exec_querys++;
		$data = $obj_query->fetch(PDO::FETCH_ASSOC);
		return $data;
	}

}

class Country extends Base {
	
	public static $name = __CLASS__;

	public function execute($query) {
		$obj_query = $this->db->prepare($query);
		$obj_query->execute();
		$this->exec_querys++;
		$data = $obj_query->fetch(PDO::FETCH_ASSOC);
		return $data;
	}

}

class Province extends Base{

	public static $name = __CLASS__;

	public function execute($query) {
		$obj_query = $this->db->prepare($query);
		$obj_query->execute();
		$this->exec_querys++;
		$data = $obj_query->fetch(PDO::FETCH_ASSOC);
		if($data
			&& !empty($data['departamento'])) {
			unset($this->_errors);
			return strtolower($data['departamento']);
		}
		return NULL;
	}

}

class Vias extends Base{

	public static $name = __CLASS__;

	public function execute($query) {
		$oquery = $this->db->prepare($query);
		$oquery->execute();
		$data = $oquery->fetch(PDO::FETCH_ASSOC);
		if($data && isset($data['via'])){
			return $data;
		}
		return NULL;
	}

}


/**
 * posicionlogica facade
 **/
class PosicionLogica extends QueryModel{

	const LOG_TAG = __CLASS__;

	public static $version = '1.0';
	public static $name = __CLASS__;

	// public $range_area = 5;
	public $init_sql_error;
	public $exec_querys = 0;
	public $count_validzone = 1;
	public $wkt_point;
	public $gpsid;
	public $recordset;
	public $city_recordset;
	public $optional_recordsets;
	public $printDataset;
	public $resultsetLocations;
	public $utf8_enable;
	public $urban_zone;
	private $uuid;
	private $_clases;
	private $_results;
	private $_log;
	private $_isConsole;
	private $connected;
	private $_output;
	private $_errors;
	private $_bulkmode;
	private $_positions;
	private $_buffer;
	private $_isDebug;

	public function __construct($log, $args = array()) {
		$this->uuid = __()->uniqueId(self::$name . '_');
		try {
			$this->db = $this->createConnexion(
				$host = POS_DB_HOST, $dbname = POS_DB, $uname = POS_DB_UNAME, $pword = POS_DB_PWORD, $port = (!defined('POS_DB_PORT'))? '' : POS_DB_PORT
			);
		}catch(PDOException $e) {
			$this->_errors = array('error' => 'not connected', 'msg' => $e->getMessage());
		}
		if($this->db) {
			$this->connected = TRUE;
			$this->load_queries();
		}
		$this->_isConsole = (defined('STDIN'));
		$this->_log = $log;
		$this->_clases = Priority::getPriority();
		if($this->_isConsole && count($args) > 1) {
			$this->flat = $args[0];
			$this->flon = $args[1];
			$this->wkt_point = sprintf("ST_Point(%s,%s)", $this->flon, $this->flat);
		}
		if(!$args) {
			return;
		}
		if(isset($args['printDataset'])) {
			$this->printDataset = $this->_isDebug = TRUE;
		}
		if(isset($args['gpsid'])) {
			$this->gpsid = $args['gpsid'];
		}
		if(isset($args['country_name'])){
			$this->country = $args['country_name'];
		}

	}
	/**
	 * receives a block of coordinates from a csv file
	 */
	public function setFileHistory($file_handler = NULL) {
		$end =  ($this->_isConsole) ? "\n" : "<br>\n";
		$arr_coords = array();
		$this->_buffer = "";
		$lines_count = 0;
		$this->_bulkmode = TRUE;
		$this->printDataset = FALSE;
		if(!is_null($file_handler)) {
			while($line = fgetcsv($file_handler)) {
				$lines_count++;
				$this->_buffer  = join(",",$line);
				$this->_buffer .= ": ";
				$this->flat = floatval($line[0]);
				$this->flon = floatval($line[1]);
				$this->wkt_point = sprintf("ST_Point(%s,%s)", $this->flon, $this->flat);
				$updater = new Updater($this->_log, $this->gpsid, $this->flat, $this->flon);
				if($updater->execute()) {
					$this->setLastPosition($updater->oldpos);
				}
				// Authorized method from internally calls
				$this->_execute();
				$this->_log->LogDebug($this->_buffer);
				// sleep(4);
			}
			$this->_output .= "Cantidades de lineas ejecutadas: $lines_count $end";
		}
	}

	public function setLastPosition($positions = array()) {
		$this->_positions = $positions;
	}

	public function setWktPoint($flon, $flat){
		if($this->_bulkmode) {
			return NULL;
		}
		$this->wkt_point = sprintf("ST_Point(%s,%s)", $flon, $flat);
		$this->flon = floatval($flon);
		$this->flat = floatval($flat);
	}

	public function setWktPointString($point){
		$this->wkt_point = $point;
	}

	private function isUrbanZone(){
		if(!isset($this->flat) && !isset($this->flon)) {
			return FALSE;
		}
		$this->urban = new UrbanZone($this->db);
		$query = sprintf($this->perimetro_ciudad, $this->wkt_point);
		$this->urban->change_query($query);
		if($this->urban->execute()) {
			$this->exec_querys++;
			if(!$this->urban->data || empty($this->urban->data['ciudad'])) {
				$error = sprintf("No se encuentra ningun resultado para este punto (%s,%s)", $this->flat, $this->flon);
				$this->_errors = array('error' => 'not found', 'msg' => $error);
				// $this->urban_zone = FALSE;
			}
			$this->stdout(strtolower($this->urban->data['ciudad']), 2);
			$this->urban_zone = TRUE;
		}
		return $this->urban_zone;
	}

	private function getUrbanLocations($end) {
		$query = sprintf($this->second_query, $this->wkt_point, $this->wkt_point, $this->wkt_point);
		$this->db->exec("SET client_encoding TO 'utf-8'");
		$this->urban->change_query($query);
		if($this->urban->execute($array_result = TRUE)) {
			$this->exec_querys++;
			$this->urban->cleanDouble();
			if($this->urban->data 
				&& count($this->urban->data)>1) {
				if($this->printDataset) {
					$this->city_recordset = $this->urban->data;
				}
				$output = $this->urban->getReadableAdress();
				$this->stdout($output, 1);
			}			
		}
	}

	public function execute(){
		if(!$this->connected) {
			$this->_errors = array('error' => 'Connection Error:', 'msg' => 'Not Connected');
            throw new PosicionException(join(' ',$this->_errors), 1);
        }
        if(isset($this->init_sql_error)
			|| !isset($this->flat)
			|| !isset($this->flon)
			|| !isset($this->wkt_point)){
			return FALSE;
		}
		$this->_execute();
		if(isset($this->_errors) && array_key_exists('error', $this->_errors)) {
			echo "{$this->_errors['msg']} $end";
			$this->_output = ob_get_contents();
			$this->endStream();
			// return $this->_output;
		}
		return $this->_output;
	}

	private function _execute() {
		$this->_results = array();
		$range = 0.001;
		$end =  ($this->_isConsole) ? " " : "<br>\n";
		$this->openStream();
		if(!$this->_isConsole) {
		echo __CLASS__ . ": execute ...$end";
		echo __CLASS__ . ": latitude {$this->flat} $end";
		echo __CLASS__ . ": longitude {$this->flon} $end";
		echo __CLASS__ . ": {$this->wkt_point}$end";
		}

		// vias
		$vias = new Vias($this->db);
		$query = sprintf($this->qvias, $this->flon, $this->flon, $this->flat, $this->flat, $this->flon, $this->flat);
		$data = $vias->execute($query);
		if($data
			&& !empty($data['via'])){
			$result_vias = True;
			$output = rtrim(ltrim(strtolower($data['via'])));
			$this->stdout("via $output", 3);
			if(isset($data['KM']) && !empty($data['KM'])) {
				$km = strtolower($data['KM']);
				$this->stdout("- $km km ", 4);
			}
		}
		// new UrbanZone()::isUrbanZone()
		if($this->isUrbanZone()){
			if(!$this->_isConsole 
				&& $this->_isDebug) {
				echo "estamos en zona urbana$end";
			}
			if($this->connected) {
				$this->getUrbanLocations($end);
			}
		}
		if(!isset($result_vias) && !$this->urban_zone) {
			if(!$this->_isConsole 
				&& $this->_isDebug) {
				echo "estamos en carretera$end";
			}
			$this->road_zone = new RoadZone($this->db);
			$query = sprintf($this->count_query, $this->wkt_point, 'highway');
			if($this->road_zone->validOutsideZone($query, $this->count_validzone)) {
				$query = sprintf($this->place_query, $this->wkt_point, $range, 'highway', $this->wkt_point, $range, $this->wkt_point);
				$data = $this->road_zone->lookForMapObjects($query);
				$precision_enable = True;

			}else{
				if(!$this->_isConsole 
					&& $this->_isDebug) {
					echo "reduce el rango$end";
				}
				$range = 0.01;
				$query = sprintf($this->place_query, $this->wkt_point, $range, 'highway', $this->wkt_point, $range, $this->wkt_point);
				$data = $this->road_zone->lookForMapObjects($query);
			}
			if($data 
				&& isset($data['place_id'])) {
				$place_id = $data['place_id'];
				$query_names = sprintf($this->query_nombres, $place_id);
				$country = new Country($this->db);
				$data = $country->execute($query_names);
				if($data && $this->printDataset) {
					$this->resultsetLocations = $data;
				}
				if($data) {
					$this->stdout($data['isin'], 20);
				}
				$query_locat = sprintf($this->location_query, $this->wkt_point, $place_id, $this->wkt_point);
				$options = array("wkt_point" => $this->wkt_point);
				if(isset($precision_enable)){
					$options = array_merge($options, array('precision_enable' => $precision_enable));
				}
				$this->road_zone->init($options);
				$this->road_zone->setPlaceId($place_id);
				if($this->road_zone->execute($query_locat)) {
					$this->exec_querys++;
					if(!empty($this->road_zone->roadname)) {
						$this->stdout($this->road_zone->roadname, 5);
					}
					$this->road_zone->queryOptionalNames($this->qclasses, $range=2);
					$this->exec_querys = count($this->_clases) + $this->exec_querys;
					if(!empty($this->road_zone->optional_names)) {
						$this->stdout($this->road_zone->optional_names, 6);
					}
				}
				if(!empty($this->road_zone->administrative_names)) {
					$this->stdout($this->road_zone->administrative_names, 7);
				}
				// sending all resultsets to view for debug
				if($this->road_zone->data && $this->printDataset) {
					$this->recordset = $this->road_zone->data;
				}
				if($this->road_zone->recordset && $this->printDataset) {
					$this->optional_recordsets = $this->road_zone->recordset;
				}
			}

		}
		if(!$this->urban_zone) {
			$distance = new Distance($this->db);
			$query = sprintf($this->distance_city_query, $this->wkt_point, $this->wkt_point, $this->wkt_point);
			$data = $distance->execute($query);
			if($data) {
				$city = strtolower($data['ciudad']);
				$distance = $data['distancia'];
				if(mb_strpos($distance, ".")) {
					$km = mb_substr($distance, 0, mb_strpos($distance, ".") + 2);	
				}else{
					$km = (string)((float)($distance));
				}
				$this->stdout("$km km de $city", 8);
			}
			// Departamento
			$province = new Province($this->db);
			$query = sprintf($this->states_query, $this->wkt_point);
			$departamento = $province->execute($query);
			if($departamento){
				$this->stdout("departamento de $departamento", 9);
			}
		}
		// adding country name at the end
		if(!mb_strpos(strtolower(join($this->_results, " ")), strtolower($this->country))) {
			$this->stdout($this->country, 20);
		}
		if(isset($this->_positions)
			&& $this->_positions['move'] <> "--") {
			$pos = GeoUtils::tranformPosition($this->_positions['move']);
			$this->stdout($pos, 11);
		}
		if(!$this->_isConsole
			&& $this->_isDebug) {
			echo __CLASS__ . ": {$this->exec_querys} querys ejecutados$end";
		}
		$this->endStream();
	}

	private function stdout($msg, $index=NULL){
		if(is_null($index)) {
			$this->_results[] = " $msg";
		}else{
			$this->_results[$index] = " $msg";
		}
		
		$end =  ($this->_isConsole) ? " " : "<br>\n";
		if(!$this->_isConsole) {
			echo "$msg $end";
		}
	}
	public function endStream() {
		if(!$this->_isConsole
			&& !$this->_bulkmode) {
			$this->_output = ob_get_contents();
			ob_end_clean();
			ob_end_flush();
		}
		uksort($this->_results, 'cmp_function');
		if($this->_bulkmode) {
			$this->_buffer .= join(",", $this->_results);
		}
		if($this->_isConsole) {
			$output = join(",", $this->_results);
			if($this->utf8_enable) {
				echo utf8_encode(ltrim($output));
			}else {
				echo ltrim($output);
			}
			
		}
		if($this->_isConsole) {
			echo "\n";
		}		
	}
	public function openStream() {
		if(!$this->_isConsole
			&& !$this->_bulkmode) {
			ob_start();
		}
	}
	public function n() {
		return __CLASS__;
	}
}