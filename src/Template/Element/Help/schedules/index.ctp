<?php
use Cake\Core\Configure;

if ($is_coordinator || Configure::read('Perm.is_admin')) {
	echo $this->element('Help/topics', [
			'section' => 'schedules',
			'topics' => [
				'add' => [
					'title' => 'Add Games',
					'image' => 'schedule_add_32.png',
				],
				'edit' => [
					'image' => 'edit_32.png',
				],
				'publish',
				'delete',
				'reschedule',
				'playoffs',
			],
	]);
}
