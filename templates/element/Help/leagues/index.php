<?php
/**
 * @var \App\View\AppView $this
 */

$identity = $this->Authorize->getIdentity();
if ($identity && ($identity->isManager() || $identity->isCoordinator())) {
	echo $this->element('Help/topics', [
		'section' => 'leagues',
		'topics' => [
			'edit' => [
				'image' => 'edit_32.png',
			],
		],
	]);
}
