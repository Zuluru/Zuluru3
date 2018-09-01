<?php
use Cake\Core\Configure;

$seasons = array_unique(collection($events)->extract('division.league.season')->toArray());
echo $this->element('selector', ['title' => 'Season', 'options' => array_intersect(array_keys(Configure::read('options.season')), $seasons)]);

$days = collection($events)->extract('division.days.{*}')->combine('id', 'name')->toArray();
ksort($days);
echo $this->element('selector', ['title' => 'Day', 'options' => $days]);

$play_types = ['team', 'individual'];
?>
<div class="table-responsive clear-float">
	<table class="table table-condensed">
		<thead>
			<tr>
				<th><?= __('Registration') ?></th>
				<th><?= __('Cost') ?></th>
				<th><?= __('Opens on') ?></th>
				<th><?= __('Closes on') ?></th>
				<th><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
$affiliate_id = null;
$my_affiliates = $this->UserCache->read('ManagedAffiliateIDs');
foreach ($events as $event):
	// Perhaps remove manager status, if we're looking at a different affiliate
	$is_event_manager = in_array($event->affiliate_id, $my_affiliates);

	if (count($affiliates) > 1 && $event->affiliate_id != $affiliate_id):
		$affiliate_id = $event->affiliate_id;
?>
			<tr>
				<th colspan="5"><h3 class="affiliate"><?= h($event->affiliate->name) ?></h3></th>
			</tr>
<?php
	endif;

	if (in_array($event->event_type->type, $play_types)) {
		if (!empty($event->division_id)) {
			$classes[] = $this->element('selector_classes', ['title' => 'Season', 'options' => $event->division->league->season]);
			$days = collection($event->division->days)->combine('id', 'name')->toArray();
			ksort($days);
			$classes[] = $this->element('selector_classes', ['title' => 'Day', 'options' => $days]);
		} else {
			$classes[] = $this->element('selector_classes', ['title' => 'Season', 'options' => []]);
			$classes[] = $this->element('selector_classes', ['title' => 'Day', 'options' => []]);
		}
	}
	if (!empty($classes)) {
		$class = ' class="' . implode(' ', $classes) . '"';
	} else {
		$class = '';
	}

	if (count($event->prices) == 1):
?>
			<tr>
				<td><?= $this->Html->link($event->name, ['action' => 'view', 'event' => $event->id]) ?></td>
				<td><?php
				$cost = $event->prices[0]->total;
				if ($cost > 0) {
					echo $this->Number->currency($cost);
				} else {
					echo $this->Html->tag('span', __('FREE'), ['class' => 'free']);
				}
				?></td>
				<td><?= $this->Time->datetime($event->prices[0]->open) ?></td>
				<td><?= $this->Time->datetime($event->prices[0]->close) ?></td>
				<td class="actions"><?= $this->element('Events/actions', ['event' => $event, 'is_event_manager' => $is_event_manager]) ?></td>
			</tr>
<?php
	else:
?>
			<tr>
				<td colspan="4"><h4><?= $this->Html->link($event->name, ['action' => 'view', 'event' => $event->id]) ?></h4></td>
				<td class="actions"><?= $this->element('Events/actions', ['event' => $event, 'is_event_manager' => $is_event_manager]) ?></td>
			</tr>
<?php
		foreach ($event->prices as $price):
?>
			<tr>
				<td class="price-point"><?= $this->Html->link($price->name, ['action' => 'view', 'event' => $event->id]) ?></td>
				<td><?php
				$cost = $price->cost + $price->tax1 + $price->tax2;
				if ($cost > 0) {
					echo $this->Number->currency($cost);
				} else {
					echo $this->Html->tag('span', __('FREE'), ['class' => 'free']);
				}
				?></td>
				<td><?= $this->Time->datetime($price->open) ?></td>
				<td><?= $this->Time->datetime($price->close) ?></td>
				<td class="actions"><?php
				echo $this->Html->iconLink('view_24.png',
					['action' => 'view', 'event' => $event->id],
					['alt' => __('View'), 'title' => __('View')]);
				if (Configure::read('registration.register_now')) {
					echo $this->Html->link(__('Register Now'), ['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id, 'price' => $price->id]);
				}
				?></td>
			</tr>
<?php
		endforeach;
	endif;
endforeach;
?>

		</tbody>
	</table>
</div>
