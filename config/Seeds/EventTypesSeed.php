<?php
use Migrations\AbstractSeed;

/**
 * Event Types seed.
 */
class EventTypesSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'name' => __d('seeds', 'Membership'),
				'type' => 'membership',
			],
			[
				'name' => __d('seeds', 'Teams for Leagues'),
				'type' => 'team',
			],
			[
				'name' => __d('seeds', 'Individuals for Leagues'),
				'type' => 'individual',
			],
			[
				'name' => __d('seeds', 'Teams for Events'),
				'type' => 'team',
			],
			[
				'name' => __d('seeds', 'Individuals for Events'),
				'type' => 'individual',
			],
			[
				'name' => __d('seeds', 'Clinics'),
				'type' => 'generic',
			],
			[
				'name' => __d('seeds', 'Social Events'),
				'type' => 'generic',
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('event_types');
		$table->insert($this->data())->save();
	}
}
