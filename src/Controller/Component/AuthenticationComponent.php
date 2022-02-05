<?php
namespace App\Controller\Component;

use Authentication\Controller\Component\AuthenticationComponent as CakeAuthenticationComponent;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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

		return [$this->activeAffiliateId()];
	}

	public function applicableAffiliates($admin_only = false) {
		$identity = $this->getIdentity();
		if ($identity) {
			return $identity->applicableAffiliates($admin_only);
		}
		return [$this->activeAffiliateId() => Configure::read('organization.name')];
	}

	private function activeAffiliateId() {
		return Cache::remember('active_affiliate', function() {
			$affiliate = TableRegistry::getTableLocator()->get('Affiliates')
				->find('active')->first();
			return $affiliate ? $affiliate->id : 1;
		}, 'long_term');
	}
}
