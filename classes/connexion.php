<?php
class Connexion{
	public $db = NULL;
	private $_connexionstring;

	public function __construct() {
	}
	public function createConnexion($host = '', $dbname = '', $uname = '', $pword = '', $port = ''){
		if(!empty($port)) {
			$this->_connexionstring = sprintf(
			"pgsql:host=%s;dbname=%s;port=%s", $host,  $dbname, $port);
		}else{
			$this->_connexionstring = sprintf(
			"pgsql:host=%s;dbname=%s", $host,  $dbname);
		}
		$this->db = new PDO($this->_connexionstring, $uname, $pword);
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		return $this->db;
	}
}