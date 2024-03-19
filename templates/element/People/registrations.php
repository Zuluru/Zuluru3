<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration[] $registrations
 * @var int[] $affiliates
 * @var string[] $years
 */

use App\Model\Entity\Registration;
use Cake\Core\Configure;

if (isset($registrations)):
?>
<div class="index">
	<p><?php
	echo $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]);
	?></p>

<?php
	echo $this->Selector->selector('Season', $this->Selector->extractOptionsUnsorted(
		$registrations,
		function (Registration $item) { return $item->event->division ? $item->event->division->league : null; },
		'season'
	));
	echo $this->Selector->selector('Type', $this->Selector->extractOptions(
		$registrations,
		function (Registration $item) { return $item->event->event_type; },
		'name', 'id'
	));
	echo $this->Selector->selector('Day', $this->Selector->extractOptions(
		$registrations,
		function (Registration $item) { return $item->event->division && !empty($item->event->division->days) ? $item->event->division->days : null; },
		'name', 'id'
	));

	$play_types = ['team', 'individual'];
?>

	<div class="table-responsive clear-float">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr class="paginator">
					<th><?= $this->Paginator->sort('Events.name', __('Event Name')) ?></th>
					<th><?= $this->Paginator->sort('id', __('Order ID')) ?></th>
					<th><?= $this->Paginator->sort('created', __('Date')) ?></th>
					<th><?= $this->Paginator->sort('payment') ?></th>
					<th><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$affiliate_id = null;
	$registrations = $registrations->toArray();
	foreach ($registrations as $registration):
		if (count($affiliates) > 1 && $registration->event->affiliate_id != $affiliate_id):
			$affiliate_id = $registration->event->affiliate_id;
?>
				<tr>
					<th colspan="5">
						<h3 class="affiliate"><?= h($registration->event->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
		endif;
?>
				<tr class="select_id_<?= $registration->id ?>">
					<td><?= $this->Html->link($registration->event->name, ['controller' => 'Events', 'action' => 'view', 'event' => $registration->event->id]) ?></td>
					<td><?php
					$order = sprintf(Configure::read('registration.order_id_format'), $registration->id);
					if ($this->Authorize->can('view', $registration)) {
						echo $this->Html->link($order, ['controller' => 'Registrations', 'action' => 'view', 'registration' => $registration->id]);
					} else {
						echo $order;
					}
					?></td>
					<td><?= $this->Time->date($registration->created) ?></td>
					<td><?= $registration->payment ?></td>
					<td class="actions"><?= $this->element('Registrations/actions', ['registration' => $registration]) ?></td>
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
endif;
