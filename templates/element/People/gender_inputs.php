<?php
/**
 * @var \App\View\AppView $this
 * @var string $prefix
 * @var \App\Model\Entity\Person $person
 * @var bool $secure
 * @var bool $edit
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

echo Configure::read('organization.gender_equity_statement');
if (!$edit || empty($person->gender) || in_array(Configure::read('profile.gender'), $access)) {
	echo $this->Jquery->toggleInput("{$prefix}gender", [
		'label' => __('Gender Identification'),
		'type' => 'select',
		'empty' => '---',
		'options' => Configure::read('options.gender'),
		'help' => __('Sharing your gender identity is optional. It can help us better understand the gender representation of our community, leading to more informed decisions about programming.'),
		'secure' => $secure,
	], [
		'values' => [
			'Prefer to specify' => ".{$class_prefix}self-defined-gender",
		],
	]);
} else {
	echo $this->Form->control("{$prefix}gender", [
		'label' => __('Gender Identification'),
		'disabled' => true,
		'class' => 'disabled',
		'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new gender'), $this->Html->link($admin, "mailto:$admin")),
	]);
}

$gender = isset($person) ? $person->gender : null;
if (!$edit || in_array(Configure::read('profile.gender'), $access) || $gender == 'Prefer to specify') {
	echo $this->Html->tag('div',
		$this->Form->control("{$prefix}gender_description", [
			'secure' => false,
		]),
		['class' => "{$class_prefix}self-defined-gender"]
	);
}

echo $this->Form->control("{$prefix}publish_gender", [
	'label' => __('Allow registered users to view gender identification'),
]);

if (Configure::read('gender.column') == 'roster_designation') {
	if (!$edit || empty($person->roster_designation) || in_array(Configure::read('profile.gender'), $access)) {
		echo $this->Form->control("{$prefix}roster_designation", [
			'options' => Configure::read('options.roster_designation'),
			'empty' => '---',
			'secure' => false,
			'help' => $this->element('People/roster_designation_help'),
		]);
	} else {
		echo $this->Form->control("{$prefix}roster_designation", [
			'disabled' => true,
			'class' => 'disabled',
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new roster designation'), $this->Html->link($admin, "mailto:$admin")),
		]);
	}
}

if (Configure::read('profile.pronouns')) {
	if (!$edit || empty($person->pronouns) || in_array(Configure::read('profile.pronouns'), $access)) {
		echo $this->Form->control("{$prefix}pronouns", [
			'label' => __('Pronouns'),
			'help' => __('In case we need to get in touch with you or introduce you to another player, captain, or volunteer, what pronouns should we use? (Optional)'),
			'secure' => $secure,
		]);
	} else {
		echo $this->Form->control("{$prefix}pronouns", [
			'label' => __('Pronouns'),
			'disabled' => true,
			'class' => 'disabled',
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new pronouns'), $this->Html->link($admin, "mailto:$admin")),
		]);
	}

	echo $this->Form->control("{$prefix}publish_pronouns", [
		'label' => __('Allow registered users to view pronouns'),
	]);
}
