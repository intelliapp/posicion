<?php
/**
 * Generic exception class
 */
if (!class_exists('PosicionSQLException')) {
  class PosicionSQLException extends Exception {
    // pass
  }
}
class QueryModel extends Connexion {

    private $_queries = array(
        "qclasses",
        "qvias",
        "first_query",
        "second_query",
        "count_query",
        "place_query",
        "location_query",
        "query_nombres",
        "states_query",
        "distance_city_query",
        "perimetro_ciudad",
    );

    public $dir = "sql/";


	public function load_queries() {
        foreach ($this->_queries as $value) {
            if(file_exists( dirname( dirname(__FILE__) ) . "/" . $this->dir . $value . ".sql")) {
                $this->$value =  file_get_contents(dirname( dirname(__FILE__) ) . "/" . $this->dir . $value . ".sql" );
            }
            if(empty($this->$value)){
                $this->init_sql_error = TRUE;
                throw new PosicionSQLException();
            }
        }
	}
}    