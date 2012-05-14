<?php

namespace Bundles\MongoDB;
use Exeception;
use e;

/**
 * MongoDB Model
 * @author Kelly Becker
 */
class Model {

	private $_new = true;

	private $_collection;
	private $_connection;
	private $_condition;

	private $_data = array();

	public function __construct($collection, $condition = false, $connection) {
		$this->_collection = $collection;
		$this->_connection = $connection;

		/**
		 * If the condition does not exist or is not an array
		 */
		if(empty($condition) || !is_array($condition)) return;

		/**
		 * Store the conditions in the object
		 */
		$this->_condition = $condition;

		/**
		 * Fetch the results
		 */
		$result = $this->_connection->find($collection, $condition)->limit(1)->getNext();

		/**
		 * If the result is not an array then return
		 */
		if(!is_array($result)) return;

		/**
		 * Set the data to the object
		 */
		$this->_data = $result;

		/**
		 * Since we have data this is not a new object
		 */
		$this->_new = false;
	}

	public function save($array = false, $safe = false, $opts = array()) {

		/**
		 * Merge in the data
		 */
		if(!empty($array) && is_array($array))
			$this->_data = e\array_merge_recursive_simple($this->_data, $array);

		/**
		 * If there is no data to save stop trying
		 */
		if(empty($this->_data)) return false;

		/**
		 * Set timestamps
		 */
		if($this->_new) $this->_data['created_timestamp'] = date('Y-m-d H:i:s');
		$this->_data['updated_timestamp'] = date('Y-m-d H:i:s');

		/**
		 * If safe insert safe
		 */
		if($safe) $opts['safe'] = true;

		/**
		 * Upsert the record
		 */
		$opts['upsert'] = true;
		$result = $this->_connection->update($this->_collection, $this->_condition, $this->_data, $opts);
		$this->_new = false;
		return $result;
	}

	/**
	 * Get a var and climb the data tree
	 */
	public function __get($var) {
		if($var === '_id' && isset($this->_data['_id']))
			return (string) $this->_data['_id'];

		if(isset($this->_data[$var]))
			return $this->_data[$var];
	}

	/**
	 * Set a variable to the data array
	 */
	public function __set($var, $val) {

		if(is_array($val)) {
			$val = array($var => $val);
			$this->_data = e\array_merge_recursive_simple($this->_data, $val);
			return true;
		}

		$this->_data[$var] = $val;
		return true;
	}

	/**
	 * Return the data array
	 */
	public function __toArray() {
		return $this->_data;
	}

}