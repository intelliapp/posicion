<?php
if (!class_exists('UpdaterException')) {
  class UpdaterException extends Exception {
    // pass
  }
}
class Updater extends Connexion{
	
	public static $name = __CLASS__;

	public $oldpos;
	private $_log;
	private $_connected;
	private $_query;
	private $uuid;

	public $query_insert = "SELECT \"updategps\" ('%s', '%s', '%s')";

	public function __construct($log, $gpsid, $flat, $flon) {
		$this->uuid = __()->uniqueId(self::$name . '_');
		try{
			$this->db = $this->createConnexion(
				$host = POS_DB_HOST, $dbname = POS_DB_EVENTS, $uname = POS_DB_UNAME, $pword = POS_DB_PWORD, $port = (!defined('POS_DB_PORT'))? '' : POS_DB_PORT
			);
		}catch(PDOException $e) {
			$this->_errors = array('error' => 'not connected', 'msg' => $e->getMessage());
		}
		if($this->db) {
			$this->_connected = TRUE;
		}
		if(empty($gpsid)) {
			throw new UpdaterException("no tiene id del gps", 1);
		}
		$this->setQuery($gpsid, $flat, $flon);
		$this->_isConsole = (defined('STDIN'));
		$this->_log = $log;
	}
	public function getQuery($gpsid, $flat, $flon) {
		return sprintf($this->query_insert, $gpsid, $flat, $flon);
	}
	public function setQuery($gpsid, $flat, $flon) {
		$this->_query = sprintf($this->query_insert, $gpsid, $flat, $flon);
	}
	public function execute(){
		$end =  ($this->_isConsole) ? "\n" : "<br>";
		if($this->_connected && !empty($this->_query)) {
			$query_obj = $this->db->prepare($this->_query);
		 	// echo __CLASS__ . ": execute ...$end";
			if($query_obj->execute()) {
				$data = $query_obj->fetch(PDO::FETCH_ASSOC);
				if($data && isset($data['updategps'])){
					$arr = explode(",", $data['updategps']);
					$this->oldpos = array('lat' => $arr[0], 'long' => $arr[1], 'move' => $arr[2]);
					return TRUE;
				}
			}
		}
		return FALSE;
	}
}