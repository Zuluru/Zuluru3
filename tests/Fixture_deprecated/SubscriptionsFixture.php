<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * SubscriptionsFixture
 *
 */
class SubscriptionsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'subscriptions'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'mailing_list_id' => MAILING_LIST_ID_MASTERS,
				'person_id' => PERSON_ID_ADMIN,
				'subscribed' => false,
				'created' => FrozenDate::now(),
			],
		];

		parent::init();
	}

}
