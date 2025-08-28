<?php
declare(strict_types=1);

namespace App\TestSuite;

use App\Application;
use App\Authentication\ActAsIdentity;
use App\Core\UserCache;
use Authentication\Identity;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;
use Cake\View\View;

trait AuthorizationHelperTrait
{
	protected function createHelper(string $helperClass, int $personId): Helper
	{
		UserCache::setIdentity(null);

		$person = TableRegistry::getTableLocator()->get('People')->get($personId);
		if (!$person->user_id) {
			$this->fail('Cannot log in as a profile without a user record.');
		}

		$user_table = TableRegistry::getTableLocator()->get(Configure::read('Security.authModel', 'Users'));
		$user = $user_table->get($person->user_id);
		$user->person = $person;
		$identity = new Identity($user);

		$view = new View();
		$request = $view->getRequest();
		$authz = Application::getAuthorizationServiceStatic($request);

		$view->setRequest($request
			->withAttribute('identity', new ActAsIdentity($authz, $identity))
			->withAttribute('authorization', $authz)
		);

		return new $helperClass($view);
	}
}
