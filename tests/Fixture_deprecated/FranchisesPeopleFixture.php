<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FranchisesPeopleFixture
 *
 */
class FranchisesPeopleFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public $table = 'franchises_people';

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'franchises_people'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init(): void {
		$this->records = [
			[
				'franchise_id' => FRANCHISE_ID_RED,
				'person_id' => PERSON_ID_CAPTAIN,
			],
			[
				'franchise_id' => FRANCHISE_ID_RED2,
				'person_id' => PERSON_ID_CAPTAIN,
			],
			[
				'franchise_id' => FRANCHISE_ID_BLUE,
				'person_id' => PERSON_ID_CAPTAIN2,
			],
			[
				'franchise_id' => FRANCHISE_ID_BLUE,
				'person_id' => PERSON_ID_DUPLICATE,
			],
			[
				'franchise_id' => FRANCHISE_ID_LIONS,
				'person_id' => PERSON_ID_ANDY_SUB,
			],
		];

		parent::init();
	}

}
