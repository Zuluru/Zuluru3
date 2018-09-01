<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Registrations'));
$this->Html->addCrumb($event->name);
$this->Html->addCrumb(__('List'));
?>

<div class="registrations index">
	<h2><?= __('Registration List') . ': ' . $event->name ?></h2>

	<div id="RegistrationList" class="zuluru_pagination">

<?= $this->element('Registrations/full_list') ?>

	</div>
</div>
<div class="actions columns">
	<?= $this->element('Events/actions', ['event' => $event, 'is_event_manager' => Configure::read('Perm.is_manager'), 'format' => 'list']) ?>
</div>
