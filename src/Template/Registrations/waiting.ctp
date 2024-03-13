<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

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
					<td><?= $this->Authorize->can('view', $registration) ? $this->Html->link($order_id, ['action' => 'view', 'registration' => $registration->id]) : $order_id ?></td>
					<td><?= $this->element('People/block', ['person' => $registration->person]) ?></td>
					<td><?= $this->Time->datetime($registration->created) ?></td>
					<td class="actions"><?= $this->element('Registrations/actions', ['registration' => $registration]) ?></td>
				</tr>
<?php
	if (!empty($registration->notes) && $this->Authorize->can('view', $registration)):
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
	<?= $this->element('Events/actions', ['event' => $event, 'format' => 'list']) ?>
</div>
