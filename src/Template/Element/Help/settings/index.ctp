<?php
use Cake\Core\Configure;

if (Configure::read('Perm.is_admin')) {
	echo $this->element('Help/topics', [
			'section' => 'settings/feature',
			'topics' => [
				'twitter',
				'uls' => 'ULS',
			],
	]);
}
