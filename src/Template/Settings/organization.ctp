<?php
/**
 * @type string[] $plugin_elements
 * @type \App\Model\Entity\Affiliate $affiliate
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Cake\Utility\Text;

$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb(__('Organization'));
?>

<div class="settings form">
<?php
if ($affiliate) {
	$empty = __('Use default');
} else {
	$empty = false;
}
echo $this->Form->create(false, ['align' => 'horizontal']);

echo $this->element('Settings/banner');
?>
	<fieldset>
		<legend><?= __('Organization') ?></legend>
<?php
if (!$affiliate) {
	echo $this->element('Settings/input', [
		'category' => 'organization',
		'name' => 'name',
		'options' => [
			'label' => __('Name'),
			'help' => __('Your organization\'s full name.'),
		],
	]);
}

echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'short_name',
	'options' => [
		'label' => __('Short Name'),
		'help' => __('Your organization\'s abbreviated name or acronym.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'address',
	'options' => [
		'label' => __('Address'),
		'help' => __('Your organization\'s street address.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'address2',
	'options' => [
		'label' => __('Unit'),
		'help' => __('Your organization\'s unit number, if any.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'city',
	'options' => [
		'label' => __('City'),
		'help' => __('Your organization\'s city.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'province',
	'options' => [
		'label' => __('Province'),
		'type' => 'select',
		'options' => $provinces,
		'empty' => $empty,
		'help' => __('Your organization\'s province or state.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'country',
	'options' => [
		'label' => __('Country'),
		'type' => 'select',
		'options' => $countries,
		'empty' => $empty,
		'help' => __('Your organization\'s country.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'postal',
	'options' => [
		'label' => __('Postal Code'),
		'help' => __('Your organization\'s postal code.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'phone',
	'options' => [
		'label' => __('Phone'),
		'help' => __('Your organization\'s phone number.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'notice',
	'options' => [
		'type' => 'textarea',
		'label' => __('Announcement Text'),
		'help' => __('Optional announcement text to display at the top of the {0}.', __('Dashboard')),
		'class' => 'wysiwyg_advanced',
	],
]);
?>
	</fieldset>

	<fieldset>
		<legend><?= __('Location and Mapping') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'latitude',
	'options' => [
		'label' => __('Latitude'),
		'help' => __('Latitude in decimal degrees for game location (center of city). Used for calculating sunset times.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'longitude',
	'options' => [
		'label' => __('Longitude'),
		'help' => __('Longitude in decimal degrees for game location (center of city). Used for calculating sunset times.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'site',
	'name' => 'gmaps_key',
	'options' => [
		'label' => __('Google Maps API V3 key'),
		'help' => __('A key for the {0}. Required for rendering custom Google Maps.',
				$this->Html->link(__('Google Maps API V3'), 'https://code.google.com/apis/maps/documentation/javascript/tutorial.html#api_key')
		),
	],
]);
?>
	</fieldset>

	<fieldset>
		<legend><?= __('Dates') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'organization',
	'name' => 'first_day',
	'options' => [
		'label' => __('First Day'),
		'type' => 'select',
		'options' => [
			// Numbering matches the PHP date('N') format and ChronosInterface
			1 => __('Monday'),
			2 => __('Tuesday'),
			3 => __('Wednesday'),
			4 => __('Thursday'),
			5 => __('Friday'),
			6 => __('Saturday'),
			7 => __('Sunday'),
		],
		'empty' => $empty,
		'help' => __('First day of the week, for scheduling purposes.'),
	],
]);
?>
	</fieldset>
<?php
foreach ($plugin_elements as $element) {
	echo $this->element($element);
}

echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
