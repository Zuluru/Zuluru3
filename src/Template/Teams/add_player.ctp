<?php
/**
 * @type \App\Model\Entity\Team $team
 * @type \App\Model\Entity\Team[] $teams
 * @type \App\Model\Entity\Event[] $events
 */

$this->Html->addCrumb(__('Team'));
$this->Html->addCrumb(__('Add Player'));
$this->Html->addCrumb(h($team->name));
?>

<div class="teams add_player">
	<h2><?= __('Add Player') . ': ' . h($team->name) ?></h2>

<?php
if (empty($team->division_id)) {
	$affiliate_id = $team->affiliate_id;
} else {
	$affiliate_id = $team->division->league->affiliate_id;
}
echo $this->element('People/search_form', ['affiliate_id' => $affiliate_id]);
?>

	<div id="SearchResults" class="zuluru_pagination">

		<?= $this->element('People/search_results', ['extra_url' => [__('Add to team') => ['controller' => 'Teams', 'action' => 'roster_add', 'team' => $team->id]]]) ?>

	</div>
	<p><?php
// Give captains the ability to add from previous teams; doesn't make much sense for admins/managers/coordinators
if (!empty($teams) && in_array($team->id, $this->UserCache->read('OwnedTeamIDs'))) {
	echo __('Or select a team from your history below to invite people from that roster.');
	$options = [];
	foreach ($teams as $history) {
		if (empty($history->division_id)) {
			$options[$history->id] = $history->name;
		} else {
			$options[$history->id] = "{$history->name} ({$history->division->full_league_name})";
		}
	}
	echo $this->Form->create(false, ['url' => ['action' => 'add_from_team', 'team' => $team->id], 'align' => 'horizontal']);
	echo $this->Form->input('team', [
		'label' => false,
		'options' => $options,
		'empty' => __('-- select from list --'),
	]);
	echo $this->Form->button(__('Show roster'), ['class' => 'btn-success']);
	echo $this->Form->end();
}
?></p>

	<p><?php
if (!empty($events)) {
	echo __('Or select a recent event to add people that are registered.');
	$options = [];
	foreach ($events as $event) {
		$options[$event->id] = $event->name;
	}
	echo $this->Form->create(false, ['url' => ['action' => 'add_from_event', 'team' => $team->id], 'align' => 'horizontal']);
	echo $this->Form->input('event', [
		'label' => false,
		'options' => $options,
		'empty' => __('-- select from list --'),
	]);
	echo $this->Form->button(__('Show registrations'), ['class' => 'btn-success']);
	echo $this->Form->end();
}
?></p>
</div>

<div class="actions columns">
<?php
$args = ['team' => $team, 'format' => 'list'];
if (!empty($team->division_id)) {
	$args['division'] = $team->division;
	$args['league'] = $team->division->league;
}
echo $this->element('Teams/actions', $args);
?>
</div>
