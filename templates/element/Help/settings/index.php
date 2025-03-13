<?php
/**
 * @var \App\View\AppView $this
 */

$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isManager()) {
	echo $this->element('Help/topics', [
		'section' => 'settings/feature',
		'topics' => [
			'uls' => 'ULS',
		],
	]);
}
