<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MailingListsFixture
 *
 */
class MailingListsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'mailing_lists'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Juniors',
				'opt_out' => true,
				'rule' => 'compare(attribute(birthdate) < format_date(\'today - 18 years\'))',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Masters',
				'opt_out' => true,
				'rule' => 'compare(attribute(birthdate) >= format_date(\'today - 35 years\'))',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Active',
				'opt_out' => true,
				'rule' => '',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Women',
				'opt_out' => true,
				'rule' => 'compare(attribute(roster_designation) = \'Womxn\'))',
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
		];

		if (!defined('MAILING_LIST_ID_JUNIORS')) {
			$i = 0;
			define('MAILING_LIST_ID_JUNIORS', ++$i);
			define('MAILING_LIST_ID_MASTERS', ++$i);
			define('MAILING_LIST_ID_ACTIVE', ++$i);
			define('MAILING_LIST_ID_WOMEN_SUB', ++$i);
		}

		parent::init();
	}

}
