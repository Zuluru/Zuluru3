<?php
/**
 * @type \App\Model\Entity\Event $event
 * @type \App\Model\Entity\Registration[] $registrations
 */

use Cake\Core\Configure;
?>

<div class="actions columns">
	<ul class="nav nav-pills">
<?= $this->Html->tag('li', $this->Jquery->selectAll('#RegistrationList')) ?>
	</ul>
</div>
<div class="index">
	<p><?php
	echo $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]);
	?></p>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th></th>
					<th><?= __('Order ID') ?></th>
					<th><?= __('Person') ?></th>
					<th><?= __('Date') ?></th>
<?php
if (count($event->prices) > 1):
?>
					<th><?= __('Price Point') ?></th>
<?php
endif;
?>
					<th><?= __('Payment') ?></th>
					<th><?= __('Total Amount') ?></th>
					<th><?= __('Amount Paid') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
foreach ($registrations as $registration):
?>

				<tr>
					<td><?= $this->Form->input("registrations.{$registration->id}", [
						'label' => false,
						'type' => 'checkbox',
						'hiddenField' => false,
						'secure' => false,
					]) ?></td>
					<td><?php
						$order = sprintf(Configure::read('registration.order_id_format'), $registration->id);
						echo $this->Html->link($order, ['controller' => 'Registrations', 'action' => 'view', 'registration' => $registration->id]);
					?></td>
					<td><?= $this->element('People/block', ['person' => $registration->person]) ?></td>
					<td><?= $this->Time->dateTime($registration->created) ?></td>
<?php
	if (count($event->prices) > 1):
?>

					<td><?= $event->prices[$registration->price_id]->name ?></td>
<?php
	endif;
?>
					<td><?= $registration->payment ?></td>
					<td><?= $this->Number->currency($registration->total_amount) ?></td>
					<td><?= $this->Number->currency($registration->total_payment) ?></td>
					<td class="actions"><?= $this->element('Registrations/actions', ['registration' => $registration]) ?></td>
				</tr>
			</tbody>
<?php
endforeach;
?>

		</table>
	</div>
</div>
<nav class="paginator"><ul class="pagination">
	<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
</ul></nav>
