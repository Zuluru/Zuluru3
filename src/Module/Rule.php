<?php
/**
 * Base class for rule functionality.  This class provides some common utility
 * functions that derived classes need.
 */
namespace App\Module;

use PDOException;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\Core\ModuleRegistry;
use App\Exception\MissingModuleException;
use App\Exception\RuleException;

abstract class Rule {
	/**
	 * Rule (or chain of rules)
	 *
	 * @var Rule|Rule[]
	 */
	protected $rule = null;

	/**
	 * Parse error text, to give help to rule writers
	 */
	public ?string $parse_error = null;

	/**
	 * Reason why the rule passed or failed
	 *
	 * @var string|array
	 */
	public $reason = 'Unknown reason!';

	public int $reason_type = REASON_TYPE_PLAYER_ACTIVE;

	/**
	 * Indication of whether the rule can ever be expected to pass
	 */
	public bool $invariant = false;

	/**
	 * Where to redirect to for prerequisite completion, if applicable
	 */
	public ?array $redirect = null;

	/**
	 * Return a description of the rule, not required for all rules
	 *
	 * @return string String description
	 */
	public function desc() {
		return null;
	}

	/**
	 * Function to parse the provided configuration string. This default
	 * implementation will work in any case where a rule accepts only a
	 * single rule as its parameter.
	 *
	 * @param $config
	 * @return bool indicating success or failure of the parsing
	 */
	public function parse($config) {
		if (empty($config)) {
			Log::debug('Got an empty rule', 'rules');
			return false;
		}
		list($this->rule, $config, $this->parse_error) = $this->parseOneRule($config);
		return (empty($config) && $this->rule != null);
	}

	public static function parseOneRule($config) {
		// Check for a constant
		if (empty($config)) {
			$parse_error = __('Got an empty config.', $config);
			Log::debug($parse_error, 'rules');
			return [null, null, $parse_error];
		} else if ($config[0] == '\'' || $config[0] == '"') {
			$rule_name = 'constant';
			$p = 0;
			$p2 = Rule::findClose($config, $p, $config[0]);
		} else {
			// Anything else should be a rule name followed by arguments in parentheses
			$p = strpos($config, '(');
			$rule_name = trim(substr($config, 0, $p));
			if (empty($rule_name)) {
				$parse_error = __('Didn\'t find a rule name in "{0}".', $config);
				Log::debug($parse_error, 'rules');
				return [null, null, $parse_error];
			}
			$p2 = Rule::findClose($config, $p, ')', '(');
		}
		if ($p2 === false) {
			return [null, null, null];
		}
		$rule_config = trim(substr($config, $p + 1, $p2 - 1));
		list($rule, $parse_error) = Rule::initRule($rule_name, $rule_config);
		$config = trim(substr($config, $p + $p2 + 1));
		return [$rule, $config, $parse_error];
	}

	static private function findClose($config, $p, $close, $open = null) {
		$count = 1;
		for ($i = $p + 1; $i < strlen($config) && $count; ++ $i) {
			if ($config[$i] == $open) {
				++ $count;
			} else if ($config[$i] == $close) {
				-- $count;
			}
		}
		if ($count > 0) {
			return false;
		}
		return $i - $p - 1;
	}

	/**
	 * Create a rule object and initialize it with a configuration string
	 *
	 * @param string $rule The name of the rule
	 * @param string $config The configuration string
	 * @return Rule The created rule object on success, false otherwise
	 *
	 */
	static private function initRule($rule, $config) {
		// To get a unique rule object instead of possibly a reference to an existing one,
		// we add a unique number to the end of the name, and specify the className in the
		// options.
		try {
			// The module registry does Inflector::underscore on what it receives as a class name. If the rule name is
			// all caps, this causes issues. We want to be case insensitive about rule names anyway, so make it all
			// lower case to avoid all that.
			$lower = strtolower($rule);
			$rule_obj = ModuleRegistry::getInstance()->load("Rule:{$lower}" . \App\Lib\fake_id(), ['className' => "Rule:{$lower}"]);
			if ($rule_obj && $rule_obj->parse($config)) {
				return [$rule_obj, $rule_obj->parse_error];
			}
		} catch (MissingModuleException $ex) {
			return [null, __('Invalid rule "{0}"', $rule)];
		}

		Log::debug("Failed to initialize rule module $rule with $config.", 'rules');

		if ($rule_obj) {
			$parse_error = $rule_obj->parse_error;
			if (!empty($parse_error)) {
				Log::debug($parse_error, 'rules');
			}
			return [null, $parse_error];
		}

		return [null, null];
	}

	/**
	 * Execute the required queries, by building a query object and passing it
	 * down to nested rules for modification. This default implementation should
	 * work for any non-meta rules.
	 *
	 * @param int $affiliate
	 * @param array $conditions
	 * @return array of matching people
	 */
	public function query($affiliate, $conditions) {
		$query = $this->initializeQuery($affiliate);

		if (!empty($conditions)) {
			// Add some more possible joins based on the initial conditions.
			// This is a bit ugly. A better solution would perhaps involve passing $joins to query.
			$condition_string = serialize($conditions);
			if (strpos($condition_string, 'Related.') !== false) {
				$user_model = Configure::read('Security.authModel');
				$authenticate = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . $user_model);
				$primary_key = $authenticate->getPrimaryKey();

				// TODO: Use matching instead?
				$query->leftJoin(['PeoplePeople' => 'people_people'], 'People.id = PeoplePeople.relative_id');
				$query->leftJoin(['Related' => 'people'], 'Related.id = PeoplePeople.person_id');
				$query->leftJoin(["Related$user_model" => $authenticate->getTable()], "Related$user_model.$primary_key = Related.user_id");
			}

			$query->where($conditions);
		}

		if ($this->buildQuery($query, $affiliate)) {
			try {
				$people = $query->all()->extract('id')->toArray();
				return array_unique($people);
			} catch (PDOException $ex) {
				throw new RuleException(__('Database query error: Probably an invalid attribute.'));
			}
		} else {
			throw new RuleException(__('The syntax of the rule is valid, but it is not possible to build a query which will return the expected results. See the "rules engine" help for suggestions.'));
		}
	}

	/**
	 * @param $affiliate int|int[] List of affiliates to consider
	 * @return \Cake\ORM\Query
	 */
	protected function initializeQuery($affiliate) {
		// Create the query object to be manipulated
		$peopleTable = TableRegistry::getTableLocator()->get('People');
		$query = $peopleTable->find();

		// Add in invariant conditions, fields and joins
		$user_model = Configure::read('Security.authModel');
		$authenticate = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . $user_model);
		$id_field = $authenticate->getPrimaryKey();

		$query->select('People.id');
		$query->where([
			'People.complete' => true,
			'People.status' => 'active',
		]);

		$query->leftJoin([$user_model => $authenticate->getTable()], "$user_model.$id_field = People.user_id");

		// TODO: We should really not use this when using TeamCount or LeagueTeamCount or Registered or MemberType
		// or SignedWaiver or HasDocument or any other hypothetical rule that already includes affiliate info.
		if (Configure::read('feature.affiliates')) {
			$query->where(['AffiliatesPeople.affiliate_id IN' => $affiliate]);
			$query->innerJoin(['AffiliatesPeople' => 'affiliates_people'], 'AffiliatesPeople.person_id = People.id');
		}

		return $query;
	}

	/**
	 * Internal function for building required queries. Must be overloaded by the derived classes.
	 */
	abstract protected function buildQuery(Query $query, $affiliate);

	// TODO: Distinguish the boolean rules from helpers that return values?

}

/**
 * When building a query, do we need to use HAVING instead of WHERE?
 */
interface RuleHaving {
	public function having();
}
