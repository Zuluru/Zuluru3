<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WaiversFixture
 *
 */
class WaiversFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'waivers'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Lorem ipsum dolor sit amet',
				'description' => 'Lorem ipsum dolor sit amet',
				'text' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'active' => 1,
				'expiry_type' => 'fixed_dates',
				'start_month' => 1,
				'start_day' => 1,
				'end_month' => 12,
				'end_day' => 31,
				'duration' => null,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Lorem ipsum dolor sit amet',
				'description' => 'Lorem ipsum dolor sit amet',
				'text' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'active' => 1,
				'expiry_type' => 'fixed_dates',
				'start_month' => 4,
				'start_day' => 1,
				'end_month' => 3,
				'end_day' => 31,
				'duration' => null,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Lorem ipsum dolor sit amet',
				'description' => 'Lorem ipsum dolor sit amet',
				'text' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'active' => 1,
				'expiry_type' => 'event',
				'start_month' => null,
				'start_day' => null,
				'end_month' => null,
				'end_day' => null,
				'duration' => null,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Lorem ipsum dolor sit amet',
				'description' => 'Lorem ipsum dolor sit amet',
				'text' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'active' => 1,
				'expiry_type' => 'elapsed_time',
				'start_month' => null,
				'start_day' => null,
				'end_month' => null,
				'end_day' => null,
				'duration' => 5,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Lorem ipsum dolor sit amet',
				'description' => 'Lorem ipsum dolor sit amet',
				'text' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'active' => 1,
				'expiry_type' => 'never',
				'start_month' => null,
				'start_day' => null,
				'end_month' => null,
				'end_day' => null,
				'duration' => null,
				'affiliate_id' => AFFILIATE_ID_SUB,
			]
		];

		if (!defined('WAIVER_ID_ANNUAL')) {
			$i = 0;
			define('WAIVER_ID_ANNUAL', ++$i);
			define('WAIVER_ID_ANNUAL2', ++$i);
			define('WAIVER_ID_EVENT', ++$i);
			define('WAIVER_ID_ELAPSED', ++$i);
			define('WAIVER_ID_PERPETUAL', ++$i);
		}

		parent::init();
	}

}
