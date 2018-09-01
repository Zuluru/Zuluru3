<?php
/**
 * This file contains any custom configuration that applies only to CLI tasks.
 */

return [
	/**
	 * App configuration.
	 */
	'App' => [
		'fullBaseUrl' => env('SITE_BASE_URL'),
	],

	/**
	 * Command line execution has a different debug email delivery method.
	 * Note that this does not affect email sending in non-debug situations.
	 */
	'EmailTransport' => [
		'debug' => [
			'className' => 'Cli',
			'url' => env('EMAIL_TRANSPORT_CLI_URL', null),
		],
	],
];
