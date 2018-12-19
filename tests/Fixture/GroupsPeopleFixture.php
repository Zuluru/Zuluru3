<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * GroupsPeopleFixture
 *
 */
class GroupsPeopleFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public $table = 'groups_people';

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'groups_people'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'person_id' => PERSON_ID_ADMIN,
				'group_id' => GROUP_ID_ADMINISTRATOR,
			],
			[
				'person_id' => PERSON_ID_MANAGER,
				'group_id' => GROUP_ID_MANAGER,
			],
			[
				'person_id' => PERSON_ID_COORDINATOR,
				'group_id' => GROUP_ID_VOLUNTEER,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN,
				'group_id' => GROUP_ID_PLAYER,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN2,
				'group_id' => GROUP_ID_PLAYER,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN3,
				'group_id' => GROUP_ID_PLAYER,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN4,
				'group_id' => GROUP_ID_PLAYER,
			],
			[
				'person_id' => PERSON_ID_PLAYER,
				'group_id' => GROUP_ID_PLAYER,
			],
			[
				'person_id' => PERSON_ID_PLAYER,
				'group_id' => GROUP_ID_PARENT,
			],
			[
				'person_id' => PERSON_ID_CHILD,
				'group_id' => GROUP_ID_PLAYER,
			],
			[
				'person_id' => PERSON_ID_DUPLICATE,
				'group_id' => GROUP_ID_PLAYER,
			],
			[
				'person_id' => PERSON_ID_ANDY_SUB,
				'group_id' => GROUP_ID_PLAYER,
			],
			[
				'person_id' => PERSON_ID_VISITOR,
				'group_id' => GROUP_ID_PLAYER,
			],
			[
				'person_id' => PERSON_ID_INACTIVE,
				'group_id' => GROUP_ID_PLAYER,
			],
		];

		parent::init();
	}

}
