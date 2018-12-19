<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isManager()) {
	echo $this->element('Help/topics', [
			'section' => 'settings/feature',
			'topics' => [
				'twitter',
				'uls' => 'ULS',
			],
	]);
}
