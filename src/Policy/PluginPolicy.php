<?php
namespace App\Policy;

class PluginPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		parent::before($identity, $resource, $action);

		return $this->allowAdmin($identity);
	}

}
