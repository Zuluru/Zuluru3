<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QuestionnairesFixture
 *
 */
class QuestionnairesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'questionnaires'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Team',
				'active' => true,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'New',
				'active' => true,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Old',
				'active' => false,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'TEAM',
				'active' => true,
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
		];

		if (!defined('QUESTIONNAIRE_ID_TEAM')) {
			$i = 0;
			define('QUESTIONNAIRE_ID_TEAM', ++$i);
			define('QUESTIONNAIRE_ID_NEW', ++$i);
			define('QUESTIONNAIRE_ID_OLD', ++$i);
			define('QUESTIONNAIRE_ID_TEAM_SUB', ++$i);
		}

		parent::init();
	}

}
