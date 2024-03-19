<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EventType $event_type
 * @var \App\Model\Entity\Event[] $events
 */

use App\Model\Entity\Event;
use Cake\Core\Configure;

$play_types = ['team', 'individual'];

$classes = collection($events)->extract(function (Event $event) { return "select_id_{$event->id}"; })->toArray();
$class = implode(' ', $classes);
?>
<tr class="select_ids <?= $class ?>"><th colspan="5"><h4><?= $event_type->name ?></h4></th></tr>
<?php
foreach ($events as $event):
	if (count($event->prices) === 1):
?>
<tr class="select_id_<?= $event->id ?>">
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
<tr class="select_id_<?= $event->id ?>">
	<td colspan="4"><h5><?= $this->Html->link($event->name, ['action' => 'view', 'event' => $event->id]) ?></h5></td>
	<td class="actions"><?= $this->element('Events/actions', ['event' => $event]) ?></td>
</tr>
<?php
		foreach ($event->prices as $price):
?>
<tr class="select_id_<?= $event->id ?>">
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
