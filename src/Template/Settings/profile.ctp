<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb(__('Profile'));
?>

<div class="settings form">
<?php
echo $this->Form->create(false, ['align' => 'horizontal']);

echo $this->element('Settings/banner');
?>
	<fieldset>
		<legend><?= __('Profile Requirements') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'first_name',
	'options' => [
		'label' => __('First name'),
		'type' => 'radio',
		'options' => Configure::read('options.access_required'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'last_name',
	'options' => [
		'label' => __('Last name'),
		'type' => 'radio',
		'options' => Configure::read('options.access_required'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'gender',
	'options' => [
		'label' => __('Gender'),
		'type' => 'radio',
		'options' => Configure::read('options.access_required'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'addr_street',
	'options' => [
		'label' => __('Street address'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'addr_city',
	'options' => [
		'label' => __('City'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'addr_prov',
	'options' => [
		'label' => __('Province'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'addr_country',
	'options' => [
		'label' => __('Country'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'addr_postalcode',
	'options' => [
		'label' => __('Postal code'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'home_phone',
	'options' => [
		'label' => __('Home phone'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'work_phone',
	'options' => [
		'label' => __('Work phone'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'mobile_phone',
	'options' => [
		'label' => __('Mobile phone'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'skill_level',
	'options' => [
		'label' => __('Skill level'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'year_started',
	'options' => [
		'label' => __('Year started'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'birthdate',
	'options' => [
		'label' => __('Birthdate'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'birth_year_only',
	'options' => [
		'label' => __('Birth year only'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, the system will not ask for birth month and day.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'height',
	'options' => [
		'label' => __('Height'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'shirt_size',
	'options' => [
		'label' => __('Shirt size'),
		'type' => 'radio',
		'options' => Configure::read('options.access_registration'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'contact_for_feedback',
	'options' => [
		'label' => __('Contact for feedback'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
