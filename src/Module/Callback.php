<?php
/**
 * Base class for user maintenance callback functionality.
 */
namespace App\Module;

use Cake\Event\EventListenerInterface;

abstract class Callback implements EventListenerInterface {

	public function __construct($parent, $config) {
		$this->config = $config;
	}

}
