<?php
use App\Controller\AppController;
use Cake\Core\Configure;

$this->Html->addCrumb(__('Registration Events'));
$this->Html->addCrumb(__('List'));
if (isset($year)) {
	$this->Html->addCrumb($year);
}

$my_affiliates = $this->UserCache->read('ManagedAffiliateIDs');
?>

<div class="events index">
	<h2><?= __('Registration Events List') ?><?= isset($year) ? ': ' . $year : '' ?></h2>
<?php
if (Configure::read('Perm.is_logged_in')) {
	echo $this->element('Registrations/relative_notice');
}

if (empty($events)):
?>
	<p class="warning-message"><?= __('There are no events currently available for registration. Please check back periodically for updates.') ?></p>
<?php
else:
	echo $this->element('Registrations/notice');
	if (!Configure::read('Perm.is_logged_in')) {
		echo $this->element('Events/not_logged_in');
	}

	$sports = array_unique(collection($events)->extract('division.league.sport')->reject(function($sport) { return empty($sport); })->toArray());
	echo $this->element('selector', ['title' => 'Sport', 'options' => $sports]);

	$seasons = array_unique(collection($events)->extract('division.league.season')->reject(function($season) { return empty($season); })->toArray());
	echo $this->element('selector', ['title' => 'Season', 'options' => $seasons]);

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
	$last_name = $affiliate_id = null;
	$cols = 4 + (!Configure::read('Perm.is_admin'));
	foreach ($events as $event):
		// Perhaps remove manager status, if we're looking at a different affiliate
		$is_event_manager = in_array($event->affiliate_id, $my_affiliates);

		if (count($affiliates) > 1 && $event->affiliate_id != $affiliate_id):
			$affiliate_id = $event->affiliate_id;
?>
			<tr>
				<th colspan="<?= $cols ?>"><h3 class="affiliate"><?= h($event->affiliate->name) ?></h3></th>
<?php
			if (Configure::read('Perm.is_admin')):
?>
				<th class="actions"><?php
				echo $this->Html->iconLink('edit_24.png',
					['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => $event->affiliate_id, 'return' => AppController::_return()],
					['alt' => __('Edit'), 'title' => __('Edit Affiliate')]);
				?></th>
<?php
			endif;
?>
			</tr>
<?php
		endif;

		if ($event->event_type->name != $last_name) {
			$classes = [];
			if (in_array($event->event_type->type, $play_types)) {
				$divisions = collection($events)->match(['event_type_id' => $event->event_type_id])->extract('division');

				$sports = array_unique($divisions->extract('league.sport')->toArray());
				$classes[] = $this->element('selector_classes', ['title' => 'Sport', 'options' => $sports]);

				$seasons = array_unique($divisions->extract('league.season')->toArray());
				$classes[] = $this->element('selector_classes', ['title' => 'Season', 'options' => $seasons]);

				$days = $divisions->extract('days.{*}')->combine('id', 'name')->toArray();
				ksort($days);
				$classes[] = $this->element('selector_classes', ['title' => 'Day', 'options' => $days]);
			}
			if (!empty($classes)) {
				$class = ' class="' . implode(' ', $classes) . '"';
			} else {
				$class = '';
			}
			echo "<tr$class><th colspan='5'><h4>{$event->event_type->name}</h4></th></tr>";
			$last_name = $event->event_type->name;
		}
		$classes = [];
		if (in_array($event->event_type->type, $play_types)) {
			if (!empty($event->division_id)) {
				$classes[] = $this->element('selector_classes', ['title' => 'Sport', 'options' => $event->division->league->sport]);
				$classes[] = $this->element('selector_classes', ['title' => 'Season', 'options' => $event->division->league->season]);
				$days = collection($event->division->days)->combine('id', 'name')->toArray();
				ksort($days);
				$classes[] = $this->element('selector_classes', ['title' => 'Day', 'options' => $days]);
			} else {
				$classes[] = $this->element('selector_classes', ['title' => 'Sport', 'options' => []]);
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
				<td class="actions"><?= $this->element('Events/actions', ['event' => $event, 'is_event_manager' => $is_event_manager]) ?></td>
			</tr>
<?php
		else:
?>
			<tr>
				<td colspan="4"><h5><?= $this->Html->link($event->name, ['action' => 'view', 'event' => $event->id]) ?></h5></td>
				<td class="actions"><?= $this->element('Events/actions', ['event' => $event, 'is_event_manager' => $is_event_manager]) ?></td>
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
					echo $this->Html->link(__('Register Now'), ['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id, 'price' => $price->id]);
				}
				if (Configure::read('Perm.is_admin') || $is_event_manager) {
					echo $this->Form->iconPostLink('delete_24.png',
						['controller' => 'Prices', 'action' => 'delete', 'price' => $price->id],
						['alt' => __('Delete'), 'title' => __('Delete')],
						['confirm' => __('Are you sure you want to delete this price?')]);
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
<?php
endif;
?>
</div>
<?php
if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
	foreach ($years as $year) {
		echo $this->Html->tag('li', $this->Html->link($year['year'], ['year' => $year['year']]));
	}
?>
	</ul>
</div>
<?php
endif;

echo $this->element('People/confirmation', ['fields' => ['height', 'shirt_size', 'year_started', 'skill_level']]);
