<?php
namespace App\Policy;

use Authorization\Policy\ResultInterface;

class PluginPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		$result = parent::before($identity, $resource, $action);
		if ($result === false || $result instanceof ResultInterface) {
			return $result;
		}

		return $this->allowAdmin($identity);
	}

}
