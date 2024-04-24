<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use GroupsSeed;

class GroupsFixture extends TestFixture {

	use SeedFixtureTrait;

	/**
	 * Seed name to use
	 */
	public $seed = GroupsSeed::class;

}
