<?php
echo $this->element('Help/topics', [
	'section' => 'teams',
	'topics' => [
		'joining_teams',
		'my_teams',
		'edit' => [
			'image' => 'edit_32.png',
		],
		'roster_add' => [
			'title' => 'Add Player',
			'image' => 'roster_add_32.png',
		],
		'roster_role',
	],
]);
