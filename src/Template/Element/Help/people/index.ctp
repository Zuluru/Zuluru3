<?php
/**
 * @var \App\View\AppView $this
 */

echo $this->element('Help/topics', [
	'section' => 'people',
	'topics' => [
		'searching',
		'preferences',
		'photo_upload' => 'Player Photos',
		'skill_level',
	],
]);
