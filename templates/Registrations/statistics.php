<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[] $events
 * @var int[] $affiliates
 * @var string[] $years
 */

use App\Model\Entity\Event;

$this->Breadcrumbs->add(__('Registrations'));
$this->Breadcrumbs->add(__('Statistics'));
?>

<div class="registrations statistics">
	<h2><?= __('Registration Statistics') ?></h2>

<?php
echo $this->Selector->selector('Sport', $this->Selector->extractOptions(
	$events,
	function (Event $item) { return $item->division ? $item->division->league : null; },
	'sport'
));
echo $this->Selector->selector('Season', $this->Selector->extractOptionsUnsorted(
	$events,
	function (Event $item) { return $item->division ? $item->division->league : null; },
	'season'
));
echo $this->Selector->selector('Type', $this->Selector->extractOptions(
	$events,
	function (Event $item) { return $item->event_type; },
	'name', 'id'
));
echo $this->Selector->selector('Day', $this->Selector->extractOptions(
	$events,
	function (Event $item) { return $item->division && !empty($item->division->days) ? $item->division->days : null; },
	'name', 'id'
));

$play_types = ['team', 'individual'];
?>

	<div class="table-responsive clear-float">
		<table class="table table-striped table-hover table-condensed">
			<tbody>
<?php
$group = $affiliate_id = null;
$my_affiliates = $this->UserCache->read('ManagedAffiliateIDs');
foreach ($events as $event):
	if (count($affiliates) > 1 && $event->affiliate_id != $affiliate_id):
		$affiliate_id = $event->affiliate_id;
?>
				<tr><td colspan="3" class="affiliate"><h3><?= $event->_matchingData['Affiliates']->name ?></h3></td></tr>
<?php
	endif;

	if ($event->event_type->name != $group):
		$group = $event->event_type->name;

		$classes = collection($events)->filter(function ($test) use ($event) {
			return $test->event_type_id == $event->event_type_id;
		})->extract(function (Event $event) {
			return "select_id_{$event->id}";
		})->toArray();
?>
				<tr class="<?= implode(' ', $classes) ?>"><td colspan="3"><h4><?= $group ?></h4></td></tr>
<?php
	endif;
?>

				<tr class="select_id_<?= $event->id ?>">
					<td><?= $this->Html->link($event->name, ['action' => 'summary', '?' => ['event' => $event->id]]) ?></td>
					<td><?= $event->registration_count ?></td>
					<td class="actions"><?= $this->element('Events/actions', ['event' => $event]) ?></td>
				</tr>
<?php
endforeach;
?>

			</tbody>
		</table>
	</div>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
foreach ($years as $year) {
	echo $this->Html->tag('li', $this->Html->link($year['year'], ['action' => 'statistics', '?' => ['year' => $year['year']]]));
}
?>

	</ul>
</div>
