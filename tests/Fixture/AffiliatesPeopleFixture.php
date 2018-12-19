<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AffiliatesPeopleFixture
 *
 */
class AffiliatesPeopleFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public $table = 'affiliates_people';

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'affiliates_people'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_ADMIN,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_MANAGER,
				'position' => 'manager',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_COORDINATOR,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_CAPTAIN,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_CAPTAIN2,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_CAPTAIN3,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_CAPTAIN4,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_PLAYER,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_CHILD,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_DUPLICATE,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_SUB,
				'person_id' => PERSON_ID_DUPLICATE,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_SUB,
				'person_id' => PERSON_ID_ANDY_SUB,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_VISITOR,
				'position' => 'player',
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'person_id' => PERSON_ID_INACTIVE,
				'position' => 'player',
			],
		];

		parent::init();
	}

}
