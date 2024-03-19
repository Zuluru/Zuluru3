<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 */

if (isset($division)) {
	$this->Breadcrumbs->add(__('Division'));
	$this->Breadcrumbs->add($division->full_league_name);
} else {
	$this->Breadcrumbs->add(__('League'));
	$this->Breadcrumbs->add($league->full_name);
}
$this->Breadcrumbs->add(__('Delete Games'));
?>

<div class="schedules delete">
<h2><?php
echo __('Delete Games') . ': ';
if (isset($division)) {
	echo $division->full_league_name;
} else {
	echo $league->full_name;
}
?></h2>

<?php
$published = collection($games)->match(['published' => true])->toList();
$finalized = collection($games)->filter(function ($game) {
	return $game->isFinalized();
})->toList();

if (isset($date)) {
	$dates = collection($games)->extract('game_slot.game_date')->toList();
	echo $this->Html->para(null, __('You have requested to delete games on {0}.', $this->Time->dateRange(min($dates), max($dates))));
} else {
	echo $this->Html->para(null, __('You have requested to delete games from pool {0}.', $pool->name));
}
?>
<p><?php
echo __('This will remove {0} games', count($games));
if (!empty($published)) {
	echo __(', of which {0} are published', count($published));
	if (!empty($finalized)) {
		echo __(' and {0} have been finalized', count($finalized));
	}
}
?>.<?php
if (!empty($same_pool)) {
	echo ' ' . $this->Html->tag('span', __('There are {0} games in the same pool but on different days which will also be deleted.', count($same_pool)), ['class' => 'warning-message']);
}
if (!empty($dependent)) {
	echo ' ' . $this->Html->tag('span', __('There are also {0} additional games dependent in some way on these which will be deleted.', count($dependent)), ['class' => 'warning-message']);
}
?></p>
<?php
if (!empty($published)) {
	echo $this->Html->para(null, __('Deleting published games can be confusing for coaches, captains and players, so be sure to {0} to inform them of this.',
		isset($division) ? $this->Html->link(__('contact all coaches and captains'), ['controller' => 'Divisions', 'action' => 'emails', 'division' => $division->id]) : __('contact all coaches and captains')));
}
if (!empty($finalized)):
?>
<p class="warning-message"><?= __('Deleting finalized games will have effects on standings <strong>which cannot be undone</strong>. Please be <strong>very sure</strong> that you want to do this before proceeding.') ?></p>
<?php
endif;
?>

<div class="actions columns">
	<ul class="nav nav-pills">
		<li>
<?php
if (isset($division)) {
	$id_field = 'division';
	$id = $division_id;
} else {
	$id_field = 'league';
	$id = $league_id;
}
// TODOBOOTSTRAP: Format this as a submit button, not an action link
echo $this->Html->link(__('Proceed'), [$id_field => $id, 'date' => $date->toDateString(), 'pool' => $pool_id, 'confirm' => true]);
?>
		</li>
	</ul>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
if (isset($division)) {
	echo $this->element('Divisions/actions', [
		'league' => $division->league,
		'division' => $division,
		'format' => 'list',
	]);
} else {
	echo $this->element('Leagues/actions', [
		'league' => $league,
		'format' => 'list',
	]);
}
?>
	</ul>
</div>
