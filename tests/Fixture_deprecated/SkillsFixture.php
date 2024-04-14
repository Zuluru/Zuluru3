<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * SkillsFixture
 *
 */
class SkillsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'skills'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init(): void {
		$this->records = [
			[
				'person_id' => PERSON_ID_MANAGER,
				'sport' => 'ultimate',
				'enabled' => 1,
				'skill_level' => 6,
				'year_started' => FrozenTime::now()->year - 3,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN,
				'sport' => 'ultimate',
				'enabled' => 1,
				'skill_level' => 8,
				'year_started' => FrozenTime::now()->year - 2,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN2,
				'sport' => 'ultimate',
				'enabled' => 1,
				'skill_level' => 5,
				'year_started' => FrozenTime::now()->year - 1,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN3,
				'sport' => 'ultimate',
				'enabled' => 1,
				'skill_level' => 6,
				'year_started' => FrozenTime::now()->year - 5,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN4,
				'sport' => 'ultimate',
				'enabled' => 1,
				'skill_level' => 5,
				'year_started' => FrozenTime::now()->year - 4,
			],
			[
				'person_id' => PERSON_ID_PLAYER,
				'sport' => 'ultimate',
				'enabled' => 1,
				'skill_level' => 6,
				'year_started' => FrozenTime::now()->year - 1,
			],
			[
				'person_id' => PERSON_ID_CHILD,
				'sport' => 'ultimate',
				'enabled' => 1,
				'skill_level' => 2,
				'year_started' => FrozenTime::now()->year,
			],
			[
				'person_id' => PERSON_ID_DUPLICATE,
				'sport' => 'ultimate',
				'enabled' => 1,
				'skill_level' => 7,
				'year_started' => FrozenTime::now()->year - 3,
			],
			[
				'person_id' => PERSON_ID_DUPLICATE,
				'sport' => 'baseball',
				'enabled' => 1,
				'skill_level' => 4,
				'year_started' => FrozenTime::now()->year,
			],
			[
				'person_id' => PERSON_ID_INACTIVE,
				'sport' => 'ultimate',
				'enabled' => 1,
				'skill_level' => 7,
				'year_started' => FrozenTime::now()->year - 10,
			],
		];

		parent::init();
	}

}
