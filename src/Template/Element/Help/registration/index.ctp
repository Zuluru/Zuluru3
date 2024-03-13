<?php
/**
 * @var \App\View\AppView $this
 */

echo $this->element('Help/topics', [
	'section' => 'registration',
	'topics' => [
		'introduction',
		'wizard',
	],
]);
