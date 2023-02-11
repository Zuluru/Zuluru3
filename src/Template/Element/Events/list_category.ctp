<?php
/**
 * @type $this \App\View\AppView
 * @type $category \App\Model\Entity\Category
 * @type $events \App\Model\Entity\Event[]
 */

use App\Authorization\ContextResource;
use Cake\Core\Configure;
use function App\Lib\no_blank;

$sports = array_unique(collection($events)->extract('division.league.sport')->reject(function($sport) { return empty($sport); })->toArray());
$seasons = array_unique(collection($events)->extract('division.league.season')->reject(function($season) { return empty($season); })->toArray());
$ratios = array_unique(collection($events)->extract('division.ratio_rule')->reject(function($ratio) { return empty($ratio); })->toArray());
$types = array_unique(collection($events)->extract('event_type.name')->toArray());
$days = collection($events)->extract('division.days.{*}')->combine('id', 'name')->toArray();
ksort($days);
$competitions = no_blank(array_unique(collection($events)->extract('level_of_play')->toArray()));
$locations = no_blank(array_unique(collection($events)->extract('location')->toArray()));

$classes = [
	'table-responsive', 'clear-float',
	$this->element('selector_classes', ['title' => 'Sport', 'options' => $sports]),
	$this->element('selector_classes', ['title' => 'Season', 'options' => $seasons]),
	$this->element('selector_classes', ['title' => 'Ratio', 'options' => $ratios]),
	$this->element('selector_classes', ['title' => 'Type', 'options' => $types]),
	$this->element('selector_classes', ['title' => 'Day', 'options' => $days]),
	$this->element('selector_classes', ['title' => 'Competition', 'options' => $competitions]),
	$this->element('selector_classes', ['title' => 'Location', 'options' => $locations]),
];
$class = implode(' ', $classes);
?>
<div class="<?= $class ?>">
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
			<th style="width: 20%;"><?= __('Price') ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?= $category->image_url ? $this->Html->image($category->image_url) : '' ?></td>
<?php
if (count($sports) > 1):
?>
			<td><?= $this->element('radio_selector', ['slug' => $category->slug, 'title' => 'Sport', 'options' => $sports]) ?></td>
<?php
endif;

if (count($seasons) > 1):
?>
			<td><?= $this->element('radio_selector', ['slug' => $category->slug, 'title' => 'Season', 'options' => $seasons]) ?></td>
<?php
endif;

if (count($ratios) > 1):
?>
			<td><?= $this->element('radio_selector', ['slug' => $category->slug, 'title' => 'Ratio', 'options' => $ratios]) ?></td>
<?php
endif;

if (count($types) > 1):
?>
			<td><?= $this->element('radio_selector', ['slug' => $category->slug, 'title' => 'Type', 'options' => $types]) ?></td>
<?php
endif;

if (count($days) > 1):
?>
			<td><?= $this->element('radio_selector', ['slug' => $category->slug, 'title' => 'Day', 'options' => $days]) ?></td>
<?php
endif;

if (count($competitions) > 1):
?>
			<td><?= $this->element('radio_selector', ['slug' => $category->slug, 'title' => 'Competition', 'options' => $competitions]) ?></td>
<?php
endif;

if (count($locations) > 1):
?>
			<td><?= $this->element('radio_selector', ['slug' => $category->slug, 'title' => 'Location', 'options' => $locations]) ?></td>
<?php
endif;
?>
			<td class="final"><span class="final"></span>
<?php
$events_by_class = [];
foreach ($events as $event) {
	$days = collection($event->division->days)->combine('id', 'name')->toArray();
	ksort($days);
	$classes = [
		$this->element('selector_classes', ['title' => 'Sport', 'options' => $event->division->league->sport]),
		$this->element('selector_classes', ['title' => 'Season', 'options' => $event->division->league->season]),
		$this->element('selector_classes', ['title' => 'Ratio', 'options' => $event->division->ratio_rule]),
		$this->element('selector_classes', ['title' => 'Type', 'options' => $event->event_type->name]),
		$this->element('selector_classes', ['title' => 'Day', 'options' => $days]),
		$this->element('selector_classes', ['title' => 'Competition', 'options' => $event->level_of_play]),
		$this->element('selector_classes', ['title' => 'Location', 'options' => $event->location]),
	];
	$class = implode(' ', $classes);

	$id_class = implode(' ', collection($classes)->extract(function (string $class) {
		if (strpos($class, ' ') === false) {
			return '';
		}
		[$type, $specific] = explode(' ', $class);
		return $specific;
	})->toArray());

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
			$link = $this->Html->link(__('Register Now!'), ['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id]);
		} else if ($resource->context('notices')) {
			$link = $this->element('messages', ['messages' => $resource->context('notices')]);
		}
	} else {
		$link = $this->Html->link(__('View Event'), ['controller' => 'Events', 'action' => 'view', 'event' => $event->id]);
	}

	echo $this->Html->tag('span', '', [
		'class' => "prices $class",
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
