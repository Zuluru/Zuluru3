<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isManager()) {
	echo $this->element('Help/topics', [
			'section' => 'rules',
			'topics' => [
				'rules' => 'Rule Definitions',
				'mailing_lists' => 'Using Rules with Mailing Lists',
			],
	]);
}
