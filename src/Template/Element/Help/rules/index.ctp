<?php
use Cake\Core\Configure;

if (Configure::read('Perm.is_admin')) {
	echo $this->element('Help/topics', [
			'section' => 'rules',
			'topics' => [
				'rules' => 'Rule Definitions',
				'mailing_lists' => 'Using Rules with Mailing Lists',
			],
	]);
}
