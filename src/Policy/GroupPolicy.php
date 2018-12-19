<?php
namespace App\Policy;

class GroupPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		return $this->allowAdmin($identity) ? true : false;
	}

}
