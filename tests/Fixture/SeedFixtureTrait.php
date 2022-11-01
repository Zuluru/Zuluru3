<?php
namespace App\Test\Fixture;

use Migrations\AbstractSeed;

trait SeedFixtureTrait {

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		require_once(CONFIG . DS . 'Seeds' . DS . $this->seed . '.php');
		/** @var AbstractSeed $seed */
		$seed = new $this->seed;
		$this->records = $seed->data();

		parent::init();
	}

}
