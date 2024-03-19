<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>
<p><?= __('The "{0}" page is used to update details of your facilities and {1}.',
	__('Edit Facility'),
	Configure::read('UI.fields')
) ?></p>
<p><?= __('The "{0}" page is essentially identical to this page.',
	__('Create Facility')
) ?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'facilities/edit',
	'topics' => [
		'name',
		'code',
		'is_open',
		'location_street' => 'Address',
		'driving_directions',
		'parking_details',
		'transit_directions',
		'biking_directions',
		'washrooms',
		'public_instructions',
		'site_instructions',
		'sponsor',
	],
]);
?>
<h3><?= Configure::read('UI.fields_cap') ?></h3>
<p><?= __('Details of {0} are also edited through the {1} page.',
	Configure::read('UI.field_cap'), __('Edit Facility')
) ?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'fields/edit',
	'topics' => [
		'is_open',
		'num',
	],
]);
