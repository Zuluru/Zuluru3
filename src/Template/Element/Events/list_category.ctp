<?php
/**
 * @type $this \App\View\AppView
 * @type $category \App\Model\Entity\Category
 * @type $events \App\Model\Entity\Event[]
 */

use function App\Lib\no_null;

$sports = array_unique(collection($events)->extract('division.league.sport')->reject(function($sport) { return empty($sport); })->toArray());
$seasons = array_unique(collection($events)->extract('division.league.season')->reject(function($season) { return empty($season); })->toArray());
$ratios = array_unique(collection($events)->extract('division.ratio_rule')->reject(function($ratio) { return empty($ratio); })->toArray());
$types = array_unique(collection($events)->extract('event_type.name')->toArray());
$days = collection($events)->extract('division.days.{*}')->combine('id', 'name')->toArray();
ksort($days);
$competitions = array_unique(collection($events)->extract('level_of_play')->toArray());
$locations = no_null(array_unique(collection($events)->extract('location')->toArray()));

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

	$prices = array_unique(collection($event->prices)->extract('total')->toArray());
	sort($prices);
	echo $this->Html->tag('span', '', [
		'class' => "prices $class",
		'data-min-cost' => min($prices),
		'data-max-cost' => max($prices),
		'data-event' => $event->name,
		'data-link' => $this->Html->link(__('Register Now!'), ['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id]),
	]);
}
?>
			</td>
		</tr>
	</tbody>
</table>
</div>
