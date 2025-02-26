<?php
/**
 * Rule helper for returning any attribute from a record.
 */
namespace App\Module;

use App\Model\Entity\Team;
use Cake\ORM\Query;

class RuleAttribute extends Rule {

	/**
	 * Attribute to look at
	 */
	protected string $attribute;

	/**
	 * Path to attribute to look at
	 */
	protected array $attribute_path;

	public bool $invariant = true;

	private array $invariant_attributes = ['People.first_name', 'People.last_name', 'People.birthdate', 'People.gender', 'People.roster_designation', 'People.height'];

	public function parse($config) {
		$this->attribute_path = explode('.', trim($config, '"\''));
		$this->attribute = implode('.', $this->attribute_path);
		if (count($this->attribute_path) == 1) {
			$this->attribute = "People.{$this->attribute}";
		}
		array_unshift($this->attribute_path, 'People');

		// TODO: Check for valid attributes
		$this->invariant = in_array($this->attribute, $this->invariant_attributes);
		return true;
	}

	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		// TODO: Look for likely array keys (person, user model config name)
		// Maybe go through all properties and check in those that are entities? (But not arrays of entities...)
		$data = $params;
		foreach ($this->attribute_path as $key) {
			// We know we have a Person entity, so we can skip that part of the key
			if ($key != 'People') {
				if (is_array($data)) {
					return collection($data)->extract($key)->toArray();
				} else if ($data->has($key)) {
					$data = $data->$key;
				} else if ($data->has(strtolower($key))) {
					$key = strtolower($key);
					$data = $data->$key;
				} else {
					return '';
				}
			}
		}

		return $data;
	}

	protected function buildQuery(Query $query, $affiliate) {
		// Add some more possible joins based on the attribute being queried
		if (strpos($this->attribute, 'UserGroups.') !== false) {
			$query->innerJoin(['GroupsPeople' => 'groups_people'], 'People.id = GroupsPeople.person_id');
			$query->innerJoin(['UserGroups' => 'user_groups'], 'UserGroups.id = GroupsPeople.group_id');
		}

		return $this->attribute;
	}

	public function desc() {
		return __('have a {0}', __($this->attribute));
	}

}
