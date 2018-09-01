<?php
namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;
use App\Core\ModuleRegistry;

class RuleSyntaxRule {
	/**
	 * Performs the syntax check
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to extract the fields from
	 * @param array $options Options passed to the check
	 * @return bool|mixed Reason for failure, true otherwise
	 */
	public function __invoke(EntityInterface $entity, array $options) {
		$rule_obj = ModuleRegistry::getInstance()->load('RuleEngine');
		$field = $options['errorField'];
		if (!$rule_obj->init($entity->$field)) {
			return $rule_obj->parse_error;
		}
		return true;
	}

}
