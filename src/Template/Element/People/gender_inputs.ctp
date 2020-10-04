<?php
/**
 * @type string $prefix
 * @type \App\Model\Entity\Person $person
 * @type boolean $secure
 * @type boolean $edit
 */

use Cake\Core\Configure;

if (is_array($edit)) {
	$access = $edit;
	$edit = true;
}

$admin = Configure::read('email.admin_email');
if (!empty($prefix)) {
	$class_prefix = str_replace('.', '-', $prefix) . '-';
} else {
	$class_prefix = '';
}

if (!$edit || in_array(Configure::read('profile.gender'), $access)) {
	switch (Configure::read('offerings.genders')) {
		case 'Open':
			$help = __('This information will help us better understand the gender diversity of the {0} community.', Configure::read('organization.short_name'));
			break;

		default:
			$help = false;
			break;
	}

	echo $this->Jquery->toggleInput("{$prefix}gender", [
		'label' => __('Gender Identification'),
		'type' => 'select',
		'empty' => '---',
		'options' => Configure::read('options.gender'),
		'help' => $help,
		'secure' => $secure,
	], [
		'values' => [
			'Self-defined' => ".{$class_prefix}self-defined-gender",
		],
	]);
} else {
	echo $this->Form->input("{$prefix}gender", [
		'label' => __('Gender Identification'),
		'disabled' => true,
		'class' => 'disabled',
		'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new gender'), $this->Html->link($admin, "mailto:$admin")),
	]);
}

$gender = isset($person) ? $person->gender : null;
if (!$edit || in_array(Configure::read('profile.gender'), $access) || $gender == 'Self-defined') {
	echo $this->Html->tag('div',
		$this->Form->input("{$prefix}gender_description", [
			'secure' => false,
		]),
		['class' => "{$class_prefix}self-defined-gender"]
	);
}

if (Configure::read('offerings.genders') !== 'Open' &&
	(!$edit || in_array(Configure::read('profile.gender'), $access) || !in_array($gender, Configure::read('options.gender_binary')))
) {
	echo $this->Html->tag('div',
		$this->Form->input("{$prefix}roster_designation", [
			'options' => Configure::read('options.roster_designation'),
			'empty' => '---',
			'secure' => false,
			'help' => $this->element('People/gender_equity_statement'),
		])
	);
}

if (!$edit || in_array(Configure::read('profile.pronouns'), $access)) {
	echo $this->Jquery->toggleInput("{$prefix}pronouns", [
		'label' => __('Pronouns'),
		'type' => 'select',
		'empty' => '---',
		'options' => Configure::read('options.pronouns'),
		'help' => $help,
		'secure' => $secure,
	], [
		'values' => [
			'Self-defined' => ".{$class_prefix}self-defined-pronouns",
		],
	]);
} else {
	echo $this->Form->input("{$prefix}pronouns", [
		'label' => __('Pronouns'),
		'disabled' => true,
		'class' => 'disabled',
		'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new pronouns'), $this->Html->link($admin, "mailto:$admin")),
	]);
}

$pronouns = isset($person) ? $person->pronouns : null;
if (!$edit || in_array(Configure::read('profile.pronouns'), $access) || $pronouns == 'Self-defined') {
	echo $this->Html->tag('div',
		$this->Form->input("{$prefix}personal_pronouns", [
			'secure' => false,
		]),
		['class' => "{$class_prefix}self-defined-pronouns"]
	);
}
