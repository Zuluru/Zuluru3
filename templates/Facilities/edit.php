<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Facility $facility
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
		<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="false">
			<div class="panel panel-default">
				<div class="panel-heading" role="tab" id="FacilityHeading">
					<h4 class="panel-title"><a role="button" class="accordion-toggle" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#FacilityDetails" aria-expanded="true" aria-controls="FacilityDetails">Facility Details</a></h4>
				</div>
				<div id="FacilityDetails" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="FacilityHeading">
					<div class="panel-body">
<?php
echo $this->Form->control('name');
echo $this->Form->control('code');
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
echo $this->Form->control('driving_directions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->control('parking_details', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->control('transit_directions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->control('biking_directions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->control('washrooms', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->control('public_instructions', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->control('site_instructions', ['cols' => 70, 'class' => 'wysiwyg_advanced']);
echo $this->Form->control('sponsor', ['cols' => 70, 'class' => 'wysiwyg_advanced']);
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
echo $this->Html->tag('li', $this->Jquery->ajaxLink($this->Html->iconImg('add_32.png', ['alt' => __('Add {0}', Configure::read('UI.field')), 'title' => __('Add {0}', Configure::read('UI.field'))]), [
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
		['action' => 'delete', '?' => ['facility' => $facility->id]],
		['alt' => __('Delete'), 'title' => __('Delete Facility')],
		['confirm' => __('Are you sure you want to delete this facility?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Facility')]));
}
?>
	</ul>
</div>
