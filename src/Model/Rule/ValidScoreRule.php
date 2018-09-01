<?php
namespace App\Model\Rule;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Validation\Validation;

class ValidScoreRule {
	/**
	 * Constructor.
	 *
	 * @param string $lower The lowest allowable score
	 * @param string $upper The highest allowable score
	 */
	public function __construct($lower, $upper) {
		$this->_lower = $lower;
		$this->_upper = $upper;
	}

	/**
	 * Performs the validity check
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to extract the fields from
	 * @param array $options Options passed to the check
	 * @return bool
	 */
	public function __invoke(EntityInterface $entity, array $options) {
		$field = $options['errorField'];
		$check = $entity->$field;

		// For unplayed games, the score must be null
		if (in_array($entity->status, Configure::read('unplayed_status'))) {
			return ($check === null);
		}

		// For defaulted games, we're going to adjust it in beforeSave, so why check it here?
		if (in_array($entity->status, ['home_default', 'away_default'])) {
			return true;
		}

		// If it's a normal game and no score at all, that's fine
		if ($entity->status == 'normal' && $entity->home_score === null && $entity->away_score === null) {
			return true;
		}

		return Validation::range($check, $this->_lower,  $this->_upper);
	}

}
