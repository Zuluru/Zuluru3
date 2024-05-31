<?php
/**
 * This file is where any local configuration customizations that can't be
 * handled through the .env file should go. Making edits here instead of
 * app.php minimizes the chance of anything breaking when an update is done.
 *
 * Any edits here should follow the same format and names as in app.php.
 * Settings entered in this file will overwrite anything with the same
 * name in app.php. A couple of common examples are given.
 *
 * Note that, unlike .env, this file is NOT required for Zuluru to function.
 */

return [
	'App' => [
		'theme' => null,
		'email' => [
			// Attachments may be located relative to the resources folder,
			// e.g. use ZULURU_RESOURCES . 'attachment.png', or they might
			// be taken from somewhere in your webroot.
			'attachments' => [],
			'newsletter_attachments' => [],
		],
	],

	'Security' => [
		// Which third-party system(s) to use for user authentication. Implementations are found
		// in src/Authentication/Authenticator. Don't include the "Authenticator" part of the
		// filename; for example, if you're using Drupal to manage logins, just put 'DrupalSession'
		// here, not 'DrupalSessionAuthenticator'. If you're using Zuluru for authentication, leave
		// this blank; don't set it to 'Zuluru'.
		// You'll typically also need to add some configuration details. See the implementation
		// file in question for details of what it expects here.
		'authenticators' => [],
		'authModel' => 'Users',
	],
];
