<?php
namespace App\Authenticator;

use App\Core\UserCache;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Authentication\Identifier\IdentifierInterface;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
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

	public function authenticate(ServerRequestInterface $request): ResultInterface {
		Configure::write('feature.control_account_creation', $this->getConfig('zuluruDrupalModule'));
		Configure::write('feature.authenticate_through', 'Drupal');

		// Check if we're running under Drupal
		$drupal_session_id = $request->getCookie(Configure::read('Security.drupalSessionName'));

		// Check if there's already a Zuluru session
		$result = $this->_sessionAuth->authenticate($request);
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

			$response = new Response();
			$this->clearIdentity($request, $response);
		}

		if ($drupal_session_id) {
			$user = TableRegistry::getTableLocator()->get('UserDrupal')->find()
				->matching('DrupalSessions', function (Query $q) use ($drupal_session_id) {
					return $q->where(['DrupalSessions.sid' => $this->drupal_hash_base64($drupal_session_id)]);
				})
				->contain([
					'People' => ['UserGroups'],
				])
				->first();

			if (!$user || empty($user->uid)) {
				$response = new Response();
				$this->clearIdentity($request, $response);
				return new Result(null, ResultInterface::FAILURE_IDENTITY_NOT_FOUND);
			}

			return new Result($user, ResultInterface::SUCCESS);
		}

		return new Result(null, ResultInterface::FAILURE_IDENTITY_NOT_FOUND);
	}

	private function drupal_hash_base64(string $data): string {
		$hash = base64_encode(hash('sha256', $data, TRUE));
		// Modify the hash so it's safe to use in URLs.
		return strtr($hash, array(
			'+' => '-',
			'/' => '_',
			'=' => '',
		));
	}
}
