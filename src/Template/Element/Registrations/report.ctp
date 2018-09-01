<?php
use Cake\Core\Configure;

if (isset($registrations)):
?>
<div class="index">
	<p><?php
	$this->Paginator->options([
		'url' => compact('start_date', 'end_date'),
	]);
	echo $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]);
	?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr class="paginator">
					<th><?= $this->Paginator->sort('created', __('Created Date')) ?></th>
					<th><?= $this->Paginator->sort('id', __('Order ID')) ?></th>
					<th><?= $this->Paginator->sort('event_id', __('Event ID')) ?></th>
					<th><?= __('Event') ?></th>
					<th><?= __('Price Point') ?></th>
					<th><?= $this->Paginator->sort('person_id', __('Person ID')) ?></th>
					<th><?= __('First Name') ?></th>
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
						['controller' => 'Registrations', 'action' => 'view', 'registration' => $registration->id]) ?></td>
					<td><?= $registration->event->id ?></td>
					<td><?= $this->Html->link($registration->event->name,
						['controller' => 'Events', 'action' => 'view', 'event' => $registration->event->id]) ?></td>
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
	if ($this->request->is('post')):
?>

	<div class="actions columns">
		<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Download Registration Report'), ['action' => 'report', 'affiliate' => $affiliate, 'start_date' => $start_date, 'end_date' => $end_date, '_ext' => 'csv']));
?>
		</ul>
	</div>

<?php
	endif;
endif;
