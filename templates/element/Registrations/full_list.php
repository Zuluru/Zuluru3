<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration[] $registrations
 */

use Cake\Core\Configure;
?>

<div class="index">
	<p><?php
	echo $this->Paginator->counter(
		__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	);
	?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('id', __('Order ID')) ?></th>
					<th><?= __('Person') ?></th>
					<th><?= $this->Paginator->sort('created', __('Date')) ?></th>
<?php
if (count($event->prices) > 1):
?>
					<th><?= __('Price Point') ?></th>
<?php
endif;
?>
					<th><?= $this->Paginator->sort('payment') ?></th>
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
					<td><?php
						$order = sprintf(Configure::read('registration.order_id_format'), $registration->id);
						if ($this->Authorize->can('view', $registration)) {
							echo $this->Html->link($order, ['controller' => 'Registrations', 'action' => 'view', '?' => ['registration' => $registration->id]]);
						} else {
							echo $order;
						}
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
