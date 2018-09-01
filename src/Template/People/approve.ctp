<?php

use Cake\Chronos\ChronosInterface;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb(__('Approve Account'));
$this->Html->addCrumb($person->full_name);
?>

<div class="people approve">
<h2><?= __('Approve Account') . ': ' . $person->full_name ?></h2>

<?php
$dispositions = [
	'approved' => __('Approved'),
];

$this_is_player = collection($person->groups)->some(function ($group) { return $group->id == GROUP_PLAYER; });
$this_is_coach = collection($person->groups)->some(function ($group) { return $group->id == GROUP_COACH; });

$use_shirt_size = Configure::read('profile.shirt_size');
if ($use_shirt_size == PROFILE_REGISTRATION) {
	$use_shirt_size = ($this_is_player || $this_is_coach);
}

$rows = [
	'full_name' => ['name' => 'Name'],
];

$rows['name'] = ['name' => 'Group', 'model' => 'groups', 'func' => 'groups'];

if (!empty($person->user_name)) {
	$rows['user_name'] = ['name' => 'Username'];
}

if (!empty($person->user_id) && Configure::read('feature.external_accounts')) {
	$rows['user_id'] = ['name' => __('{0} User Id', Configure::read('feature.manage_name'))];
}

$rows['id'] = ['name' => 'Zuluru User ID'];
$rows[] = 'email';

if (Configure::read('profile.home_phone')) {
	$rows[] = 'home_phone';
}

if (Configure::read('profile.work_phone')) {
	$rows[] = 'work_phone';
	$rows[] = 'work_ext';
}

if (Configure::read('profile.mobile_phone')) {
	$rows[] = 'mobile_phone';
}

if (Configure::read('profile.addr_street')) {
	$rows['addr_street'] = ['name' => 'Address'];
}

if (Configure::read('profile.addr_city')) {
	$rows['addr_city'] = ['name' => 'City'];
}

if (Configure::read('profile.addr_prov')) {
	$rows['addr_prov'] = ['name' => 'Province'];
}

if (Configure::read('profile.addr_postalcode')) {
	$rows['addr_postalcode'] = ['name' => 'Postal Code'];
}

if (Configure::read('profile.birthdate')) {
	$rows['birthdate'] = ['func' => 'date'];
}

if (Configure::read('profile.height')) {
	$rows['height'] = ['func' => 'height'];
}

$rows[] = 'gender';
if (Configure::read('gender.column') == 'roster_designation') {
	$rows[] = 'roster_designation';
}

if ($use_shirt_size) {
	$rows[] = 'shirt_size';
}

$sports = array_keys(Configure::read('options.sport'));
foreach ($sports as $sport) {
	$skill = collection($person->skills)->firstMatch(['sport' => $sport]);
	if ($skill && $skill->enabled) {
		$person->{"skill_level_$sport"} = $skill->skill_level;
		$person->{"year_started_$sport"} = $skill->year_started;
	} else {
		$person->{"skill_level_$sport"} = $person->{"year_started_$sport"} = null;
	}

	if (Configure::read('profile.skill_level')) {
		if (count($sports) > 1) {
			$rows["skill_level_$sport"] = ['name' => "Skill Level ($sport)"];
		} else {
			$rows["skill_level_$sport"] = ['name' => 'Skill Level'];
		}
	}

	if (Configure::read('profile.year_started')) {
		if (count($sports) > 1) {
			$rows["year_started_$sport"] = ['name' => "Year Started ($sport)"];
		} else {
			$rows["year_started_$sport"] = ['name' => 'Year Started'];
		}
	}
}

$rows['status'] = ['name' => 'Account Status'];

$cols = ['name' => [], 'person' => []];
$i = 0;
$has_data = [];
foreach ($rows as $key => $data) {
	$name = null;
	if (is_numeric($key)) {
		$field = $data;
		if ($person->has($field)) {
			$val = $person->$field;
		} else {
			$val = null;
		}
	} else {
		$field = $key;
		if (array_key_exists('name', $data)) {
			$name = $data['name'];
		}
		if (array_key_exists('model', $data)) {
			$record = $person->{$data['model']};
		} else {
			$record = $person;
		}
		if (array_key_exists('func', $data) && (!is_object($record))) {
			$val = $record;
		} else {
			$val = $record->$field;
		}
		if (array_key_exists('func', $data)) {
			$func = "format_{$data['func']}";
			$val = $func($val, $this);
		}
	}
	if ($name == null) {
		$name = Inflector::humanize($field);
	}
	// TODO: Replace this with a class?
	$cols['name'][] = [$name, ['style' => 'text-align: right; font-weight: bold; padding-right: 1em;']];
	$cols['person'][] = $val;
	if (!empty($val)) {
		$has_data[$i] = true;
	}
	++ $i;
}

if ($duplicates->count() > 0) {
	echo $this->Html->para('warning-message', __('The following users may be duplicates of this account (click to compare):'));

	$compare = [];
	foreach ($duplicates as $duplicate) {
		foreach ($sports as $sport) {
			$skill = collection($duplicate->skills)->firstMatch(['sport' => $sport]);
			if ($skill && $skill->enabled) {
				$duplicate->{"skill_level_$sport"} = $skill->skill_level;
				$duplicate->{"year_started_$sport"} = $skill->year_started;
			} else {
				$duplicate->{"skill_level_$sport"} = $duplicate->{"year_started_$sport"} = null;
			}
		}

		$dispositions["merge_duplicate:{$duplicate->id}"] = __('Merged backwards into {0} ({1})', $duplicate->full_name, $duplicate->id);
		$dispositions["delete_duplicate:{$duplicate->id}"] = __('Deleted as duplicate of {0} ({1})', $duplicate->full_name, $duplicate->id);
		$compare[] = $this->Jquery->toggleLink("{$duplicate->full_name} ({$duplicate->id})", [
			'hide' => '.duplicate',
			'show' => ".player_id_{$duplicate->id}",
		]);

		$i = 0;
		foreach ($rows as $key => $data) {
			if (is_numeric($key)) {
				if ($person->has($data)) {
					$user_val = $person->$data;
				} else {
					$user_val = null;
				}
				if ($duplicate->has($data)) {
					$val = $duplicate->$data;
				} else {
					$val = null;
				}
			} else {
				if (array_key_exists('model', $data)) {
					$user_record = $person->{$data['model']};
					$record = $duplicate->{$data['model']};
				} else {
					$user_record = $person;
					$record = $duplicate;
				}
				if (array_key_exists('func', $data) && (!is_object($record) || !$record->has($field))) {
					$user_val = $user_record;
					$val = $record;
				} else {
					if (!empty($user_record->$key)) {
						$user_val = $user_record->$key;
					} else {
						$user_val = null;
					}
					if (!empty($record->$key)) {
						$val = $record->$key;
					} else {
						$val = null;
					}
				}
				if (array_key_exists('func', $data)) {
					$func = "format_{$data['func']}";
					$user_val = $func($user_val, $this);
					$val = $func($val, $this);
				}
			}
			$class = "duplicate player_id_{$duplicate->id}";
			if (strtolower($val) == strtolower($user_val)) {
				$class .= ' warning-message';
			}
			$cols[$duplicate->id][] = [$val, ['class' => $class]];
			if (!empty($val)) {
				$has_data[$i] = true;
			}
			++ $i;
		}
	}
	echo $this->Html->nestedList($compare, ['class' => 'nav nav-pills']);
}

$dispositions['delete'] = __('Deleted silently');

echo '<br>';

$table_data = \App\Lib\array_transpose($cols);
foreach (array_keys($table_data) as $key) {
	if (!array_key_exists($key, $has_data)) {
		unset($table_data[$key]);
	}
}

echo $this->Html->tag('div',
	$this->Html->tag('table', $this->Html->tableCells($table_data, []), ['class' => 'table-hover table-condensed']),
	['class' => 'table-responsive']);

if (!empty($duplicates) && !$activated && $person->user_id) {
	echo $this->Html->para('warning-message', __('This user has not yet activated their account. If the user record is merged backwards, they WILL NOT be able to activate their account.'));
}

echo $this->Form->create($person, ['align' => 'horizontal']);
echo $this->Form->input('disposition', [
	'label' => __('This user should be:'),
	'options' => $dispositions,
	'empty' => '---',
]);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();

$this->Html->scriptBlock('jQuery(".duplicate").hide();', ['buffer' => true]);
?>
</div>

<?php
// Helper functions for formatting data
function format_date(ChronosInterface $data = null, $ths) {
	if (empty($data) || $data->year == 0) {
		return __('unknown');
	} else if (Configure::read('feature.birth_year_only')) {
		$data->year;
	} else {
		return $ths->Time->date($data);
	}
}
function format_height($data) {
	if (!empty($data)) {
		return $data . ' ' . (Configure::read('feature.units') == 'Metric' ? __('cm') : __('inches'));
	}
}
function format_groups($data) {
	$groups = collection($data)->extract('name')->toArray();
	if (empty($groups)) {
		return __('None');
	} else {
		return implode(', ', $groups);
	}
}
