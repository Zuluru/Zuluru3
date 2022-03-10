<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use GroupsSeed;

class GroupsFixture extends TestFixture {

	use SeedFixtureTrait;

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'groups'];

	/**
	 * Seed name to use
	 */
	public $seed = GroupsSeed::class;

}
