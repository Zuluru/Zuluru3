<?php
use Migrations\AbstractSeed;

/**
 * Provinces seed.
 */
class ProvincesSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'name' => 'Ontario',
			],
			[
				'name' => 'Quebec',
			],
			[
				'name' => 'Alberta',
			],
			[
				'name' => 'British Columbia',
			],
			[
				'name' => 'Manitoba',
			],
			[
				'name' => 'New Brunswick',
			],
			[
				'name' => 'Newfoundland',
			],
			[
				'name' => 'Northwest Territories',
			],
			[
				'name' => 'Nunavut',
			],
			[
				'name' => 'Nova Scotia',
			],
			[
				'name' => 'Prince Edward Island',
			],
			[
				'name' => 'Saskatchewan',
			],
			[
				'name' => 'Yukon',
			],
			[
				'name' => 'Alabama',
			],
			[
				'name' => 'Alaska',
			],
			[
				'name' => 'Arizona',
			],
			[
				'name' => 'Arkansas',
			],
			[
				'name' => 'California',
			],
			[
				'name' => 'Colorado',
			],
			[
				'name' => 'Connecticut',
			],
			[
				'name' => 'Delaware',
			],
			[
				'name' => 'Florida',
			],
			[
				'name' => 'Georgia',
			],
			[
				'name' => 'Hawaii',
			],
			[
				'name' => 'Idaho',
			],
			[
				'name' => 'Illinois',
			],
			[
				'name' => 'Indiana',
			],
			[
				'name' => 'Iowa',
			],
			[
				'name' => 'Kansas',
			],
			[
				'name' => 'Kentucky',
			],
			[
				'name' => 'Louisiana',
			],
			[
				'name' => 'Maine',
			],
			[
				'name' => 'Maryland',
			],
			[
				'name' => 'Massachusetts',
			],
			[
				'name' => 'Michigan',
			],
			[
				'name' => 'Minnesota',
			],
			[
				'name' => 'Mississippi',
			],
			[
				'name' => 'Missouri',
			],
			[
				'name' => 'Montana',
			],
			[
				'name' => 'Nebraska',
			],
			[
				'name' => 'Nevada',
			],
			[
				'name' => 'New Hampshire',
			],
			[
				'name' => 'New Jersey',
			],
			[
				'name' => 'New Mexico',
			],
			[
				'name' => 'New York',
			],
			[
				'name' => 'North Carolina',
			],
			[
				'name' => 'North Dakota',
			],
			[
				'name' => 'Ohio',
			],
			[
				'name' => 'Oklahoma',
			],
			[
				'name' => 'Oregon',
			],
			[
				'name' => 'Pennsylvania',
			],
			[
				'name' => 'Rhode Island',
			],
			[
				'name' => 'South Carolina',
			],
			[
				'name' => 'South Dakota',
			],
			[
				'name' => 'Tennessee',
			],
			[
				'name' => 'Texas',
			],
			[
				'name' => 'Utah',
			],
			[
				'name' => 'Vermont',
			],
			[
				'name' => 'Virginia',
			],
			[
				'name' => 'Washington',
			],
			[
				'name' => 'West Virginia',
			],
			[
				'name' => 'Wisconsin',
			],
			[
				'name' => 'Wyoming',
			],
		];

		$table = $this->table('provinces');
		$table->insert($data)->save();
	}
}
