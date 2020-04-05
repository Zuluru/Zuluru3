<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb(__('Team'));
?>

<div class="settings form">
<?php
echo $this->Form->create(false, ['align' => 'horizontal']);

echo $this->element('Settings/banner');
?>
	<fieldset>
		<legend><?= __('Team Features') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'female_captain',
	'options' => [
		'label' => __('Require Man and Woman Captains'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable the requirement for co-ed teams to have both a man and a woman captain (or coach, where applicable).'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'franchises',
	'options' => [
		'label' => __('Handle Franchises'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable linking of teams through franchises.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'shirt_colour',
	'options' => [
		'label' => __('Shirt Colours'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Disable this if teams don\'t have predetermined shirt colours (e.g. if you use pinnies or if matching shirt colours on a team is unimportant).'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'shirt_numbers',
	'options' => [
		'label' => __('Shirt Numbers'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable everything to do with shirt numbers. If enabled here, teams can still opt not to use this feature.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'urls',
	'options' => [
		'label' => __('Allow URLs'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable attachment of URLs to team and franchise records.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'flickr',
	'options' => [
		'label' => 'Flickr',
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable attachment of Flickr slideshows to team records.'),
	],
]);
?>
	</fieldset>

	<fieldset>
		<legend><?= __('Location Preference Features') ?></legend>
		<p class="warning-message"><?= __('Any or all of these options may be enabled; {0} will be allocated in order of most specific available preference to least.', Configure::read('UI.fields')) ?></p>
<?php
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'home_field',
	'options' => [
		'label' => __('Home Field'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, administrators will be able to assign home {0} to teams. Teams with home {0} will be scheduled there whenever possible.',
			Configure::read('UI.fields')
		),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'facility_preference',
	'options' => [
		'label' => __('Facility Preference'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, teams will be allowed to set a list of preferred facilities for scheduling.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'region_preference',
	'options' => [
		'label' => __('Region Preference'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, teams will be allowed to set a regional preference for scheduling.'),
	],
]);
?>
	</fieldset>

	<fieldset>
		<legend><?= __('Roster Management Features') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'force_roster_request',
	'options' => [
		'label' => __('Force Roster Request Responses'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, players will be forced to respond to roster requests the next time they sign on. It is recommended to use either this or Generate Roster Emails, not both.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'generate_roster_email',
	'options' => [
		'label' => __('Generate Roster Email'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, emails will be sent to players invited to join rosters, and coaches and captains who have players request to join their teams. It is recommended to use either this or Force Roster Request Responses, not both.'),
	],
]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
