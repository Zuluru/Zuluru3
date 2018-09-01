<?php
/**
 * Rule for handling a constant.  Can be invoked by name, or by
 * any string starting with ' or ".
 */
namespace App\Module;

use App\Model\Entity\Team;
use Cake\ORM\Query;

class RuleConstant extends Rule {
	// Constants can never change, by definition
	public $invariant = true;

	/**
	 * Constant to return
	 *
	 * @var string
	 */
	protected $constant;

	public function parse($config) {
		$this->constant = trim($config, '"\'');
		return true;
	}

	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		return $this->constant;
	}

	protected function buildQuery(Query $query, $affiliate) {
		return $this->constant;
	}

	// Just a constant, so we simply return our configured value
	public function desc() {
		return $this->constant;
	}

}
