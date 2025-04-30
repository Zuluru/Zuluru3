<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 */

$this->Breadcrumbs->add(__('Assign Official'));

$roster = collection($game->team_officials[0]->people)->combine('id', 'full_name')->toArray();
?>

<div class="games officials form">
	<h2><?= __('Assign Official') ?></h2>
	<?= $this->Form->create($game) ?>
	<dl class="row">
		<dt class="col-sm-3 text-end"><?= __('League') . '/' . __('Division') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Divisions/block', ['division' => $game->division, 'field' => 'full_league_name']) ?></dd>
		<dt class="col-sm-3 text-end"><?= $game->division->schedule_type == 'competition' ? __('Team') : __('Home Team') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
			if ($game->home_team === null) {
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
<?php
if ($game->division->schedule_type !== 'competition'):
?>
		<dt class="col-sm-3 text-end"><?= __('Away Team') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
			if ($game->away_team === null) {
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
<?php
endif;
?>
		<dt class="col-sm-3 text-end"><?= __('Date and Time') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Time->dateTimeRange($game->game_slot) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Location') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Assign Official') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Form->control('officials.0.id', [
			'label' => false,
			'type' => 'select',
			'options' => $roster,
			'hiddenField' => false,
			'empty' => empty($game->officials),
		]) ?></dd>
	</dl>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
