<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QuestionsFixture
 *
 */
class QuestionsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'questions'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init(): void {
		$this->records = [
			[
				'name' => 'Returning team',
				'question' => 'Is your team returning from last season?',
				'type' => 'checkbox',
				'active' => true,
				'anonymous' => false,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Previous night',
				'question' => 'What night did your team play on last year?',
				'type' => 'select',
				'active' => true,
				'anonymous' => false,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Previous result',
				'question' => 'If so, where did your team finish?',
				'type' => 'checkbox',
				'active' => true,
				'anonymous' => false,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Obsolete',
				'question' => 'Do we need this question any more?',
				'type' => 'text',
				'active' => false,
				'anonymous' => false,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Returning team',
				'question' => 'Is your team returning from last season?',
				'type' => 'checkbox',
				'active' => true,
				'anonymous' => false,
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
			[
				'name' => 'Previous night',
				'question' => 'What night did your team play on last year?',
				'type' => 'select',
				'active' => true,
				'anonymous' => false,
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
		];

		if (!defined('QUESTION_ID_TEAM_RETURNING')) {
			$i = 0;
			define('QUESTION_ID_TEAM_RETURNING', ++$i);
			define('QUESTION_ID_TEAM_NIGHT', ++$i);
			define('QUESTION_ID_TEAM_PREVIOUS', ++$i);
			define('QUESTION_ID_TEAM_OBSOLETE', ++$i);
			define('QUESTION_ID_TEAM_RETURNING_SUB', ++$i);
			define('QUESTION_ID_TEAM_NIGHT_SUB', ++$i);
		}

		parent::init();
	}

}
