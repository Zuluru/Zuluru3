<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FieldsFixture
 *
 */
class FieldsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'fields'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init(): void {
		$this->records = [
			[
				'num' => 'Field Hockey 1',
				'is_open' => true,
				'indoor' => false,
				'surface' => 'grass',
				'rating' => 'A',
				'facility_id' => FACILITY_ID_SUNNYBROOK,
				'latitude' => 43.724033,
				'longitude' => -79.356307,
				'angle' => 83,
				'length' => 110,
				'width' => 40,
				'zoom' => 16,
				'layout_url' => null,
				'sport' => 'ultimate',
			],
			[
				'num' => 'Field Hockey 2',
				'is_open' => true,
				'indoor' => false,
				'surface' => 'grass',
				'rating' => 'A',
				'facility_id' => FACILITY_ID_SUNNYBROOK,
				'latitude' => 43.723691,
				'longitude' => -79.356243,
				'angle' => 83,
				'length' => 110,
				'width' => 40,
				'zoom' => 16,
				'layout_url' => null,
				'sport' => 'ultimate',
			],
			[
				'num' => 'Field Hockey 3',
				'is_open' => true,
				'indoor' => false,
				'surface' => 'grass',
				'rating' => 'A',
				'facility_id' => FACILITY_ID_SUNNYBROOK,
				'latitude' => 43.723339,
				'longitude' => -79.356171,
				'angle' => 83,
				'length' => 110,
				'width' => 40,
				'zoom' => 16,
				'layout_url' => null,
				'sport' => 'ultimate',
			],
			[
				'num' => 'Greenspace',
				'is_open' => false,
				'indoor' => false,
				'surface' => 'grass',
				'rating' => 'A',
				'facility_id' => FACILITY_ID_SUNNYBROOK,
				'latitude' => 43.722451,
				'longitude' => -79.358620,
				'angle' => -7,
				'length' => 110,
				'width' => 40,
				'zoom' => 16,
				'layout_url' => null,
				'sport' => 'ultimate',
			],
			[
				'num' => '1',
				'is_open' => true,
				'indoor' => false,
				'surface' => 'grass',
				'rating' => 'B',
				'facility_id' => FACILITY_ID_BROADACRES,
				'latitude' => 43.648455,
				'longitude' => -79.570627,
				'angle' => 83,
				'length' => 110,
				'width' => 40,
				'zoom' => 16,
				'layout_url' => null,
				'sport' => 'soccer',
			],
			[
				'num' => '1',
				'is_open' => true,
				'indoor' => false,
				'surface' => 'grass',
				'rating' => 'B',
				'facility_id' => FACILITY_ID_BLOOR,
				'latitude' => 43.658476,
				'longitude' => -79.436645,
				'angle' => 75,
				'length' => 100,
				'width' => 40,
				'zoom' => 17,
				'layout_url' => null,
				'sport' => 'ultimate',
			],
			[
				'num' => '2',
				'is_open' => true,
				'indoor' => false,
				'surface' => 'grass',
				'rating' => 'B',
				'facility_id' => FACILITY_ID_BLOOR,
				'latitude' => 43.658476,
				'longitude' => -79.436645,
				'angle' => 75,
				'length' => 100,
				'width' => 40,
				'zoom' => 17,
				'layout_url' => null,
				'sport' => 'ultimate',
			],
			[
				'num' => '1',
				'is_open' => false,
				'indoor' => false,
				'surface' => 'grass',
				'rating' => 'C',
				'facility_id' => FACILITY_ID_MARILYN_BELL,
				'latitude' => null,
				'longitude' => null,
				'angle' => 0,
				'length' => 0,
				'width' => 0,
				'zoom' => 0,
				'layout_url' => null,
				'sport' => 'ultimate',
			],
			[
				'num' => '1',
				'is_open' => true,
				'indoor' => false,
				'surface' => 'turf',
				'rating' => 'B',
				'facility_id' => FACILITY_ID_CENTRAL_TECH,
				'latitude' => 43.662559,
				'longitude' => -79.409206,
				'angle' => -16,
				'length' => 108,
				'width' => 34,
				'zoom' => 19,
				'layout_url' => null,
				'sport' => 'ultimate',
			],
		];

		if (!defined('FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1')) {
			$i = 0;
			define('FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1', ++$i);
			define('FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2', ++$i);
			define('FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_3', ++$i);
			define('FIELD_ID_SUNNYBROOK_GREENSPACE', ++$i);
			define('FIELD_ID_BROADACRES', ++$i);
			define('FIELD_ID_BLOOR', ++$i);
			define('FIELD_ID_BLOOR2', ++$i);
			define('FIELD_ID_MARILYN_BELL', ++$i);
			define('FIELD_ID_CENTRAL_TECH', ++$i);
		}

		parent::init();
	}

}
