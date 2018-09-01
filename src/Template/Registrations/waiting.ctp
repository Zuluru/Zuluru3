<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Registrations'));
$this->Html->addCrumb(__('Waiting List'));
$this->Html->addCrumb($event->name);
?>

<div class="registrations index">
	<h2><?= __('Waiting List') . ': ' . $event->name ?></h2>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Registration') ?></th>
					<th><?= __('Person') ?></th>
					<th><?= __('Date') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$order_id_format = Configure::read('registration.order_id_format');
foreach ($event->registrations as $registration):
	$order_id = sprintf($order_id_format, $registration->id);
?>
				<tr>
					<td><?= Configure::read('Perm.is_manager') ? $this->Html->link($order_id, ['action' => 'view', 'registration' => $registration->id]) : $order_id ?></td>
					<td><?= $this->element('People/block', ['person' => $registration->person]) ?></td>
					<td><?= $this->Time->datetime($registration->created) ?></td>
					<td class="actions"><?= $this->element('Registrations/actions', ['registration' => $registration]) ?></td>
				</tr>
<?php
	if (Configure::read('Perm.is_manager') && !empty($registration->notes)):
?>

				<tr>
					<td></td>
					<td colspan="4"><?= $registration->notes ?></td>
				</tr>
<?php
	endif;
endforeach;
?>

			</tbody>
		</table>
	</div>
</div>
<div class="actions columns">
	<?= $this->element('Events/actions', ['event' => $event, 'is_event_manager' => Configure::read('Perm.is_manager'), 'format' => 'list']) ?>
</div>
