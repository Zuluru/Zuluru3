<?php
use Cake\Core\Configure;
?>
<?php
echo $this->element('Settings/input', [
	'person_id' => $id,
	'category' => 'personal',
	'name' => 'javelin',
	'options' => [
		'label' => __('Opt in to Javelin'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'confirm' => __('NOTE: By enabling this, you agree to make your contact information available to {0} (an official partner of {1}, bound by the privacy policies of {1} and {2}), for the purposes of using their service.',
			'Javelin', ZULURU, Configure::read('organization.name')
		),
	],
]);
