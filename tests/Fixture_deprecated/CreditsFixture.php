<?php
namespace App\Test\Fixture_deprecated;

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
				'payment_id' => null,
				'amount' => 11,
				'amount_used' => 10,
				'notes' => 'Credit note.',
				'created' => FrozenDate::now(),
				'created_person_id' => PERSON_ID_ADMIN,
			],
		];

		if (!defined('CREDIT_ID_CAPTAIN')) {
			$i = 0;
			define('CREDIT_ID_CAPTAIN', ++$i);
		}

		parent::init();
	}

}
