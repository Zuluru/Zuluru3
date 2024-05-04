<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 */

$this->Breadcrumbs->add(__('Games'));
$this->Breadcrumbs->add(__('Game') . ' ' . $game->id);
$this->Breadcrumbs->add(__('Note'));
if ($note->isNew()) {
	$this->Breadcrumbs->add(__('Add'));
} else {
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="games view">
	<h2><?= __('Game Note') ?></h2>
	<dl class="row">
		<dt class="col-sm-3 text-end"><?= __('League') . '/' . __('Division') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Divisions/block', ['division' => $game->division, 'field' => 'full_league_name']) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Home Team') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
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
		<dt class="col-sm-3 text-end"><?= __('Away Team') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
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
		<dt class="col-sm-3 text-end"><?= __('Date and Time') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Time->dateTimeRange($game->game_slot) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Location') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']) ?></dd>
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
echo $this->Form->control('visibility', [
	'options' => $options,
	'hide_single' => true,
]);

echo $this->Form->control('note', ['cols' => 70, 'class' => 'wysiwyg_simple']);
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
