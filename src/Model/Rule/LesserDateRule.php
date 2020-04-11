<?php
namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;

class LesserDateRule {
	/**
	 * Constructor.
	 *
	 * @param string $compare The field to compare the given date against.
	 */
	public function __construct($compare) {
		$this->_compare = $compare;
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
			$check = $entity->$field;
		} else if (is_null($entity->$field)) {
			// Anything more useful to do than always pass checks that involve a null value?
			return true;
		}

		if (is_a($entity->{$this->_compare}, 'Cake\Chronos\ChronosInterface')) {
			$compare = $entity->{$this->_compare};
		}

		return ($check <= $compare);
	}

}
