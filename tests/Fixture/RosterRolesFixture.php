<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use RosterRolesSeed;

class RosterRolesFixture extends TestFixture {

	use SeedFixtureTrait;

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'roster_roles'];

	/**
	 * Seed name to use
	 */
	public $seed = RosterRolesSeed::class;

}
