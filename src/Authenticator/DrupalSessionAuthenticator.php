<?php
namespace App\Authenticator;

use App\Core\UserCache;
use Authentication\Authenticator\Result;
use Authentication\Identifier\IdentifierInterface;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authenticator using Drupal session information
 *
 * If you are using this class, you may need to manually add the following
 * to the 'Security' section in your config/app_local.php file, if the
 * default settings don't work:
 *
	'authenticators' => [
		'DrupalSession' => [
			// drupalRoot must point to wherever your Drupal installation is,
			// e.g. the folder that holds the sites, themes, modules and
			// includes folders. Do not include a trailing slash.
			'drupalRoot' => '/path/to/your/drupal/installation',
			// If you are using the Zuluru Drupal module to replace Drupal's
			// user registration functionality, you must also set this to true
			'zuluruDrupalModule' => false,
		],
	],
 *
 */
class DrupalSessionAuthenticator extends CMSSessionAuthenticator {

	public $hasher = 'Drupal';

	public function __construct(IdentifierInterface $identifiers, $config) {
		// Set the default config for this object.
		$this->_defaultConfig = [
			'drupalRoot' => $_SERVER['DOCUMENT_ROOT'],
			// TODO: Have that module set something in the session, then we don't have to configure it here.
			'zuluruDrupalModule' => false,
		];

		parent::__construct($identifiers, $config);
	}

	public function authenticate(ServerRequestInterface $request, ResponseInterface $response) {
		Configure::write('feature.control_account_creation', $this->getConfig('zuluruDrupalModule'));
		Configure::write('feature.authenticate_through', 'Drupal');

		// Check if we're running under Drupal
		$prefix = ini_get('session.cookie_secure') ? 'SSESS' : 'SESS';
		$drupal_session_name = $prefix . substr(hash('sha256', Configure::read('Security.drupalCookieDomain')), 0, 32);
		$drupal_session_id = $request->cookie($drupal_session_name);

		// Check if there's already a Zuluru session
		$result = $this->_sessionAuth->authenticate($request, $response);
		if ($result->isValid()) {
			$user = $result->getData();

			// Check that our session data matches Drupal session data.
			// Also, make sure that the person referenced in the session hasn't been deleted;
			// this will happen when a logged-in user is merged or deleted, for example.
			if ($user->has('_matchingData') &&
				array_key_exists('DrupalSessions', $user->_matchingData) &&
				$user->_matchingData['DrupalSessions']->sid == $drupal_session_id &&
				$user->has('person') &&
				!empty(UserCache::getInstance()->read('Person', $user->person->id))
			) {
				return new Result($user, Result::SUCCESS);
			}

			$this->clearIdentity($request, $response);
		}

		if ($drupal_session_id) {
			$user = TableRegistry::get('UserDrupal')->find()
				->matching('DrupalSessions', function (Query $q) use ($drupal_session_id) {
					return $q->where(['DrupalSessions.sid' => $drupal_session_id]);
				})
				->contain([
					'People' => ['Groups'],
				])
				->first();

			if (!$user || empty($user->uid)) {
				$this->clearIdentity($request, $response);
				return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
			}

			return new Result($user, Result::SUCCESS);
		}

		return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
	}

}
