<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[] $events
 * @var string $year
 * @var string[][] $years
 * @var int[] $affiliates
 */

use App\Controller\AppController;
use App\Model\Entity\Event;
use App\Model\Entity\Price;
use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Registration Events'));
$this->Breadcrumbs->add(__('List'));
if (isset($year)) {
	$this->Breadcrumbs->add($year);
}
?>

<div class="events index">
	<h2><?= __('Registration Events List') ?><?= isset($year) ? ': ' . $year : '' ?></h2>
<?php
echo $this->element('Events/selectors', compact('events'));

$events = collection($events)->groupBy('affiliate_id')->toArray();
foreach ($events as $affiliate_id => $affiliate_events):
	if (count($affiliates) > 1):
?>
	<h3 class="affiliate"><?= h($affiliate_events[0]->affiliate->name) ?></h3>
<?php
	endif;

	$uncategorized = [];
	foreach ($affiliate_events as $event) {
		if (!array_key_exists($event->event_type_id, $uncategorized)) {
			$uncategorized[$event->event_type_id] = [];
		}
		$uncategorized[$event->event_type_id][] = $event;
	}

	ksort($uncategorized);
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
endforeach;
?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
foreach ($years as $y) {
	echo $this->Html->tag('li', $this->Html->link($y['year'], ['?' => ['year' => $y['year']]]));
}
?>
	</ul>
</div>
