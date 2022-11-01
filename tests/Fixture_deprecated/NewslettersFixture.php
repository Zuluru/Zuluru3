<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * NewslettersFixture
 *
 */
class NewslettersFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'newsletters'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Juniors clinic',
				'from_email' => 'clinics@zuluru.org',
				'to_email' => null,
				'subject' => 'Upcoming Juniors Clinics',
				'text' => 'There will be skills clinics for our junior members the next four Tuesdays.',
				'target' => FrozenDate::now()->subMonths(2),
				'delay' => 1,
				'batch_size' => 5,
				'personalize' => false,
				'created' => FrozenDate::now()->subMonths(2),
				'mailing_list_id' => MAILING_LIST_ID_JUNIORS,
				'reply_to' => null,
			],
			[
				'name' => 'Masters meetups',
				'from_email' => 'masters@zuluru.org',
				'to_email' => null,
				'subject' => 'Upcoming Masters Meetups',
				'text' => 'There are meetups for our masters-aged members the first Monday of each month.',
				'target' => FrozenDate::now(),
				'delay' => 1,
				'batch_size' => 10,
				'personalize' => true,
				'created' => FrozenDate::now(),
				'mailing_list_id' => MAILING_LIST_ID_MASTERS,
				'reply_to' => null,
			],
			[
				'name' => 'Womens clinics',
				'from_email' => 'women@zuluru.org',
				'to_email' => null,
				'subject' => 'Upcoming Womens Clinics',
				'text' => 'There are beginner and advanced clinics coming soon for our female members.',
				'target' => FrozenDate::now(),
				'delay' => 1,
				'batch_size' => 10,
				'personalize' => true,
				'created' => FrozenDate::now(),
				'mailing_list_id' => MAILING_LIST_ID_WOMEN_SUB,
				'reply_to' => null,
			],
		];

		if (!defined('NEWSLETTER_ID_JUNIOR_CLINICS')) {
			$i = 0;
			define('NEWSLETTER_ID_JUNIOR_CLINICS', ++$i);
			define('NEWSLETTER_ID_MASTER_MEETUPS', ++$i);
			define('NEWSLETTER_ID_WOMENS_CLINICS_SUB', ++$i);
		}

		parent::init();
	}

}
