<?php
use Cake\Core\Configure;

if (Configure::read('Perm.is_admin')) {
	echo $this->element('Help/topics', [
			'section' => 'events',
			'topics' => [
				'connections' => [
					'image' => 'connections_32.png',
				],
			],
	]);
}
