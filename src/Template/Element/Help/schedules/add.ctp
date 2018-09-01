<p><?= __('To add games to a schedule, you select the type of schedule to create, whether to publish games, and whether double-headers are allowed.') ?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'schedules/add',
	'topics' => [
		'schedule_type',
	],
	'compact' => true,
]);
echo $this->element('Help/topics', [
	'section' => 'games/edit',
	'topics' => [
		'publish',
		'double_header' => 'Double-headers',
		'double_booking' => 'Double-booking',
	],
	'compact' => true,
]);
echo $this->element('Help/topics', [
	'section' => 'divisions/edit',
	'topics' => [
		'exclude_teams',
	],
	'compact' => true,
]);
echo $this->element('Help/topics', [
	'section' => 'games/edit',
	'topics' => [
		'start_date',
	],
	'compact' => true,
]);
