<?php
declare(strict_types=1);

namespace App\Policy;

use App\Core\UserCache;
use Cake\Core\Configure;

class LockedResult extends RedirectResult
{
	public function __construct()
	{
		$reason = __('Your profile is currently {0}, so you can continue to use the site, but may be limited in some areas. To reactivate, {1}.',
			__(UserCache::getInstance()->read('Person.status')),
			__('contact {0}', Configure::read('email.admin_name'))
		);

		parent::__construct($reason);
	}
}
