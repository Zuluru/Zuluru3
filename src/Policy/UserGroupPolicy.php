<?php
namespace App\Policy;

class UserGroupPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		return (bool)$this->allowAdmin($identity);
	}

}
