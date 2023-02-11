<?php
/**
 * @type $this \App\View\AppView
 * @type $events \App\Model\Entity\Event[]
 * @type $category \App\Model\Entity\Category
 * @type $affiliates int[]
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Registration Events'));
$this->Html->addCrumb(__('List'));
?>

<div class="events index">
<?php
if (isset($category)):
?>
	<h2><?= $category->image_url ? $this->Html->image($category->image_url) : '' ?><?= $category->name ?></h2>
<?php
else:
?>
	<h2><?= __('Registration Events List') ?></h2>
<?php
endif;

if ($this->Identity->isLoggedIn()) {
	echo $this->element('Registrations/relative_notice');
}

echo $this->element('Registrations/notice');
if (!$this->Identity->isLoggedIn()) {
	echo $this->element('Events/not_logged_in');
}

echo $this->element('Events/selectors', compact('events'));

foreach ($events as $affiliate_id => $affiliate_events):
	if (count($affiliates) > 1):
?>
	<h3 class="affiliate"><?= h($affiliate_events[0]->affiliate->name) ?></h3>
<?php
	endif;

	// Combine events by categories
	if (!isset($category)) {
		$categories = $uncategorized = [];
		foreach ($affiliate_events as $event) {
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
		$categories = [$category->id => ['category' => $category, 'events' => $affiliate_events]];
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

endforeach;
?>
</div>
<?php
echo $this->element('People/confirmation', ['fields' => ['height', 'shirt_size', 'year_started', 'skill_level']]);

$no_matches = __('No matching offering, please try a different option');
$multiple_matches = __('Multiple matches, please refine your selection');
$locale = \Cake\I18n\I18n::getLocale();
$currency = Configure::read('payment.currency');

echo $this->Html->scriptBlock("
const formatter = new Intl.NumberFormat('$locale', {
  style: 'currency',
  currency: '$currency',
});

function resetRadio(trigger) {
	table = trigger.closest('table').find('input:checked').not('.disabled').each(function() {
		i = zjQuery(this);
		if (i.attr('disabled') === undefined) {
			i.prop('checked', false);
			radioChanged(i);
		}
	});
}

function radioChangedCallback(trigger, row) {
	options = row.find('span.prices:visible');
	el = row.find('span.final');

	if (options.length === 0) {
		html = '$no_matches';
	} else {
		min = options.get().reduce(function (result, item) {
			return Math.min(result, zjQuery(item).data('min-cost'));
		}, 999999);
		max = options.get().reduce(function (result, item) {
			return Math.max(result, zjQuery(item).data('max-cost'));
		}, 0);

		html = formatter.format(min);
		if (max !== min) {
			html += ' - ' + formatter.format(max);
		}

		if (options.length === 1) {
			action = zjQuery(options[0]).data('link');
			html += '<br/>' + zjQuery(options[0]).data('event');
		} else {
			action = '$multiple_matches';
		}

		html += '<br/>' + action;
	}

	el.html(html);
}

function initializePrices() {
	zjQuery('.final').each(function () {
		row = zjQuery(this).closest('tr');
		radioChangedCallback(zjQuery(this), row);
	});
}
");

$this->Html->scriptBlock('initializePrices();', ['buffer' => true]);
