<?php

namespace Bundles\MongoDB;
use Exception;
use e;

class Bundle {

	/**
	 * Cache the instances
	 * @author Kelly Becker
	 */
	private static $instances = array();

	/**
	 * Get a new DB Instance
	 * @author Kelly Becker
	 */
	public function __callBundle($slug = 'default') {
		if(isset(self::$instances[$slug]))
			return self::$instances[$slug];

		$dsn = e::$environment->requireVar(
			"mongodb.$slug.connection",
			'mongodb://username[:password]@hostname[:port]/database'
		);

		return self::$instances[$slug] = new Connection($dsn);
	}

	public function __get($var) {
		return $this->__callBundle($var);
	}

	/**
	 * Load the Environment From MongoDB
	 */
	public function _on_environmentLoad($env) {

		/**
		 * Instantiate MongoDB
		 */
		if(isset($env['mongodb.default.connection']))
			self::$instances[$slug] = new Connection($env['mongodb.default.connection']);

		/**
		 * Load the model and return the environment
		 */
		$env = $this->default->model('$_environment', array('env' => 1));
		return $env->__toArray();
	}

	/**
	 * Save the Environment From MongoDB
	 */
	public function _on_environmentSave($array = array()) {

		/**
		 * Load the model and return the environment
		 */
		$env = $this->default->model('$_environment', array('env' => 1));
		$env->save($array, true);
		return $env->__toArray();
	}

}