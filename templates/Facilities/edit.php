<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Facility $facility
 * @var string[] $provinces
 * @var int $region
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Facilities'));
if ($facility->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($facility->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="facilities form">
	<?= $this->Form->create($facility, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $facility->isNew() ? __('Create Facility') : __('Edit Facility') ?></legend>
<?php
$this->start('facility_details');

echo $this->Form->i18nControls('name');
echo $this->Form->i18nControls('code');
echo $this->Form->control('sport', [
	'options' => Configure::read('options.sport'),
	'hide_single' => true,
	'empty' => __('Multi-sport facility'),
	'help' => __('Primary sport played at this facility.'),
]);
echo $this->Form->control('is_open');
echo $this->Form->control('location_street', [
	'label' => __('Address'),
]);
echo $this->Form->control('location_city', [
	'label' => __('City'),
	'default' => Configure::read('organization.city'),
]);
echo $this->Form->control('location_province', [
	'label' => __('Province'),
	'options' => $provinces,
	'default' => Configure::read('organization.province'),
]);
echo $this->Form->control('region_id', ['hide_single' => true, 'default' => $region]);
echo $this->Form->i18nControls('driving_directions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->i18nControls('parking_details', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->i18nControls('transit_directions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->i18nControls('biking_directions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->i18nControls('washrooms', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->i18nControls('public_instructions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->i18nControls('site_instructions', ['cols' => 70, 'class' => 'wysiwyg_advanced']);
echo $this->Form->i18nControls('sponsor', ['cols' => 70, 'class' => 'wysiwyg_advanced']);

$this->end();

$this->start('panels');

echo $this->Bootstrap->panel(
	$this->Bootstrap->panelHeading('Facility', __('Facility Details'), ['collapsed' => false]),
	$this->Bootstrap->panelContent('Facility', $this->fetch('facility_details'), ['collapsed' => false])
);

if (empty($facility->fields)) {
	echo $this->element('Facilities/field', ['index' => 0]);
} else {
	foreach ($facility->fields as $index => $field) {
		echo $this->element('Facilities/field', compact('index'));
	}
}

$this->end();
echo $this->Bootstrap->accordion($this->fetch('panels'));
?>
		<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Jquery->ajaxLink(
		$this->Html->iconImg('add_32.png', ['alt' => __('Add {0}', Configure::read('UI.field')), 'title' => __('Add {0}', Configure::read('UI.field'))]),
		[
			'url' => ['action' => 'add_field'],
			'disposition' => 'append',
			'selector' => '#accordion',
		],
		[
			'class' => 'icon',
			'escape' => false,
		]
	)
]);
?>
		</div>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
<?php
$links = [$this->Html->link(__('List Facilities'), ['action' => 'index'], ['class' => $this->Bootstrap->navPillLinkClasses()])];
if (!$facility->isNew()) {
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['facility' => $facility->id]],
		['alt' => __('Delete'), 'title' => __('Delete Facility')],
		['confirm' => __('Are you sure you want to delete this facility?')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Facility')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>
