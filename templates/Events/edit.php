<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Events'));
if ($event->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($event->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="events form">
	<?= $this->Form->create($event, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $event->isNew() ? __('Create Event') : __('Edit Event') ?></legend>
<?php
$this->start('event_details');

echo $this->Form->i18nControls('name', [
	'size' => 70,
	'help' => __('Full name of this registration event.'),
]);
if ($event->isNew()) {
	echo $this->Form->control('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
} else {
	echo $this->Form->hidden('affiliate_id');
}
echo $this->Form->i18nControls('description', [
	'cols' => 70,
	'rows' => 5,
	'help' => __('Complete description of the event, HTML is allowed.'),
	'class' => 'wysiwyg_advanced',
]);
echo $this->Jquery->ajaxInput('event_type_id', [
	'selector' => '#EventTypeFields',
	'url' => ['action' => 'event_type_fields'],
], [
	'empty' => '---',
	'help' => __('Note that any team type will result in team records being created. If you don\'t want this, then use the appropriate individual type.'),
]);
echo $this->Form->control('open_cap', [
	'help' => __('-1 for no limit.'),
]);
echo $this->Form->control('women_cap', [
	'help' => __('-1 for no limit, -2 to use open cap as combined limit.'),
]);
// TODOBOOTSTRAP: Better alignment for checkboxes. Also, investigate http://www.bootstraptoggle.com/
echo $this->Form->control('multiple', [
	'label' => __('Allow multiple registrations'),
	'help' => __('Can a single user register for this event multiple times?'),
]);
echo $this->Form->control('questionnaire_id', [
	'empty' => 'None',
]);
?>
						<div id="EventTypeFields">
<?php
if (isset($event_obj)) {
	echo $this->element('Registrations/configuration/' . $event_obj->configurationFieldsElement());
}
?>
						</div>
<?php
$this->end();

$this->start('panels');

echo $this->Accordion->panel(
	$this->Accordion->panelHeading('Event', __('Event Details'), ['collapsed' => false]),
	$this->Accordion->panelContent('Event', $this->fetch('event_details'), ['collapsed' => false])
);

if (empty($event->prices)) {
	echo $this->element('Events/price', ['index' => 0]);
} else {
	foreach ($event->prices as $index => $price) {
		echo $this->element('Events/price', compact('index'));
	}
}

$this->end();
echo $this->Accordion->accordion($this->fetch('panels'));
?>
		<div class="actions columns">
			<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Jquery->ajaxLink($this->Html->iconImg('add_32.png', ['alt' => __('Add Price Point'), 'title' => __('Add Price Point')]), [
	'url' => ['action' => 'add_price'],
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
<?php
if (!$event->isNew()):
?>
<div class="actions columns">
	<?= $this->element('Events/actions', ['event' => $event, 'format' => 'list']) ?>
</div>
<?php
endif;
?>
