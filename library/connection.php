<?php

namespace Bundles\MongoDB;
use Exception;
use Mongo;
use e;

/**
 * MongoDB Connection Class
 */
class Connection {

	/**
	 * Store the MongoDB Connection
	 */
	private $connection;

	/**
	 * Instantiate and connect to the mongo server
	 * @author Kelly Becker
	 */
	public function __construct($dsn = false) {
		if(!$dsn) throw new Exception("No DSN provided to connect to Mongo");

		/**
		 * Make sure the Mongo Class exists
		 */
		if(!class_exists('Mongo'))
			throw new Exception("The MongoDB PECL extension has not been installed or enabled");

		$dbname = array_pop(explode('/', $dsn));
		$this->connection = new Mongo($dsn, array("persist" => "x"));
		$this->connection = $this->connection->$dbname;
	}

	/**
	 * Insert a row into a collection
	 * @return Bool | Array
	 * @author Kelly Becker
	 */
	public function insert($collection, $array, $opts = array()) {
		return $this->connection->$collection->insert($array, $opts);
	}

	/**
	 * Find row(s) from a collection
	 * @return MongoCursor
	 * @author Kelly Becker
	 */
	public function find($collection, $array, $fields = array()) {
		return $this->connection->$collection->find($array, $fields);
	}

	/**
	 * Update a row in a collection
	 * @return Bool | Array
	 * @author Kelly Becker
	 */
	public function update($collection, $conditions, $array, $opts = array()) {
		
		/**
		 * Emulate SQL Update if need be
		 */
		if(isset($opts['$set']) && $opts['$set'] === true) {
			$array = array('$set' => $array);
			unset($opts['$set']);
		}

		return $this->connection->$collection->update($conditions, $array, $opts);
	}

	/**
	 * Removes a row in a collection
	 * @return Bool | Array
	 * @author Kelly Becker
	 */
	public function remove($collection, $conditions, $opts = array()) {
		return $this->connection->$collection->remove($conditions, $opts);
	}

	/**
	 * Load a Model object for Mongo
	 */
	public function model($collection, $conditions = false) {
		return new Model($collection, $conditions, $this);
	}

	/**
	 * Run a manual function on the MongoDB Instance
	 * @author Kelly Becker
	 */
	public function __call($func, $args) {
		return call_user_func_array(array($this->connection, $func), $args);
	}

}