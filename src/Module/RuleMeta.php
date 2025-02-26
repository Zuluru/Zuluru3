<?php
/**
 * Base class for meta rule functionality.  This class provides some common
 * utility functions that derived classes need.
 */
namespace App\Module;

use Cake\ORM\Query;

abstract class RuleMeta extends Rule {
	protected array $people;

	public function query($affiliate, $conditions) {
		if (empty($this->rule)) {
			return false;
		}

		// Prepare for the rules
		$this->people = [];
		$this->preQuery($affiliate);

		if (!is_array($this->rule)) {
			$this->rule = [$this->rule];
		}
		foreach ($this->rule as $rule) {
			// Do a query for each rule that our meta-rule includes
			$people = $rule->query($affiliate, $conditions);

			if ($people === null) {
				// This means that an unresolvable query was detected
				return null;
			} else if (!is_array($people)) {
				trigger_error("Unexpected rule result '$people'", E_USER_ERROR);
			}

			// Merge the result sets as appropriate
			if (!$this->merge($people)) {
				// A false result means the result set is sure to be empty; skip remaining queries
				return [];
			}
		}

		return $this->people;
	}

	/**
	 * Take care of anything that needs to be done in this meta rule before starting queries.
	 * The default is to do nothing.
	 *
	 * @param $affiliate
	 */
	protected function preQuery($affiliate) {
	}

	/**
	 * Provide a dummy empty implementation of the required function.
	 */
	protected function buildQuery(Query $query, $affiliate) {
	}

	/**
	 * All meta rules need a way to merge results from various data sets.
	 */
	abstract protected function merge(array $people);
}
