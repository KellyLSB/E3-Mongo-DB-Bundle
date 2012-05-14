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

}