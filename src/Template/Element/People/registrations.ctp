<?php
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
	$types = collection($registrations)->extract('event.event_type')->combine('id', 'name')->toArray();
	ksort($types);
	echo $this->element('selector', ['title' => 'Type', 'options' => $types]);

	$seasons = array_unique(collection($registrations)
		->filter(function ($registration) {
			return !empty($registration->event->division_id);
		})->extract('event.division.league.season')->toArray());
	echo $this->element('selector', ['title' => 'Season', 'options' => array_intersect(array_keys(Configure::read('options.season')), $seasons)]);

	$days = collection($registrations)->extract('event.division.days.{*}')->combine('id', 'name')->toArray();
	ksort($days);
	echo $this->element('selector', ['title' => 'Day', 'options' => $days]);

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

		$classes = [];
		$classes[] = $this->element('selector_classes', ['title' => 'Type', 'options' => $registration->event->event_type->name]);
		if (in_array($registration->event->event_type->type, $play_types) && !empty($registration->event->division->id)) {
			$classes[] = $this->element('selector_classes', ['title' => 'Season', 'options' => $registration->event->division->league->season]);
			$days = collection($registration->event->division->days)->combine('id', 'name')->toArray();
			ksort($days);
			$classes[] = $this->element('selector_classes', ['title' => 'Day', 'options' => $days]);
		} else {
			$classes[] = $this->element('selector_classes', ['title' => 'Season', 'options' => []]);
			$classes[] = $this->element('selector_classes', ['title' => 'Day', 'options' => []]);
		}
?>
				<tr class="<?= implode(' ', $classes) ?>">
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
