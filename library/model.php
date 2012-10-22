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
	private $_opts = array();

	private $_data = array();

	public function __construct($collection, $condition = false, $connection, $opts = array()) {
		$this->_collection = $collection;
		$this->_connection = $connection;
		$this->_opts = $opts;

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
		 * If serialized object unserialize
		 */
		if(is_array($result) && !empty($opts['serialize'])) $result['data'] = unserialize($result['data']);

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
		if(!empty($array) && is_array($array)) foreach($array as $var => $val) {
			$this->$var = $val;
		}

		/**
		 * Set object if one was passed
		 */
		elseif(is_object($array) && !empty($this->_opts['serialize']))
			$this->_data['data'] = serialize($array);
		elseif(is_object($array))
			$this->_data['data'] = $array;

		/**
		 * If data object was passed and was are asked to serialize
		 */
		if(is_object($this->_data['data']) && !empty($this->_opts['serialize']))
			$this->_data['data'] = serialize($this->_data['data']);

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
		 * If new and condition was specified add it
		 */
		if($this->_new && !empty($this->_condition)) foreach($this->_condition as $cond => $val) {
			$this->_data[$cond] = $val;
		}

		/**
		 * If safe insert safe
		 */
		if($safe) $opts['safe'] = true;

		/**
		 * Upsert the record
		 */
		if(empty($this->_data['_id'])) $result = $this->_connection->insert($this->_collection, $this->_data, $opts);
		else $result = $this->_connection->update($this->_collection, $this->_condition, $this->_data, $opts);

		/**
		 * If any data was passed back append it to the string and set the id as a condition
		 */
		if(isset($result['_data'])) {
			$this->_data = $result['_data'];
			$this->_condition = array('_id' => $this->_data['_id']);
		}

		$this->_new = false;
		return $result;
	}

	public function __empty() {
		if($this->_new) return true;
		if(empty($this->_data)) return true;
		return false;
	}

	/**
	 * Get a var and climb the data tree
	 */
	public function __get($var) {
		if($var === '_id' && isset($this->_data['_id']))
			return (string) $this->_data['_id'];

		if(array_key_exists($var, $this->_data))
			return $this->_data[$var];
	}

	/**
	 * Set a variable to the data array
	 */
	public function __set($var, $val) {

		if(array_key_exists($var, $this->_data)) {
			if(is_array($this->_data[$var]) && is_array($val))
				return $this->_data[$var] = e\array_merge_recursive_simple($this->_data[$var], $val);
		}

		$this->_data[$var] = $val;
		return true;
	}

	/**
	 * Run isset on the array
	 */
	public function __isset($var) {
		if(isset($this->_data[$var]))
			return true;
		else return false;
	}

	/**
	 * Return the data array
	 */
	public function __toArray() {
		return $this->_data;
	}

}