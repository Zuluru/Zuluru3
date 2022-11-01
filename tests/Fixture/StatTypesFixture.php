<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use StatTypesSeed;

class StatTypesFixture extends TestFixture {

	use SeedFixtureTrait;

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'stat_types'];

	/**
	 * Seed name to use
	 */
	public $seed = StatTypesSeed::class;

	public function __construct() {
		parent::__construct();

		if (!defined('STAT_TYPE_ID_ULTIMATE_GOALS')) {
			define('STAT_TYPE_ID_ULTIMATE_GOALS', 9);
		}
	}

}
