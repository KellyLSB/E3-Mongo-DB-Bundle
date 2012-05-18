<?php

namespace Bundles\MongoDB;
use Bundles\Manage\Tile;
use Exception;
use e;

/**
 * Members PHP Manage
 * @author Nate Ferrero
 */
class Manage {
	
	public $title = 'MongoDB';
	
	public function page($path) {
		return 'Coming Soon.';
	}
	
	public function tile() {
	    $tile = new Tile('mongodb');
    	$tile->body .= '<h2>Manage the MongoDB Instance.</h2>';
    	return $tile;
    }
}