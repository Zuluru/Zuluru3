<?php
namespace App\Model\Rule;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;

class InConfigRule {
	/**
	 * Constructor.
	 *
	 * @param string|array $config The configuration key to read for the list of valid options.
	 */
	public function __construct($config) {
		if (is_array($config)) {
			$this->_configKey = $config['key'];
			$this->_optional = $config['optional'];
		} else {
			$this->_configKey = $config;
			$this->_optional = false;
		}
	}

	/**
	 * Performs the existence check
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to extract the fields from
	 * @param array $options Options passed to the check
	 * @return bool
	 */
	public function __invoke(EntityInterface $entity, array $options) {
		// TODO: Handle questionnaire values: needed for SHIRT_SIZE in EventTypeIndividual::validateResponse, when we change that structure.
		/*
		if (is_array($check) && array_key_exists('question_id', $check) && array_key_exists('answer', $check)) {
			$check = $check['answer'];
		}
		*/

		$field = $options['errorField'];
		if (!$entity->has($field)) {
			if ($this->_optional) {
				return true;
			} else {
				return false;
			}
		}
		if (empty($entity->$field) && $entity->$field !== '0' && $this->_optional) {
			return true;
		}

		$check = $entity->$field;
		if (is_bool($check)) {
			$check = intval($check);
		}

		$values = Configure::read($this->_configKey);
		if (!is_array($values)) {
			trigger_error("Configuration '{$this->_configKey}' does not have an array of options.", E_USER_ERROR);
		}

		// Check for nested option arrays
		if (is_array(current($values))) {
			return collection($values)->some(function ($values) use ($check) {
				return array_key_exists($check, $values);
			});
		}

		return array_key_exists($check, $values);
	}

}
