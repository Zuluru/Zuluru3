<?php
namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;

class AndRule {
	/**
	 * Constructor.
	 *
	 * @param Array $rules The list of rules to check.
	 */
	public function __construct(Array $rules) {
		$this->_rules = $rules;
	}

	/**
	 * Performs the check
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to extract the fields from
	 * @param array $options Options passed to the check
	 * @return bool false if any rules are not true, true otherwise
	 */
	public function __invoke(EntityInterface $entity, array $options) {
		foreach ($this->_rules as $rule) {
			if (!$rule($entity, $options)) {
				return false;
			}
		}
		return true;
	}

}
