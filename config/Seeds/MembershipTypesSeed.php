<?php
use Migrations\AbstractSeed;

/**
 * MembershipTypes seed.
 */
class MembershipTypesSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'name' => 'full',
				'description' => __d('seeds', 'Full'),
				'active' => '1',
				'priority' => 1,
				'report_as' => 'full',
				'badge' => 'member_registered',
			],
			[
				'name' => 'intro',
				'description' => __d('seeds', 'Introductory'),
				'active' => '1',
				'priority' => 2,
				'report_as' => 'intro',
				'badge' => 'member_intro',
			],
			[
				'name' => 'junior_intro',
				'description' => __d('seeds', 'Junior Introductory'),
				'active' => '0',
				'priority' => 2,
				'report_as' => 'intro',
				'badge' => 'member_intro',
			],
			[
				'name' => 'trial',
				'description' => __d('seeds', 'Trial'),
				'active' => '0',
				'priority' => 2,
				'report_as' => 'intro',
				'badge' => 'member_trial',
			],
			[
				'name' => 'touring',
				'description' => __d('seeds', 'Touring'),
				'active' => '0',
				'priority' => 3,
				'report_as' => 'touring',
				'badge' => 'member_touring',
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('membership_types');
		$table->insert($this->data())->save();
	}
}
