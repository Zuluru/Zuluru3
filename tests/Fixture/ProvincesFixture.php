<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use ProvincesSeed;

class ProvincesFixture extends TestFixture {

	use SeedFixtureTrait;

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'provinces'];

	/**
	 * Seed name to use
	 */
	public $seed = ProvincesSeed::class;

}
