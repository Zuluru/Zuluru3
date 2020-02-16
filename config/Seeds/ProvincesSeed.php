<?php
use Migrations\AbstractSeed;

/**
 * Provinces seed.
 */
class ProvincesSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'name' => __d('seeds', 'Ontario'),
			],
			[
				'name' => __d('seeds', 'Quebec'),
			],
			[
				'name' => __d('seeds', 'Alberta'),
			],
			[
				'name' => __d('seeds', 'British Columbia'),
			],
			[
				'name' => __d('seeds', 'Manitoba'),
			],
			[
				'name' => __d('seeds', 'New Brunswick'),
			],
			[
				'name' => __d('seeds', 'Newfoundland'),
			],
			[
				'name' => __d('seeds', 'Northwest Territories'),
			],
			[
				'name' => __d('seeds', 'Nunavut'),
			],
			[
				'name' => __d('seeds', 'Nova Scotia'),
			],
			[
				'name' => __d('seeds', 'Prince Edward Island'),
			],
			[
				'name' => __d('seeds', 'Saskatchewan'),
			],
			[
				'name' => __d('seeds', 'Yukon'),
			],
			[
				'name' => __d('seeds', 'Alabama'),
			],
			[
				'name' => __d('seeds', 'Alaska'),
			],
			[
				'name' => __d('seeds', 'Arizona'),
			],
			[
				'name' => __d('seeds', 'Arkansas'),
			],
			[
				'name' => __d('seeds', 'California'),
			],
			[
				'name' => __d('seeds', 'Colorado'),
			],
			[
				'name' => __d('seeds', 'Connecticut'),
			],
			[
				'name' => __d('seeds', 'Delaware'),
			],
			[
				'name' => __d('seeds', 'Florida'),
			],
			[
				'name' => __d('seeds', 'Georgia'),
			],
			[
				'name' => __d('seeds', 'Hawaii'),
			],
			[
				'name' => __d('seeds', 'Idaho'),
			],
			[
				'name' => __d('seeds', 'Illinois'),
			],
			[
				'name' => __d('seeds', 'Indiana'),
			],
			[
				'name' => __d('seeds', 'Iowa'),
			],
			[
				'name' => __d('seeds', 'Kansas'),
			],
			[
				'name' => __d('seeds', 'Kentucky'),
			],
			[
				'name' => __d('seeds', 'Louisiana'),
			],
			[
				'name' => __d('seeds', 'Maine'),
			],
			[
				'name' => __d('seeds', 'Maryland'),
			],
			[
				'name' => __d('seeds', 'Massachusetts'),
			],
			[
				'name' => __d('seeds', 'Michigan'),
			],
			[
				'name' => __d('seeds', 'Minnesota'),
			],
			[
				'name' => __d('seeds', 'Mississippi'),
			],
			[
				'name' => __d('seeds', 'Missouri'),
			],
			[
				'name' => __d('seeds', 'Montana'),
			],
			[
				'name' => __d('seeds', 'Nebraska'),
			],
			[
				'name' => __d('seeds', 'Nevada'),
			],
			[
				'name' => __d('seeds', 'New Hampshire'),
			],
			[
				'name' => __d('seeds', 'New Jersey'),
			],
			[
				'name' => __d('seeds', 'New Mexico'),
			],
			[
				'name' => __d('seeds', 'New York'),
			],
			[
				'name' => __d('seeds', 'North Carolina'),
			],
			[
				'name' => __d('seeds', 'North Dakota'),
			],
			[
				'name' => __d('seeds', 'Ohio'),
			],
			[
				'name' => __d('seeds', 'Oklahoma'),
			],
			[
				'name' => __d('seeds', 'Oregon'),
			],
			[
				'name' => __d('seeds', 'Pennsylvania'),
			],
			[
				'name' => __d('seeds', 'Rhode Island'),
			],
			[
				'name' => __d('seeds', 'South Carolina'),
			],
			[
				'name' => __d('seeds', 'South Dakota'),
			],
			[
				'name' => __d('seeds', 'Tennessee'),
			],
			[
				'name' => __d('seeds', 'Texas'),
			],
			[
				'name' => __d('seeds', 'Utah'),
			],
			[
				'name' => __d('seeds', 'Vermont'),
			],
			[
				'name' => __d('seeds', 'Virginia'),
			],
			[
				'name' => __d('seeds', 'Washington'),
			],
			[
				'name' => __d('seeds', 'West Virginia'),
			],
			[
				'name' => __d('seeds', 'Wisconsin'),
			],
			[
				'name' => __d('seeds', 'Wyoming'),
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('provinces');
		$table->insert($this->data())->save();
	}
}
