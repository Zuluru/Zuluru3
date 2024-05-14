<?php
namespace App\Authenticator;

use App\Core\UserCache;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ServerRequestInterface;

class ZikulaSessionAuthenticator extends CMSSessionAuthenticator {

	public function authenticate(ServerRequestInterface $request): ResultInterface {
		Configure::write('feature.control_account_creation', $this->getConfig('zuluruDrupalModule'));
		Configure::write('feature.authenticate_through', 'Zikula');

		// Check if we're logged in to Zikula
		$zikula_user_id = $request->getSession()->read('PNSVuid');

		// Check if there's already a Zuluru session
		$result = $this->_sessionAuth->authenticate($request, $response);
		if ($result->isValid()) {
			$user = $result->getData();

			// Check that our session data matches Zikula session data.
			// Also, make sure that the person referenced in the session hasn't been deleted;
			// this will happen when a logged-in user is merged or deleted, for example.
			if ($zikula_user_id && $zikula_user_id == $user->id &&
				$user->has('person') &&
				!empty(UserCache::getInstance()->read('Person', $user->person->id))
			) {
				return new Result($user, Result::SUCCESS);
			}

			$this->clearIdentity($request, $response);
		}

		if ($zikula_user_id) {
			try {
				$user = TableRegistry::getTableLocator()->get('UserZikula')->get($zikula_user_id, [
					'contain' => ['People' => ['UserGroups']]
				]);
			} catch (RecordNotFoundException $ex) {
				$user = null;
			}

			if (!$user || empty($user->uid)) {
				$this->clearIdentity($request, $response);
				return new Result(null, ResultInterface::FAILURE_IDENTITY_NOT_FOUND);
			}

			return new Result($user, ResultInterface::SUCCESS);
		}

		return new Result(null, ResultInterface::FAILURE_IDENTITY_NOT_FOUND);
	}

}
