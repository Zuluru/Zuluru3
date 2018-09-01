<?php
use Migrations\AbstractSeed;

/**
 * Event Types seed.
 */
class EventTypesSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'name' => 'Membership',
				'type' => 'membership',
			],
			[
				'name' => 'Teams for Leagues',
				'type' => 'team',
			],
			[
				'name' => 'Individuals for Leagues',
				'type' => 'individual',
			],
			[
				'name' => 'Teams for Events',
				'type' => 'team',
			],
			[
				'name' => 'Individuals for Events',
				'type' => 'individual',
			],
			[
				'name' => 'Clinics',
				'type' => 'generic',
			],
			[
				'name' => 'Social Events',
				'type' => 'generic',
			],
		];

		$table = $this->table('event_types');
		$table->insert($data)->save();
	}
}
