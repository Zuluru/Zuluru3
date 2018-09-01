<?php
use Cake\Core\Configure;

if (Configure::read('Perm.is_admin')) {
	echo $this->element('Help/topics', [
			'section' => 'facilities',
			'topics' => [
				'edit' => [
					'image' => 'edit_32.png',
				],
			],
	]);
}
