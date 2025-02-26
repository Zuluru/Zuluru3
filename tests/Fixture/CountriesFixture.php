<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use CountriesSeed;

class CountriesFixture extends TestFixture {

	use SeedFixtureTrait;

	/**
	 * Seed name to use
	 */
	public $seed = CountriesSeed::class;

}
