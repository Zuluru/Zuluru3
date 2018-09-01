<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;

/**
 * Trait GameSlotsFixtureTrait
 *
 * Batch definition of game slots to create for fixtures.
 */
trait GameSlotsFixtureTrait {

	/**
	 * Description of game slot sets to create
	 *
	 * @var array
	 */
	protected $slot_definitions = [];

	public function __construct() {
		$this->slot_definitions = [
			'summer' => [
				'name' => 'WEEK',
				'fields' => [
					'SUNNYBROOK_1' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1,
					'SUNNYBROOK_2' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2,
					'SUNNYBROOK_3' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_3,
					'BROADACRES' => FIELD_ID_BROADACRES,
				],
				'start_date' => new FrozenDate('last Monday of May'),
				'weeks' => 12,
				'days' => [
					0 => [
						'name' => 'MONDAY',
						'divisions' => [DIVISION_ID_MONDAY_LADDER],
					],
					1 => [
						'name' => 'TUESDAY',
						'divisions' => [DIVISION_ID_TUESDAY_ROUND_ROBIN],
					],
					2 => [
						'name' => 'WEDNESDAY',
						'divisions' => [],
					],
					3 => [
						'name' => 'THURSDAY',
						'divisions' => [DIVISION_ID_THURSDAY_ROUND_ROBIN],
					],
				],
				'times' => [
					'' => [
						'game_start' => new FrozenTime('19:00:00'),
						'game_end' => new FrozenTime('21:00:00'),
					],
				],
			],
			'summer_sunday' => [
				'name' => 'WEEK',
				'fields' => [
					'CENTRAL_TECH' => FIELD_ID_CENTRAL_TECH,
				],
				'start_date' => new FrozenDate('last Sunday of May'),
				'weeks' => 12,
				'days' => [
					0 => [
						'name' => 'SUNDAY',
						'divisions' => [DIVISION_ID_SUNDAY_SUB],
					],
				],
				'times' => [
					'' => [
						'game_start' => new FrozenTime('19:00:00'),
						'game_end' => new FrozenTime('20:30:00'),
					],
				],
			],
			'summer_playoffs' => [
				'name' => 'PLAYOFFS',
				'fields' => [
					'SUNNYBROOK_1' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1,
					'SUNNYBROOK_2' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2,
					'SUNNYBROOK_3' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_3,
				],
				'start_date' => new FrozenDate('last Monday of August'),
				'weeks' => 1,
				'days' => [
					0 => [
						'name' => 'MONDAY',
						'divisions' => [DIVISION_ID_MONDAY_PLAYOFF],
					],
				],
				'times' => [
					'9AM' => [
						'game_start' => new FrozenTime('09:00:00'),
						'game_end' => new FrozenTime('10:55:00'),
					],
					'11AM' => [
						'game_start' => new FrozenTime('11:00:00'),
						'game_end' => new FrozenTime('12:55:00'),
					],
					'1PM' => [
						'game_start' => new FrozenTime('13:00:00'),
						'game_end' => new FrozenTime('14:55:00'),
					],
					'3PM' => [
						'game_start' => new FrozenTime('15:00:00'),
						'game_end' => new FrozenTime('16:55:00'),
					],
					'5PM' => [
						'game_start' => new FrozenTime('17:00:00'),
						'game_end' => new FrozenTime('18:55:00'),
					],
				],
			],
		];

		// Define all the required defines
		$this->__process(function ($context, $id, $field_id, $division_ids, FrozenDate $date, FrozenTime $start, FrozenTime $end, $name) {
			if (!defined($name)) {
				define($name, $id);
			}
		}, null);

		parent::__construct();
	}

	protected function __process(callable $func, $context) {
		$id = 0;
		foreach ($this->slot_definitions as $details) {
			for ($week = 1; $week <= $details['weeks']; ++$week) {
				foreach ($details['days'] as $day => $day_details) {
					$date = $details['start_date']->addWeeks($week)->addDays($day);
					foreach ($details['fields'] as $field_name => $field_id) {
						foreach ($details['times'] as $time_name => $times) {
							++$id;
							$name = "GAME_SLOT_ID_{$day_details['name']}_{$field_name}_{$details['name']}";
							if ($details['weeks'] > 1) {
								$name .= "_{$week}";
							}
							if ($time_name) {
								$name .= "_{$time_name}";
							}

							$func($context, $id, $field_id, $day_details['divisions'], $date, $times['game_start'], $times['game_end'], $name);
						}
					}
				}
			}
		}
	}

}
