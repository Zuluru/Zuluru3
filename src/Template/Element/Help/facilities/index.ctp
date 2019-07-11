<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isManager()) {
	echo $this->element('Help/topics', [
		'section' => 'facilities',
		'topics' => [
			'edit' => [
				'image' => 'edit_32.png',
			],
		],
	]);
}
