<?php
/**
 * Rule for comparing two values.
 */
namespace App\Module;

use App\Model\Entity\Team;
use Cake\ORM\Query;

class RuleCompare extends Rule {

	/**
	 * Comparison operator to use
	 */
	protected string $operator;

	private array $reverse = ['=' => '=', '<' => '>', '<=' => '>=', '>' => '<', '>=' => '<=', '!=' => '!='];

	public function parse($config) {
		$this->rule = [];

		// Get the left side rule
		list($rule, $new_config, $this->parse_error) = $this->parseOneRule($config);
		if (! $rule || empty($new_config)) {
			$this->parse_error = __('Failed to parse left side of "{0}": {1}', $config, $this->parse_error);
			return false;
		}
		$this->rule[] = $rule;
		$config = $new_config;

		// Check for a valid operator
		$p = strpos($config, ' ');
		if ($p === false) {
			$this->parse_error = __('Did not find a space in "{0}".', $config);
			return false;
		}
		$op = substr($config, 0, $p);
		if (!in_array($op, array_keys($this->reverse))) {
			$this->parse_error = __('Did not find a valid comparison operator in "{0}".', $config);
			return false;
		}
		$this->operator = $op;
		$config = trim(substr($config, $p));

		// Get the right side rule
		list($rule, $new_config, $this->parse_error) = $this->parseOneRule($config);
		if (! $rule || !empty($new_config)) {
			$this->parse_error = __('Failed to parse right side of "{0}": {1}', $config, $this->parse_error);
			return false;
		}
		$this->rule[] = $rule;

		return true;
	}

	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		if (count ($this->rule) != 2 || empty($this->operator)) {
			return null;
		}
		$left = $this->rule[0]->evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url, $formats);
		$right = $this->rule[1]->evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url, $formats);
		if ((is_array($left) || is_array($right)) && $this->operator != '=') {
			return null;
		}

		// If neither thing we're comparing can change, then neither can we
		$this->invariant = ($this->rule[0]->invariant && $this->rule[1]->invariant);

		$prefix = '';
		switch ($this->operator) {
			case '<':
				$success = ($left < $right);
				$result = __('less than');
				break;

			case '<=':
				$success = ($left <= $right);
				$result = __('less than or equal to');
				break;

			case '>':
				$success = ($left > $right);
				$result = __('greater than');
				break;

			case '>=':
				$success = ($left >= $right);
				$result = __('greater than or equal to');
				break;

			case '=':
				if (is_array($left) && is_array($right)) {
					$intersect = array_intersect($left, $right);
					$success = count($left) == count($right) && count($left) == count($intersect);
				} else if (is_array($left)) {
					$success = in_array($right, $left);
				} else if (is_array($right)) {
					$success = in_array($left, $right);
				} else {
					$success = ($left == $right);
				}
				$result = __('of');
				break;

			case '!=':
				$success = ($left != $right);
				$result = __('of');
				$prefix = __('NOT ');
				break;
		}

		$this->reason = $prefix . $this->rule[0]->desc() . ' ' . $result . ' ' . $this->rule[1]->desc();

		return $success;
	}

	protected function buildQuery(Query $query, $affiliate) {
		if (count($this->rule) != 2 || empty($this->operator)) {
			return false;
		}

		$left = $this->rule[0]->buildQuery($query, $affiliate);
		$right = $this->rule[1]->buildQuery($query, $affiliate);
		if ($left === false || $right === false) {
			return false;
		}

		// Queries with "having" will also have "group by", which doesn't produce
		// results for anyone with zero matches. Check for danger situations and
		// don't allow them to proceed.
		if ($this->rule[0] instanceof RuleHaving) {
			if ($this->operator[0] == '<' ||
				($this->operator == '=' && $right == '0') ||
				($this->operator == '!=' && $right != '0'))
			{
				return null;
			}

			$query->having([$this->rule[0]->having() . ' ' . $this->operator => $right]);
		} else if ($this->rule[1] instanceof RuleHaving) {
			if ($this->operator[0] == '>' ||
				($this->operator == '=' && $left == '0') ||
				($this->operator == '!=' && $left != '0'))
			{
				return null;
			}

			$query->having([$this->rule[1]->having() . ' ' . $this->reverse[$this->operator] => $left]);
		} else {
			$query->where(["$left {$this->operator}" => $right]);
		}

		return true;
	}

}
