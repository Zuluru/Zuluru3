<?php
/**
 * @type $this \App\View\AppView
 * @type $event_type \App\Model\Entity\EventType
 * @type $events \App\Model\Entity\Event[]
 */

use Cake\Core\Configure;

$play_types = ['team', 'individual'];

$classes = [];
if (in_array($event_type->type, $play_types)) {
	$divisions = collection($events)->extract('division');

	$sports = array_unique($divisions->extract('league.sport')->toArray());
	$classes[] = $this->element('selector_classes', ['title' => 'Sport', 'options' => $sports]);

	$seasons = array_unique($divisions->extract('league.season')->reject(function ($season) { return empty($season); })->toArray());
	$classes[] = $this->element('selector_classes', ['title' => 'Season', 'options' => $seasons]);

	$classes[] = $this->element('selector_classes', ['title' => 'Type', 'options' => $event_type->name]);

	$days = $divisions->extract('days.{*}')->combine('id', 'name')->toArray();
	ksort($days);
	$classes[] = $this->element('selector_classes', ['title' => 'Day', 'options' => $days]);

	$competitions = array_unique(collection($events)->extract('level_of_play')->toArray());
	$classes[] = $this->element('selector_classes', ['title' => 'Competition', 'options' => $competitions]);

	$locations = array_unique(collection($events)->extract('location')->toArray());
	$classes[] = $this->element('selector_classes', ['title' => 'Location', 'options' => $locations]);
}
if (!empty($classes)) {
	$class = ' class="' . implode(' ', $classes) . '"';
} else {
	$class = '';
}
echo "<tr$class><th colspan='5'><h4>{$event_type->name}</h4></th></tr>";

foreach ($events as $event):
	$classes = [];
	if (in_array($event_type->type, $play_types)) {
		if (!empty($event->division_id)) {
			$classes[] = $this->element('selector_classes', ['title' => 'Sport', 'options' => $event->division->league->sport]);
			$classes[] = $this->element('selector_classes', ['title' => 'Season', 'options' => $event->division->league->season]);
			$classes[] = $this->element('selector_classes', ['title' => 'Type', 'options' => $event_type->name]);
			$days = collection($event->division->days)->combine('id', 'name')->toArray();
			ksort($days);
			$classes[] = $this->element('selector_classes', ['title' => 'Day', 'options' => $days]);
			$classes[] = $this->element('selector_classes', ['title' => 'Competition', 'options' => $event->level_of_play]);
			$classes[] = $this->element('selector_classes', ['title' => 'Location', 'options' => $event->location]);
		} else {
			$classes[] = $this->element('selector_classes', ['title' => 'Sport', 'options' => []]);
			$classes[] = $this->element('selector_classes', ['title' => 'Season', 'options' => []]);
			$classes[] = $this->element('selector_classes', ['title' => 'Type', 'options' => $event_type->name]);
			$classes[] = $this->element('selector_classes', ['title' => 'Day', 'options' => []]);
			$classes[] = $this->element('selector_classes', ['title' => 'Competition', 'options' => $event->level_of_play]);
			$classes[] = $this->element('selector_classes', ['title' => 'Location', 'options' => []]);
		}
	}
	if (!empty($classes)) {
		$class = ' class="' . implode(' ', $classes) . '"';
	} else {
		$class = '';
	}

	if (count($event->prices) === 1):
?>
<tr<?= $class ?>>
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
	<td class="actions"><?= $this->element('Events/actions', ['event' => $event]) ?></td>
</tr>
<?php
	else:
?>
<tr>
	<td colspan="4"><h5><?= $this->Html->link($event->name, ['action' => 'view', 'event' => $event->id]) ?></h5></td>
	<td class="actions"><?= $this->element('Events/actions', ['event' => $event]) ?></td>
</tr>
<?php
		foreach ($event->prices as $price):
?>
<tr>
	<td class="price-point"><?= $this->Html->link($price->name, ['action' => 'view', 'event' => $event->id]) ?></td>
	<td><?php
	$cost = $price->total;
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
		echo $this->Html->link(__('Register Now!'), ['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id, 'price' => $price->id]);
	}
	if ($this->Authorize->can('delete', $price)) {
		echo $this->Form->iconPostLink('delete_24.png',
			['controller' => 'Prices', 'action' => 'delete', 'price' => $price->id],
			['alt' => __('Delete'), 'title' => __('Delete')],
			['confirm' => __('Are you sure you want to delete this price?')]);
	}
	if ($this->Authorize->can('refund', $event)) {
		echo $this->Html->link(__('Bulk Refunds'), ['controller' => 'Events', 'action' => 'refund', 'event' => $event->id, 'price' => $price->id]);
	}
	?></td>
</tr>
<?php
		endforeach;
	endif;
endforeach;
