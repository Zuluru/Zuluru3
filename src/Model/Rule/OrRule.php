<?php
namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;

class OrRule {
	/**
	 * Constructor.
	 *
	 * @param array $rules The list of rules to check.
	 */
	public function __construct(array $rules) {
		$this->_rules = $rules;
	}

	/**
	 * Performs the check
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to extract the fields from
	 * @param array $options Options passed to the check
	 * @return bool false if no rules are true, true otherwise
	 */
	public function __invoke(EntityInterface $entity, array $options) {
		foreach ($this->_rules as $rule) {
			if ($rule($entity, $options)) {
				return true;
			}
		}
		return false;
	}

}
