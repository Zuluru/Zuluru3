<h2><?= __('Coordinator Guide') ?></h2>
<p><?= __('{0} provides a number of tools to make a coordinator\'s job go smoothly, but the number of options can be overwhelming at first. This guide will walk you through both the common and infrequent tasks, and help you to ensure that things go smoothly for the coaches, captains and players in your divisions.', ZULURU) ?></p>
<p><?= __('Division coordinator is a position of power and responsibility, so this guide also suggests guidelines for behaviour.') ?></p>

<?php
echo $this->element('Help/topics', [
	'section' => 'divisions',
	'topics' => [
		'edit' => [
			'image' => 'edit_32.png',
		],
		'add_teams' => [
			'image' => 'team_add_32.png',
		],
		'roster_add' => [
			'image' => 'roster_add_32.png',
		],
		'emails' => [
			'image' => 'email_32.png',
		],
	],
]);

echo $this->element('Help/topics', [
	'section' => 'schedules',
	'topics' => [
		'add' => [
			'title' => 'Add Games',
			'image' => 'schedule_add_32.png',
		],
		'edit' => [
			'title' => 'Schedule Edit',
			'image' => 'edit_32.png',
		],
		'delete' => [
			'title' => 'Delete Day',
			'image' => 'delete_32.png',
		],
		'reschedule' => [
			'image' => 'reschedule_32.png',
		],
	],
]);

echo $this->element('Help/topics', [
	'section' => 'divisions',
	'topics' => [
		'approve_scores' => [
			'image' => 'score_approve_32.png',
		],
	],
]);

echo $this->element('Help/topics', [
	'section' => 'schedules',
	'topics' => [
		'playoffs',
	],
]);

echo $this->element('Help/topics', [
	'section' => 'divisions',
	'topics' => [
		'spirit' => [
			'title' => 'Spirit Report',
			'image' => 'spirit_32.png',
		],
	],
]);
