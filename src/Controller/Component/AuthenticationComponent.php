<?php
namespace App\Controller\Component;

use Authentication\Controller\Component\AuthenticationComponent as CakeAuthenticationComponent;
use Cake\Core\Configure;

/**
 * Controller Component for interacting with Authentication.
 *
 */
class AuthenticationComponent extends CakeAuthenticationComponent {

	public function applicableAffiliateIds($admin_only = false) {
		$identity = $this->getIdentity();
		if ($identity) {
			return $identity->applicableAffiliateIds($admin_only);
		}
		return [1];
	}

	public function applicableAffiliates($admin_only = false) {
		$identity = $this->getIdentity();
		if ($identity) {
			return $identity->applicableAffiliates($admin_only);
		}
		return [1 => Configure::read('organization.name')];
	}

}
