<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[] $events
 * @var \App\Model\Entity\Category $category
 * @var int[] $affiliates
 */

// Combine events by categories
if (!isset($category)) {
	$categories = $uncategorized = [];
	foreach ($events as $event) {
		if ($event->division_id && !empty($event->division->league->categories)) {
			foreach ($event->division->league->categories as $event_category) {
				if (!array_key_exists($event_category->id, $categories)) {
					$categories[$event_category->id] = ['category' => $event_category, 'events' => []];
				}

				$categories[$event_category->id]['events'][] = $event;
			}
		} else {
			if (!array_key_exists($event->event_type_id, $uncategorized)) {
				$uncategorized[$event->event_type_id] = [];
			}
			$uncategorized[$event->event_type_id][] = $event;
		}
	}

	ksort($uncategorized);
} else {
	// Keep things in the same overall structure regardless of whether there was a single category specified
	$categories = [$category->id => ['category' => $category, 'events' => $events]];
}

if (!empty($uncategorized)):
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
	foreach ($uncategorized as $type_events) {
		echo $this->element('Events/list_type', ['event_type' => $type_events[0]->event_type, 'events' => $type_events]);
	}
?>

		</tbody>
	</table>
</div>
<?php
endif;

if (!empty($categories)) {
	uasort($categories, function ($a, $b) {
		return $a['category']->sort <=> $b['category']->sort;
	});
	foreach ($categories as $details) {
		echo $this->element('Events/list_category', ['category' => $details['category'], 'events' => $details['events']]);
	}
}
