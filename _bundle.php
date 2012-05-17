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
		 * Heroku Mongo Defaults
		 */
		$herokuProviders = array(
			getenv('MONGO_URI'),
			getenv('MONGOHQ_URI'),
			getenv('MONGOLAB_URI')
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
		unset($array['@env'], $array['_id']);
		return $array;
	}

	/**
	 * Save the Environment From MongoDB
	 */
	public function _on_environmentSave($array = array()) {

		if(!isset(self::$instances['default']))
			return false;

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