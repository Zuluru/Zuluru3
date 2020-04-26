<?php
$this->Html->addCrumb(__('Games'));
$this->Html->addCrumb(__('Game') . ' ' . $game->id);
$this->Html->addCrumb(__('Note'));
if ($note->isNew()) {
	$this->Html->addCrumb(__('Add'));
} else {
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="games view">
	<h2><?= __('Game Note') ?></h2>
	<dl class="dl-horizontal">
		<dt><?= __('League') . '/' . __('Division') ?></dt>
		<dd><?= $this->element('Divisions/block', ['division' => $game->division, 'field' => 'full_league_name']) ?></dd>
		<dt><?= __('Home Team') ?></dt>
		<dd><?php
			if ($game->home_team_id === null) {
				if ($game->has('home_dependency')) {
					echo $game->home_dependency;
				} else {
					echo __('Unassigned');
				}
			} else {
				echo $this->element('Teams/block', ['team' => $game->home_team]);
				if ($game->has('home_dependency')) {
					echo " ({$game->home_dependency})";
				}
			}
		?></dd>
		<dt><?= __('Away Team') ?></dt>
		<dd><?php
			if ($game->away_team_id === null) {
				if ($game->has('away_dependency')) {
					echo $game->away_dependency;
				} else {
					echo __('Unassigned');
				}
			} else {
				echo $this->element('Teams/block', ['team' => $game->away_team]);
				if ($game->has('away_dependency')) {
					echo " ({$game->away_dependency})";
				}
			}
		?></dd>
		<dt><?= __('Date and Time') ?></dt>
		<dd><?= $this->Time->dateTimeRange($game->game_slot) ?></dd>
		<dt><?= __('Location') ?></dt>
		<dd><?= $this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']) ?></dd>
	</dl>
</div>

<div class="games form">
<?php
echo $this->Form->create($note, ['align' => 'horizontal']);
echo $this->Form->hidden('game_id', ['value' => $game->id]);

$identity = $this->Authorize->getIdentity();
$options = [
	VISIBILITY_PRIVATE => __('Only I will be able to see this'),
];
if ($this->Authorize->getIdentity()->isPlayerOn($game)) {
	$options[VISIBILITY_CAPTAINS] = __('Only the coaches/captains of the team');
	$options[VISIBILITY_TEAM] = __('Everyone on the team');
}
if ($this->Authorize->getIdentity()->isCoordinatorOf($game)) {
	$options[VISIBILITY_COORDINATOR] = __('Admins and coordinators of this division');
}
if ($this->Authorize->getIdentity()->isManagerOf($game)) {
	$options[VISIBILITY_ADMIN] = __('Administrators only');
}
echo $this->Form->input('visibility', [
	'options' => $options,
	'hide_single' => true,
]);

echo $this->Form->input('note', ['cols' => 70, 'class' => 'wysiwyg_simple']);
if ($note->isNew()) {
	echo $this->Html->para(null, __('Everyone else that is allowed to see this note will be sent an email informing them. This is a good way to communicate with your teams.'));
} else {
	echo $this->Html->para(null, __('Emails are NOT sent to others when you edit an existing note.'));
}
echo $this->Html->para(null, __('Under no circumstance will the players on the other team, or anyone else, be able to see this.'));
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
