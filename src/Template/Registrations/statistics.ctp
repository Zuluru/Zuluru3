<?php
use App\Controller\AppController;

$this->Html->addCrumb(__('Registrations'));
$this->Html->addCrumb(__('Statistics'));
?>

<div class="registrations statistics">
	<h2><?= __('Registration Statistics') ?></h2>

<?php
$types = collection($events)->combine('event_type.id', 'event_type.name')->toArray();
ksort($types);
echo $this->element('selector', ['title' => 'Type', 'options' => $types]);

$sports = array_unique(collection($events)->extract('division.league.sport')->toArray());
sort($sports);
echo $this->element('selector', ['title' => 'Sport', 'options' => $sports]);

$seasons = array_unique(collection($events)->extract('division.league.season')->toArray());
echo $this->element('selector', ['title' => 'Season', 'options' => $seasons]);

$days = collection($events)->extract('division.days.{*}')->combine('id', 'name')->toArray();
ksort($days);
echo $this->element('selector', ['title' => 'Day', 'options' => $days]);

$play_types = ['team', 'individual'];
?>

	<div class="table-responsive clear-float">
		<table class="table table-striped table-hover table-condensed">
			<tbody>
<?php
$group = $affiliate_id = null;
$my_affiliates = $this->UserCache->read('ManagedAffiliateIDs');
foreach ($events as $event):
	// Perhaps remove manager status, if we're looking at a different affiliate
	$is_event_manager = in_array($event->affiliate_id, $my_affiliates);

	if (count($affiliates) > 1 && $event->affiliate_id != $affiliate_id):
		$affiliate_id = $event->affiliate_id;
?>
				<tr><td colspan="3" class="affiliate"><h3><?= $event->_matchingData['Affiliates']->name ?></h3></td></tr>
<?php
	endif;

	if ($event->event_type->name != $group):
		$group = $event->event_type->name;

		$classes = [];
		$classes[] = $this->element('selector_classes', ['title' => 'Type', 'options' => $event->event_type->name]);
		if (in_array($event->event_type->type, $play_types)) {
			$divisions = collection($events)->filter(function ($test) use ($event) {
				return $test->event_type_id == $event->event_type_id;
			})->extract('division');

			$sports = array_unique($divisions->extract('league.sport')->toArray());
			sort($sports);
			$classes[] = $this->element('selector_classes', ['title' => 'Sport', 'options' => $sports]);

			$seasons = array_unique($divisions->extract('league.season')->toArray());
			$classes[] = $this->element('selector_classes', ['title' => 'Season', 'options' => $seasons]);

			$days = $divisions->extract('days.{*}')->combine('id', 'name')->toArray();
			ksort($days);
			$classes[] = $this->element('selector_classes', ['title' => 'Day', 'options' => $days]);
		}
?>
				<tr class="<?= implode(' ', $classes) ?>"><td colspan="3"><h4><?= $group ?></h4></td></tr>
<?php
	endif;

	$classes = [];
	$classes[] = $this->element('selector_classes', ['title' => 'Type', 'options' => $event->event_type->name]);
	if (in_array($event->event_type->type, $play_types) && !empty($event->division->id)) {
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
?>

				<tr class="<?= implode(' ', $classes) ?>">
					<td><?= $this->Html->link($event->name, ['action' => 'summary', 'event' => $event->id]) ?></td>
					<td><?= $event->registration_count ?></td>
					<td class="actions"><?= $this->element('Events/actions', ['event' => $event, 'is_event_manager' => $is_event_manager]) ?></td>
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
	echo $this->Html->tag('li', $this->Html->link($year['year'], ['action' => 'statistics', 'year' => $year['year']]));
}
?>

	</ul>
</div>
