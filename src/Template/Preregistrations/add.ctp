<?php

use Cake\Core\Configure;

$this->Html->addCrumb(__('Preregistrations'));
$this->Html->addCrumb(__('Add'));
if (isset($event)) {
	if (count($affiliates) > 1) {
		$this->Html->addCrumb($event->affiliate->name);
	}
	$this->Html->addCrumb($event->name);
}
?>

<div class="preregistrations form">
<?php
if (!isset($event)):
	echo $this->Form->create(false, ['align' => 'horizontal']);
?>
	<fieldset>
		<legend><?= __('Add Preregistration') ?></legend>
<?php
	echo $this->Form->input('event', [
		'options' => $events,
		'empty' => __('Select one:'),
	]);
?>
	</fieldset>
	<?= $this->Form->button(__('Continue'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
<?php
else:
?>
	<fieldset>
		<legend><?php
		echo __('Add Preregistration') . ': ';
		if (count($affiliates) > 1) {
			echo "{$event->affiliate->name} ";
		}
		echo $event->name;
		?></legend>
		<?= $this->element('People/search_form', ['affiliate_id' => $event->affiliate_id, 'url' => ['event' => $event->id]]) ?>
		<div id="SearchResults" class="zuluru_pagination">

			<?= $this->element('People/search_results', ['extra_url' => [__('Add preregistration') => ['controller' => 'Preregistrations', 'action' => 'add', 'event' => $event->id]]]) ?>

		</div>
	</fieldset>
<?php
endif;
?>
</div>
<?php
if (isset($event)):
?>
<div class="actions columns">
	<?= $this->element('Events/actions', ['event' => $event, 'is_event_manager' => Configure::read('Perm.is_manager'), 'format' => 'list']) ?>
</div>
<?php
endif;
?>
