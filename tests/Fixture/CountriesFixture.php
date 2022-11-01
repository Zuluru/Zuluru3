<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use CountriesSeed;

class CountriesFixture extends TestFixture {

	use SeedFixtureTrait;

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'countries'];

	/**
	 * Seed name to use
	 */
	public $seed = CountriesSeed::class;

}
