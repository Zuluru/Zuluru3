<?php
/**
 * @var \App\Model\Entity\Affiliate $affiliate
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb(__('Profile'));
?>

<div class="settings form">
<?php
echo $this->form->create(null, ['align' => 'horizontal']);

echo $this->element('Settings/banner');
?>
	<fieldset>
		<legend><?= __('Profile Requirements') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'first_name',
	'options' => [
		'label' => Configure::read('profile.legal_name') ? __('Preferred Name') : __('First Name'),
		'type' => 'radio',
		'options' => Configure::read('options.access_required'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'legal_name',
	'options' => [
		'label' => __('Legal Name'),
		'type' => 'radio',
		'options' => Configure::read('options.access_required'),
		'help' => __('If enabled, \'First Name\' will be re-labelled as \'Preferred Name\' in profiles.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'last_name',
	'options' => [
		'label' => __('Last Name'),
		'type' => 'radio',
		'options' => Configure::read('options.access_required'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'gender',
	'options' => [
		'label' => __('Gender Identification'),
		'type' => 'radio',
		'options' => Configure::read('options.access_required'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'pronouns',
	'options' => [
		'label' => __('Pronouns'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'addr_street',
	'options' => [
		'label' => __('Street Address'),
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
		'label' => __('Postal Code'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'home_phone',
	'options' => [
		'label' => __('Home Phone'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'work_phone',
	'options' => [
		'label' => __('Work Phone'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'mobile_phone',
	'options' => [
		'label' => __('Mobile Phone'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'skill_level',
	'options' => [
		'label' => __('Skill Level'),
		'type' => 'radio',
		'options' => Configure::read('options.access_optional'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'year_started',
	'options' => [
		'label' => __('Year Started'),
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
		'label' => __('Birth Year Only'),
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
		'label' => __('Shirt Size'),
		'type' => 'radio',
		'options' => Configure::read('options.access_registration'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'profile',
	'name' => 'contact_for_feedback',
	'options' => [
		'label' => __('Contact for Feedback'),
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
