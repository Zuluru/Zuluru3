<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration[] $registrations
 * @var string[] $affiliates
 * @var int $affiliate
 * @var string $start_date
 * @var string $end_date
 */

use Cake\Core\Configure;

if (isset($registrations)):
?>
<div class="index">
	<p><?php
	$this->Paginator->options([
		'url' => ['?' => compact('start_date', 'end_date')],
	]);
	echo $this->Paginator->counter(
		__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	);
	?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr class="paginator">
					<th><?= __('Created Date') ?></th>
					<th><?= __('Order ID') ?></th>
					<th><?= __('Event ID') ?></th>
					<th><?= __('Event') ?></th>
					<th><?= __('Price Point') ?></th>
					<th><?= __('Person ID') ?></th>
					<th><?= Configure::read('profile.legal_name') ? __('Preferred Name') : __('First Name') ?></th>
					<th><?= __('Last Name') ?></th>
					<th><?= __('Payment') ?></th>
					<th><?= __('Total Amount') ?></th>
					<th><?= __('Amount Paid') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$order_fmt = Configure::read('registration.order_id_format');
	$affiliate_id = null;
	foreach ($registrations as $registration):
		if (count($affiliates) > 1 && $registration->event->affiliate_id != $affiliate_id):
			$affiliate_id = $registration->event->affiliate_id;
			?>

				<tr>
					<th colspan="11">
						<h3 class="affiliate"><?= h($registration->event->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
		endif;
?>
				<tr>
					<td><?= $this->Time->datetime($registration->created) ?></td>
					<td><?= $this->Html->link(sprintf($order_fmt, $registration->id),
						['controller' => 'Registrations', 'action' => 'view', '?' => ['registration' => $registration->id]]) ?></td>
					<td><?= $registration->event->id ?></td>
					<td><?= $this->Html->link($registration->event->name,
						['controller' => 'Events', 'action' => 'view', '?' => ['event' => $registration->event->id]]) ?></td>
					<td><?= $registration->price->name ?></td>
					<td><?= $registration->person->id ?></td>
					<td><?= $this->element('People/block', ['person' => $registration->person, 'display_field' => 'first_name']) ?></td>
					<td><?= $this->element('People/block', ['person' => $registration->person, 'display_field' => 'last_name']) ?></td>
					<td><?= $registration->payment ?></td>
					<td><?= $this->Number->currency($registration->total_amount) ?></td>
					<td><?= $this->Number->currency($registration->total_payment) ?></td>
				</tr>
<?php
	endforeach;
?>

			</tbody>
		</table>
	</div>
</div>
<nav class="paginator"><ul class="pagination">
	<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
</ul></nav>

<?php
	if ($this->getRequest()->is('post')):
?>

	<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Html->link(__('Download Registration Report'),
		['action' => 'report', '?' => ['affiliate' => $affiliate, 'start_date' => $start_date, 'end_date' => $end_date], '_ext' => 'csv'],
		['class' => $this->Bootstrap->navPillLinkClasses()]
	),
]);
?>
	</div>

<?php
	endif;
endif;
