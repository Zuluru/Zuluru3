<?php
namespace App\Authenticator;

use App\Core\UserCache;
use Authentication\Authenticator\Result;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authenticator using Joomla session information
 *
 * If you are using this class, you may need to manually add the following
 * to the 'Security' section in your config/app_local.php file, if the
 * default settings don't work:
 *
	'authenticators' => [
		'JoomlaSession' => [
			// joomlaRoot must point to wherever your Joomla installation is,
			// e.g. the folder that holds the components, templates, modules
			// and includes folders. Do not include a trailing slash.
			'joomlaRoot' => '/path/to/your/joomla/installation',
		],
	],
 *
 */
class JoomlaSessionAuthenticator extends CMSSessionAuthenticator {

	public $hasher = 'Joomla';

	public function authenticate(ServerRequestInterface $request, ResponseInterface $response) {
		Configure::write('feature.control_account_creation', false);
		Configure::write('feature.authenticate_through', 'Joomla');

		// Check if we're running under Joomla
		$joomla_session = $request->getSession()->read('joomla');
		if ($joomla_session) {
			$joomla_session = unserialize(base64_decode($joomla_session));
			$joomla_user = $joomla_session->get('__default.user');
		} else {
			$joomla_user = null;
		}

		// Check if there's already a Zuluru session
		$result = $this->_sessionAuth->authenticate($request, $response);
		if ($result->isValid()) {
			$user = $result->getData();

			// Check that our session data matches Joomla session data.
			// Also, make sure that the person referenced in the session hasn't been deleted;
			// this will happen when a logged-in user is merged or deleted, for example.
			if ($joomla_user && $joomla_user->id == $user->id &&
				$user->has('person') &&
				!empty(UserCache::getInstance()->read('Person', $user->person->id))
			) {
				return new Result($user, Result::SUCCESS);
			}

			$this->clearIdentity($request, $response);
		}

		if ($joomla_user && !empty($joomla_user->id)) {
			try {
				$user = TableRegistry::get('UserJoomla')->get($joomla_user->id, [
					'contain' => ['People' => ['Groups']]
				]);
			} catch (RecordNotFoundException $ex) {
				$user = null;
			}

			if (!$user || empty($user->id)) {
				$this->clearIdentity($request, $response);
				return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
			}

			return new Result($user, Result::SUCCESS);
		}

		return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
	}

}
