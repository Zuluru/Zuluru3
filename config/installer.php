<?php

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Migrations\Migrations;

// Override some default settings for the installer
return [
	'Installer' => [
		'Connection' => [
			'database'   => 'zuluru',
			'encoding'   => 'utf8mb4',
		],

		'Files' => [
			'app_php' => [
				'use' => false,
			],
			'database_php' => [
				'use' => false,
			],
			'app_local_php' => [
				'use' => true,
			],
			'_env' => [
				'use' => true,
			],
		],

		'Import' => [
			'ask' => false,
			'schema' => false,
			'migrations' => true,
			'post_migrate' => function(Controller $controller, Migrations $migrations) {
				$controller->Flash->success(__('Your admin password has been set to {0}. Log in right away and change it to something more memorable.', Configure::read('new_admin_password')));
			},
		],
	],
];
