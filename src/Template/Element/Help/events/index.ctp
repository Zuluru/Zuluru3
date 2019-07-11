<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isManager()) {
	echo $this->element('Help/topics', [
		'section' => 'events',
		'topics' => [
			'connections' => [
				'image' => 'connections_32.png',
			],
		],
	]);
}
