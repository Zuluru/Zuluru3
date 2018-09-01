<?php

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Migrations\Migrations;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

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
			'pre_migrate' => function(Controller $controller, Migrations $migrations) {
				// TODO: Work around what appears to be a bug in the Migrations plugin. This whole block should be able to be skipped?
				$inputDefinition = new InputDefinition([
					new InputOption('source'),
					new InputOption('plugin'),
					new InputOption('connection'),
				]);
				$input = new ArrayInput([], $inputDefinition);
				$migrations->setInput($input);
				$config = $migrations->getConfig();
				$migrations->getManager($config);

				// Mark all migrations prior to the "install" one as migrated; they are for handling legacy databases
				$migrations->markMigrated(null, ['target' => 20180622171412]);
			},
			'post_migrate' => function(Controller $controller, Migrations $migrations) {
				$controller->Flash->success(__('Your admin password has been set to {0}. Log in right away and change it to something more memorable.', Configure::read('new_admin_password')));
			},
		],
	],
];
