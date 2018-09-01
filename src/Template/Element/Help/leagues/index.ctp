<?php
use Cake\Core\Configure;

if (Configure::read('Perm.is_admin') || $is_coordinator) {
	echo $this->element('Help/topics', [
			'section' => 'leagues',
			'topics' => [
				'edit' => [
					'image' => 'edit_32.png',
				],
			],
	]);
}
