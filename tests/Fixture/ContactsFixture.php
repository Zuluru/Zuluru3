<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ContactsFixture
 *
 */
class ContactsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'contacts'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Leagues',
				'email' => 'leagues@zuluru.net',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Events',
				'email' => 'events@zuluru.net',
			],
			[
				'affiliate_id' => AFFILIATE_ID_SUB,
				'name' => 'Leagues',
				'email' => 'leagues@zuluru.org',
			],
		];

		if (!defined('CONTACT_ID_LEAGUES')) {
			$i = 0;
			define('CONTACT_ID_LEAGUES', ++$i);
			define('CONTACT_ID_EVENTS', ++$i);
			define('CONTACT_ID_LEAGUES_SUB', ++$i);
		}

		parent::init();
	}

}
