<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * CreditsFixture
 *
 */
class CreditsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'credits'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_CAPTAIN,
				'amount' => 1,
				'amount_used' => 10,
				'notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'created' => FrozenDate::now(),
				'created_person_id' => PERSON_ID_ADMIN,
			],
		];

		parent::init();
	}

}
