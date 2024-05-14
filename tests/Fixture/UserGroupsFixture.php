<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use UserGroupsSeed;

class UserGroupsFixture extends TestFixture {

	use SeedFixtureTrait;

	/**
	 * Seed name to use
	 */
	public $seed = UserGroupsSeed::class;

	public $table = 'user_groups';
}
