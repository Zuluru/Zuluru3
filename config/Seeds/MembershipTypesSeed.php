<?php
use Migrations\AbstractSeed;

/**
 * MembershipTypes seed.
 */
class MembershipTypesSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'name' => 'full',
				'description' => __('Full'),
				'active' => '1',
				'priority' => 1,
				'report_as' => 'full',
				'badge' => 'member_registered',
			],
			[
				'name' => 'intro',
				'description' => __('Introductory'),
				'active' => '1',
				'priority' => 2,
				'report_as' => 'intro',
				'badge' => 'member_intro',
			],
			[
				'name' => 'junior_intro',
				'description' => __('Junior Introductory'),
				'active' => '0',
				'priority' => 2,
				'report_as' => 'intro',
				'badge' => 'member_intro',
			],
			[
				'name' => 'trial',
				'description' => __('Trial'),
				'active' => '0',
				'priority' => 2,
				'report_as' => 'intro',
				'badge' => 'member_trial',
			],
			[
				'name' => 'touring',
				'description' => __('Touring'),
				'active' => '0',
				'priority' => 3,
				'report_as' => 'touring',
				'badge' => 'member_touring',
			],
		];

		$table = $this->table('membership_types');
		$table->insert($data)->save();
	}
}
