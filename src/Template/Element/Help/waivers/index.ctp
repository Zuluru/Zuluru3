<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isManager()) {
	echo $this->element('Help/topics', [
			'section' => 'waivers/edit',
			'topics' => [
				'text',
			],
	]);
}
