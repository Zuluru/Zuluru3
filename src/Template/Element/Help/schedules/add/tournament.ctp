<?php
/**
 * @var \App\View\AppView $this
 */

echo $this->element('Help/topics', [
	'section' => 'schedules/add/tournament',
	'topics' => [
		'pools',
		'schedule_type',
	],
]);
