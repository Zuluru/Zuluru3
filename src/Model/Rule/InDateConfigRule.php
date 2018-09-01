<?php
namespace App\Model\Rule;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;

class InDateConfigRule {
	/**
	 * Constructor.
	 *
	 * @param string $config The configuration key to read for the valid options.
	 */
	public function __construct($config) {
		$this->_config = $config;
	}

	/**
	 * Performs the date check
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to extract the fields from
	 * @param array $options Options passed to the check
	 * @return bool
	 */
	public function __invoke(EntityInterface $entity, array $options) {
		$field = $options['errorField'];

		if (is_a($entity->$field, 'Cake\Chronos\ChronosInterface')) {
			$year = $entity->$field->year;
		} else {
			// Some date fields are just years as strings
			$year = $entity->field;
		}

		if (empty($year)||($year==1970)) {
			// Anything more useful to do than always pass checks that involve a null value?
			return true;
		}

		$min = Configure::read("options.year.{$this->_config}.min");
		$max = Configure::read("options.year.{$this->_config}.max");
		if ($min === null || $max === null) {
			return false;
		}

		return ($min <= $year && $year <= $max);
	}

}
