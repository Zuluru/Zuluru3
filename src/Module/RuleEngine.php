<?php
/**
 * Module for rules engine functionality.  This class handles all of
 * the rule chaining.
 */
namespace App\Module;

use App\Model\Entity\Team;

class RuleEngine {
	/**
	 * The object implementing the rule functionality
	 *
	 * @var Rule
	 */
	protected $rule = null;

	// TODOLATER: Turn all of these into functions that call the same function in the rule?

	/**
	 * Parse error text, to give help to rule writers
	 *
	 * @var string
	 */
	public $parse_error = null;

	/**
	 * Reason why the rule passed or failed
	 *
	 * @var string
	 */
	public $reason = 'Unknown reason!';

	/**
	 * Where to redirect to for prerequisite completion, if applicable
	 *
	 * @var array
	 */
	public $redirect = null;

	/**
	 * Initialize the rule engine, loading all required modules and
	 * initializing each of them.
	 *
	 * Rules may overload this if necessary, but the default should suffice.
	 *
	 * @param mixed $config A configuration string defining the rule chain
	 * @return mixed True if successful, false if there is some error in the config
	 */
	public function init($config) {
		if (empty($config)) {
			$this->rule = null;
			return true;
		}
		list($this->rule, $config, $this->parse_error) = Rule::parseOneRule($config);
		return (empty($config) && $this->rule != null);
	}

	/**
	 * Evaluate the rule chain against an input.
	 *
	 * @param mixed $affiliate The affiliate to check the rule in
	 * @param mixed $params An array with parameters used by the various rules
	 * @param mixed $team An array with team information, if applicable
	 * @param mixed $strict If false, we will allow things with prerequisites that are not yet filled but can easily be
	 * @param mixed $text_reason If true, reasons returned will be only text, no links embedded
	 * @param mixed $complete If true, the reason text will include everything, otherwise it will be situation-specific
	 * @param mixed $absolute_url If true, any links in the reason text will include the host and full path, for emails
	 * @param mixed $formats Array of formats to use for reporting various types of errors
	 * @return mixed True if the rule check passes, false if it fails, null if
	 * there is an error
	 *
	 */
	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		if ($this->rule == null) {
			return null;
		}
		$success = $this->rule->evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url, $formats);
		$this->reason = $this->rule->reason;
		if (array_key_exists($this->rule->reason_type, $formats)) {
			$this->reason = ['format' => $formats[$this->rule->reason_type], 'replacements' => [$this->reason]];
		}
		$this->redirect = $this->rule->redirect;

		return $success;
	}

	/**
	 * Perform a query that will find all people matching the rule.
	 *
	 * @param mixed $affiliate The affiliate to check the rule in
	 * @return mixed Array of conditions, contains, etc. defining the query, or false if something failed
	 *
	 */
	public function query($affiliate, $conditions = []) {
		if ($this->rule == null) {
			return null;
		}

		return $this->rule->query($affiliate, $conditions);
	}

}
