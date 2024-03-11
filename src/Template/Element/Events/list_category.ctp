<?php
/**
 * @type $this \App\View\AppView
 * @type $category \App\Model\Entity\Category
 * @type $events \App\Model\Entity\Event[]
 */

use App\Authorization\ContextResource;
use App\Model\Entity\Event;
use Cake\Core\Configure;
use function App\Lib\no_blank;

$sports = $this->Selector->extractOptions(
	$events,
	function (Event $item) { return $item->division ? $item->division->league : null; },
	'sport'
);
$seasons = $this->Selector->extractOptionsUnsorted(
	$events,
	function (Event $item) { return $item->division ? $item->division->league : null; },
	'season'
);
$ratios = $this->Selector->extractOptions(
	$events,
	function (Event $item) { return $item->division ?: null; },
	'ratio_rule'
);
$types = $this->Selector->extractOptions(
	$events,
	function (Event $item) { return $item->event_type; },
	'name', 'id'
);
$days = $this->Selector->extractOptions(
	$events,
	function (Event $item) { return $item->division && !empty($item->division->days) ? $item->division->days : null; },
	'name', 'id'
);
$competitions = $this->Selector->extractOptions(
	$events,
	null,
	'level_of_play'
);
$locations = $this->Selector->extractOptions(
	$events,
	null,
	'location'
);

$classes = collection($events)->extract(function (Event $event) { return "select_id_{$event->id}"; })->toArray();
$class = implode(' ', $classes);
?>
<div class="table-responsive clear-float <?= $class ?>">
<h3><?php
echo $category->name;
if (!empty($category->description)) {
	$div_id = "Category{$category->id}Description";
	echo $this->Html->popup(__('More info'), $category->name, $div_id, $category->description, ['style' => 'font-size: small; margin-left: 5px;']);
}
?></h3>
<table class="table table-condensed">
	<thead>
		<tr>
			<th><?= $this->Form->button(__('Reset'), ['class' => 'reset', 'style' => 'padding-top: 1px; padding-bottom: 1px;', 'onclick' => 'resetRadio(zjQuery(this)); return false;']) ?></th>
<?php
if (count($sports) > 1):
?>
			<th><?= __('Sport') ?></th>
<?php
endif;

if (count($seasons) > 1):
?>
			<th><?= __('Season') ?></th>
<?php
endif;

if (count($ratios) > 1):
?>
			<th><?= __('Gender Ratio') ?></th>
<?php
endif;

if (count($types) > 1):
?>
			<th><?= __('Type') ?></th>
<?php
endif;

if (count($days) > 1):
?>
			<th><?= __('Day of Week') ?></th>
<?php
endif;

if (count($competitions) > 1):
?>
			<th><?= __('Competition') ?></th>
<?php
endif;

if (count($locations) > 1):
?>
			<th><?= __('Location') ?></th>
<?php
endif;
?>
			<th style="width: 20%;"><?= __('Price') ?> <?= __('(taxes included)') ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?= $category->image_url ? $this->Html->image($category->image_url) : '' ?></td>
<?php
if (count($sports) > 1):
?>
			<td><?= $this->Selector->radioSelector($category->slug, 'Sport', $sports) ?></td>
<?php
endif;

if (count($seasons) > 1):
?>
			<td><?= $this->Selector->radioSelector($category->slug, 'Season', $seasons) ?></td>
<?php
endif;

if (count($ratios) > 1):
?>
			<td><?= $this->Selector->radioSelector($category->slug, 'Ratio', $ratios) ?></td>
<?php
endif;

if (count($types) > 1):
?>
			<td><?= $this->Selector->radioSelector($category->slug, 'Type', $types) ?></td>
<?php
endif;

if (count($days) > 1):
?>
			<td><?= $this->Selector->radioSelector($category->slug, 'Day', $days) ?></td>
<?php
endif;

if (count($competitions) > 1):
?>
			<td><?= $this->Selector->radioSelector($category->slug, 'Competition', $competitions) ?></td>
<?php
endif;

if (count($locations) > 1):
?>
			<td><?= $this->Selector->radioSelector($category->slug, 'Location', $locations) ?></td>
<?php
endif;
?>
			<td class="final"><span class="final"></span>
<?php
$events_by_class = [];
foreach ($events as $event) {
	$day = reset($event->division->days)->name;
	$id_class = implode(' ', [
		"sport:{$event->division->league->sport}",
		"season:{$event->division->league->season}",
		"ratio:{$event->division->ratio_rule}",
		"type:{$event->event_type->name}",
		"day:{$day}",
		"competition:{$event->level_of_play}",
		"location:{$event->location}",
	]);

	if (!array_key_exists($id_class, $events_by_class)) {
		$events_by_class[$id_class] = [];
	}
	$events_by_class[$id_class][] = $event;

	$prices = array_unique(collection($event->prices)->extract('total')->toArray());
	sort($prices);

	if ($this->Authorize->getIdentity()) {
		$resource = new ContextResource($event, ['strict' => false]);
		$can = $this->Authorize->can('register', $resource);
		if ($can) {
			$link = $this->Html->link(__('Register Now!'),
				 ['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id],
				['class' => 'btn btn-primary']
			);
		} else if ($resource->context('notices')) {
			$link = $this->element('messages', ['messages' => $resource->context('notices')]);
		}
	} else {
		$link = $this->Html->link(__('View Event'), ['controller' => 'Events', 'action' => 'view', 'event' => $event->id]);
	}

	echo $this->Html->tag('span', '', [
		'class' => "prices option_id_{$event->id}",
		'data-min-cost' => min($prices),
		'data-max-cost' => max($prices),
		'data-event' => $event->name,
		'data-link' => $link,
	]);
}
?>
			</td>
		</tr>
	</tbody>
</table>

<?php
// Assuming that if we can edit one event, we're an admin that can edit them all
if ($this->Authorize->can('edit', $event)):
	$show_warning = true;
	foreach ($events_by_class as $class => $class_events):
		if (count($class_events) > 1):
			if ($show_warning):
?>
<h4 class="warning-message"><?= __('Admin warning') ?></h4>
<p class="warning-message"><?= __('The following events were indistinguishable to the system and will result in users not being able to select any of them.') ?></p>
<?php
				$show_warning = false;
			endif;
?>
<p><?= __('The code for these events was {0}', $class) ?></p>
<ul>
<?php
			/** @var \App\Model\Entity\Event $event */
			foreach ($class_events as $event):
				$links = [
					$this->Html->link(__('View Event'), ['action' => 'view', 'event' => $event->id]),
					$this->Html->link(__('Edit Event'), ['action' => 'edit', 'event' => $event->id]),
				];
				if ($event->division_id) {
					$links[] = $this->Html->link(__('View Division'), ['controller' => 'Divisions', 'action' => 'view', 'division' => $event->division_id]);
					$links[] = $this->Html->link(__('Edit Division'), ['controller' => 'Divisions', 'action' => 'edit', 'division' => $event->division_id]);
					$links[] = $this->Html->link(__('Game Slots'), ['controller' => 'Divisions', 'action' => 'slots', 'division' => $event->division_id]);
				}
?>
	<li><?= __('{0}: {1}', $event->name, implode(' / ', $links)) ?></li>
<?php
			endforeach;
?>
</ul>
<?php
		endif;
	endforeach;
endif;
?>
</div>
