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

	public function _on_framework_loaded() {
		e::configure('manage')->activeAddKey('bundle', __NAMESPACE__, 'mongodb');
	}

	/**
	 * Get a new DB Instance
	 * @author Kelly Becker
	 */
	public function __callBundle($slug = 'default') {
		if(isset(self::$instances[$slug]))
			return self::$instances[$slug];

		$dsn = $this->isAvailable($slug);

		return self::$instances[$slug] = new Connection($dsn);
	}

	public function __get($var) {
		return $this->__callBundle($var);
	}

	public function isAvailable($slug = 'default') {
		$dsn = e::$environment->requireVar(
			"mongodb.$slug.connection",
			'mongodb://username[:password]@hostname[:port]/database'
		);

		if(empty($dsn)) return false;
		else return $dsn;
	}

	/**
	 * Load the Environment From MongoDB
	 */
	public function _on_environmentLoad($env) {

		/**
		 * Trace environment load
		 */
		e\trace("Loading mongo environment config.");

		/**
		 * Heroku Mongo Providers
		 */
		$herokuProviders = array(
			$_SERVER['MONGO_URI'],
			$_SERVER['MONGOHQ_URI'],
			$_SERVER['MONGOLAB_URI']
		);

		foreach($herokuProviders as $provider) if(!empty($provider))
			$herokuProvider = $provider;

		/**
		 * Instantiate MongoDB
		 */
		if(isset($herokuProvider))
			self::$instances['default'] = new Connection($herokuProvider);
		else if(isset($env['mongodb.default.connection']))
			self::$instances['default'] = new Connection($env['mongodb.default.connection']);
		else return false;

		/**
		 * Load the model and return the environment
		 */
		$env = $this->default->model('_environment', array('@env' => 1));
		$array = $env->__toArray();

		/**
		 * Clean out DB Vars
		 */
		unset($array['@env'], $array['_id'],
			$array['created_timestamp'],
			$array['updated_timetstamp']
		);

		/**
		 * Switch | to .
		 */
		foreach($array as $key => $val) {
			if(strpos($key, '|') === false)
				continue;

			$array[str_replace('|', '.', $key)] = $val;
			unset($array[$key]);
		}

		return $array;
	}

	/**
	 * Save the Environment From MongoDB
	 */
	public function _on_environmentSave($array = array()) {

		if(!isset(self::$instances['default']))
			return false;

		/**
		 * Trace environment save
		 */
		e\trace("Saving mongo environment config.");

		/**
		 * Switch . to |
		 */
		foreach($array as $key => $val) {
			if(strpos($key, '.') === false)
				continue;

			$array[str_replace('.', '|', $key)] = $val;
			unset($array[$key]);
		}

		/**
		 * Load the model and return the environment
		 */
		$array['@env'] = 1;
		$env = $this->default->model('_environment', array('@env' => 1));
		$env->save($array, true);
		$array = $env->__toArray();
		unset($array['@env'], $array['_id']);
		return $array;
	}

}