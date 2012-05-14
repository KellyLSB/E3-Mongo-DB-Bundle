<?php

namespace Bundles\MongoDB;
use Exeception;
use e;

/**
 * MongoDB Model
 * @author Kelly Becker
 */
class Model {

	private $_collection;
	private $_connection;
	private $_condition;

	private $_treeStack = array();
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
		 * If safe insert safe
		 */
		if($safe) $opts['safe'] = true;
		return $this->_connection->insert($this->_collection, $array, $opts);
	}

	/**
	 * Get a var and climb the data tree
	 */
	public function __get($var) {
		if(!isset($this->_data[$var]));
			return null;

		if(is_array($this->_data[$var])) {
			$array = $this->_treeStack;
			array_push($array, $var);

			return $this->__getTree($array);
		}

		else return $this->_data[$var];
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
	 * Climb the data tree
	 */
	private function __getTree($array = array()) {

		$data = $this->_data;
		foreach($array as $r) {
			if(!isset($data[$r])) {
				$this->_treeStack = array();
				return null;
			}

			$data = $data[$r];
		}

		if(!is_array($data)) {
			$this->_treeStack = array();
			return $data;
		}

		$this->_treeStack = $array;

		return $this;
	}

}