<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use DaysSeed;

class DaysFixture extends TestFixture {

	use SeedFixtureTrait;

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'days'];

	/**
	 * Seed name to use
	 */
	public $seed = DaysSeed::class;

}
