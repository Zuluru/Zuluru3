<?php


namespace App\Test\Fixture;


use Cake\TestSuite\Fixture\TestFixture;

class CountriesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'countries'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Canada',
			],
			[
				'name' => 'United States',
			],
		];

		parent::init();
	}

}
