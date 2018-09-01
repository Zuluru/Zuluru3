<?php
/**
 * @type string $prefix
 * @type string $gender
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
	echo $this->Jquery->toggleInput("{$prefix}gender", [
		'type' => 'select',
		'empty' => '---',
		'options' => Configure::read('options.gender'),
		'secure' => $secure,
	], [
		'values' => [
			'Trans' => ".{$class_prefix}trans",
			'Self-defined' => ".{$class_prefix}self-defined",
			'Prefer not to say' => ".{$class_prefix}prefer-not",
		],
	]);
} else {
	echo $this->Form->input("{$prefix}gender", [
		'disabled' => true,
		'class' => 'disabled',
		'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new gender'), $this->Html->link($admin, "mailto:$admin")),
	]);
}

if (!$edit || in_array(Configure::read('profile.gender'), $access) || $gender == 'Self-defined') {
	echo $this->Html->tag('div',
		$this->Form->input("{$prefix}gender_description", [
			'secure' => false,
		]),
		['class' => "{$class_prefix}self-defined"]
	);
}

if (!$edit || in_array(Configure::read('profile.gender'), $access) || !in_array($gender, Configure::read('options.gender_binary'))) {
	echo $this->Html->tag('div',
		$this->Form->input("{$prefix}roster_designation", [
			'options' => Configure::read('options.roster_designation'),
			'secure' => false,
			'help' => __('Our league recognizes the gender inequities in sport and is working towards making ultimate more equitable for woman players. As such, we are implementing a minimum requirement of women on your team’s roster. Do you wish to fill one of the designated women\'s spots on your team?'),
		]),
		['class' => "{$class_prefix}trans {$class_prefix}self-defined {$class_prefix}prefer-not"]
	);
}
