<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Facilities'));
if ($facility->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($facility->name));
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="facilities form">
	<?= $this->Form->create($facility, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $facility->isNew() ? __('Create Facility') : __('Edit Facility') ?></legend>
		<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="false">
			<div class="panel panel-default">
				<div class="panel-heading" role="tab" id="FacilityHeading">
					<h4 class="panel-title"><a role="button" class="accordion-toggle" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#FacilityDetails" aria-expanded="true" aria-controls="FacilityDetails">Facility Details</a></h4>
				</div>
				<div id="FacilityDetails" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="FacilityHeading">
					<div class="panel-body">
<?php
echo $this->Form->input('name');
echo $this->Form->input('code');
echo $this->Form->input('sport', [
	'options' => Configure::read('options.sport'),
	'hide_single' => true,
	'empty' => __('Multi-sport facility'),
	'help' => __('Primary sport played at this facility.'),
]);
echo $this->Form->input('is_open');
echo $this->Form->input('location_street', [
	'label' => __('Address'),
]);
echo $this->Form->input('location_city', [
	'label' => __('City'),
	'default' => Configure::read('organization.city'),
]);
echo $this->Form->input('location_province', [
	'label' => __('Province'),
	'options' => $provinces,
	'default' => Configure::read('organization.province'),
]);
echo $this->Form->input('region_id', ['hide_single' => true, 'default' => $region]);
echo $this->Form->input('driving_directions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->input('parking_details', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->input('transit_directions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->input('biking_directions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->input('washrooms', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->input('public_instructions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->input('site_instructions', ['cols' => 70, 'class' => 'wysiwyg_advanced']);
echo $this->Form->input('sponsor', ['cols' => 70, 'class' => 'wysiwyg_advanced']);
?>
					</div>
				</div>
			</div>
<?php
if (empty($facility->fields)) {
	echo $this->element('Facilities/field', ['index' => 0]);
} else {
	foreach ($facility->fields as $index => $field) {
		echo $this->element('Facilities/field', compact('index'));
	}
}
?>
		</div>
		<div class="actions columns">
			<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Jquery->ajaxLink($this->Html->iconImg('add_32.png', ['alt' => __('Add {0}', __(Configure::read('UI.field'))), 'title' => __('New {0}', __(Configure::read('UI.field')))]), [
	'url' => ['action' => 'add_field'],
	'disposition' => 'append',
	'selector' => '#accordion',
], [
	'class' => 'icon',
	'escape' => false,
]));
?>
			</ul>
		</div>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Facilities'), ['action' => 'index']));
if (!$facility->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'facility' => $facility->id],
		['alt' => __('Delete'), 'title' => __('Delete Facility')],
		['confirm' => __('Are you sure you want to delete this facility?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('New'), 'title' => __('New Facility')]));
}
?>
	</ul>
</div>
