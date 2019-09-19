<?php
/**
 * Rule for combining the output of boolean rules via "or".
 */
namespace App\Module;

use App\Model\Entity\Team;

class RuleOr extends RuleMeta {
	public $meta_rule = true;
	public $reason_type = REASON_TYPE_CONSOLIDATED;

	public function parse($config) {
		$this->rule = [];
		while (strlen($config)) {
			list($rule, $config, $this->parse_error) = $this->parseOneRule($config);
			if (! $rule) {
				return false;
			}
			if (!empty($config)) {
				if ($config[0] != ',') {
					$this->parse_error = __('Components of OR rules must be separated by commas.');
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
		$reasons = $invariant_reasons = [];
		$status = false;
		$this->invariant = false;
		$all_failures_invariant = true;
		foreach ($this->rule as $rule) {
			$rule_success = $rule->evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url, $formats);
			$rule_reason = $rule->reason;
			if (array_key_exists($rule->reason_type, $formats)) {
				$rule_reason = ['format' => $formats[$rule->reason_type], 'replacements' => [$rule_reason]];
			}
			if ($rule_success) {
				if ((empty($reasons) || $complete) && !empty($rule_reason)) {
					$reasons[] = $rule_reason;
				}

				// If an invariant rule succeeds, then the OR can never fail
				if ($rule->invariant) {
					$this->invariant = true;
				}

				$status = true;
			} else {
				// If an invariant rule fails, then we generally don't want to report it,
				// since there's nothing the user can do
				if (!$rule->invariant) {
					$reasons[] = $rule_reason;
					$all_failures_invariant = false;
				} else {
					$invariant_reasons[] = $rule_reason;
				}

				if (!$this->redirect) {
					$this->redirect = $rule->redirect;
				}
			}
		}

		if ($status == false && $all_failures_invariant) {
			$this->invariant = true;
			$reasons = $invariant_reasons;
		}

		if (count($reasons) > 1) {
			$this->reason = ['OR' => $reasons];
		} else {
			$this->reason = array_pop($reasons);
		}

		return $status;
	}

	protected function merge(Array $people) {
		$this->people = array_unique(array_merge($this->people, $people));
		return true;
	}

}
