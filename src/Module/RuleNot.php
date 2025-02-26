<?php
/**
 * Rule for negating the output of any boolean rule.
 */
namespace App\Module;

use App\Model\Entity\Team;

class RuleNot extends RuleMeta {
	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		if ($this->rule == null)
			return null;
		$success = $this->rule->evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url, $formats);
		$this->reason = ['NOT' => $this->rule->reason];
		$this->reason_type = $this->rule->reason_type;

		// If the thing we're negating can't change, then neither can we
		$this->invariant = $this->rule->invariant;

		return (! $success);
	}

	// There is no guaranteed way to negate all queries, so we must
	// get the full list of users and remove those that match.
	protected function preQuery($affiliate) {
		// CakePHP should cache the query results, so there's no overhead
		// in doing this multiple times in a single ruleset.
		$query = $this->initializeQuery($affiliate);
		$this->people = $query->all()->extract('id')->toArray();
	}

	protected function merge(array $people) {
		$this->people = array_diff($this->people, $people);
		return true;
	}

}
