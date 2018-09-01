<?php
/**
 * Rule for combining the output of boolean rules via "and".
 */
namespace App\Module;

use App\Model\Entity\Team;

class RuleAnd extends RuleMeta {
	public $meta_rule = true;
	public $reason_type = REASON_TYPE_CONSOLIDATED;

	public function parse($config) {
		$this->rule = [];
		while (strlen($config)) {
			list($rule, $config, $this->parse_error) = $this->parseOneRule($config);
			if (!$rule) {
				return false;
			}
			if (!empty($config)) {
				if ($config[0] != ',') {
					$this->parse_error = __('Components of AND rules must be separated by commas.');
					return false;
				}
				$config = substr($config, 1);
			}
			$this->rule[] = $rule;
		}
		return (count($this->rule) > 1);
	}

	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		if (empty($this->rule))
			return null;
		$reasons = [];
		$status = true;
		$this->invariant = false;
		foreach ($this->rule as $rule) {
			$rule_success = $rule->evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url, $formats);
			$rule_reason = $rule->reason;
			if (array_key_exists($rule->reason_type, $formats)) {
				$rule_reason = ['format' => $formats[$rule->reason_type], 'replacements' => [$rule_reason]];
			}
			if (!$rule_success) {
				if (!$this->redirect) {
					$this->redirect = $rule->redirect;
				}
				$status = false;

				// If an invariant rule fails, then the AND can never succeed
				if ($rule->invariant) {
					$this->invariant = true;
					$reasons = [$rule_reason];
					break;
				} else {
					$reasons[] = $rule_reason;
				}
			} else if ($complete) {
				if (!$rule->invariant) {
					$reasons[] = $rule_reason;
				}
			}
		}
		if (count($reasons) > 1) {
			$this->reason = ['AND' => $reasons];
		} else {
			$this->reason = array_pop($reasons);
		}
		return $status;
	}

	protected function merge(Array $people) {
		// If the saved array is empty, this is the first query
		if (empty($this->people)) {
			$this->people = $people;
		} else {
			$this->people = array_intersect($this->people, $people);
		}

		// If at any time there's none that match, skip the rest of the queries
		if (empty($this->people)) {
			return false;
		}

		return true;
	}

}
