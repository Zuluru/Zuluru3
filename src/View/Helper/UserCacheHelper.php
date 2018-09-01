<?php
namespace App\View\Helper;

use Cake\View\Helper;
use App\Core\UserCache;

class UserCacheHelper extends Helper {
	public function read($key, $id = null) {
		return UserCache::getInstance()->read($key, $id);
	}

	public function currentId() {
		return UserCache::getInstance()->currentId();
	}

	public function realId() {
		return UserCache::getInstance()->realId();
	}

	public function allActAs() {
		return UserCache::getInstance()->allActAs();
	}
}
