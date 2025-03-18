<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game[] $games
 * @var \Cake\I18n\FrozenDate $date
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Breadcrumbs->add(__('Daily Schedule'));
$this->Breadcrumbs->add($this->Time->date($date));
?>

<div class="schedules day form">
	<h2><?= __('Daily Schedule') . ': ' . $this->Time->date($date) ?></h2>
<?php
echo $this->Form->create(null, ['align' => 'horizontal']);
echo $this->Form->control('date', [
	'label' => false,
	'type' => 'date',
	'empty' => true,
	'default' => $date,
]);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();

if (empty($games)):
?>
	<p><?= __('No games scheduled for today.') ?></p>
<?php
else:
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
<?php
	// Check if we have any tournament games where we need to display the name.
	$is_tournament = collection($games)->some(function ($game) { return $game->type != SEASON_GAME; });

	$sport = $last_slot = null;
	$has_officials = Configure::read('feature.officials') && $this->Authorize->getIdentity();
	$can_assign = $has_officials && $this->Authorize->getIdentity()->isManager();
	foreach ($games as $game):
		if ($game->division->league->sport != $sport):
			$sport = $game->division->league->sport;
			if (count(Configure::read('options.sport')) > 1):
?>
			<tr>
				<th colspan="<?= 5 + $is_tournament + $has_officials - $can_assign ?>"><?= Inflector::humanize(__($sport)) ?></th>
<?php
				if ($can_assign):
?>
				<th><?= $this->Html->link(__('Assign'), ['controller' => 'Games', 'action' => 'assign_officials', '?' => ['date' => $this->Time->format($date, 'yyyy-MM-dd'), 'sport' => $sport]]) ?></th>
<?php
				endif;
?>
			</tr>

<?php
			endif;
?>
			<tr>
<?php
			if ($is_tournament):
?>
				<th><?= __('Game') ?></th>
<?php
			endif;
?>
				<th><?= __('Time') ?></th>
				<th><?= __(Configure::read("sports.{$sport}.field_cap")) ?></th>
				<th><?= __('Home') ?></th>
				<th><?= __('Away') ?></th>
<?php
			if ($has_officials):
?>
				<th><?= __('Officials') ?></th>
<?php
			endif;
?>
				<th><?= __('Score') ?></th>
			</tr>
<?php
		endif;

		if ($date != $game->game_slot->game_date || !$this->Authorize->can('view', $game)) {
			continue;
		}
		$game->readDependencies();
		$same_slot = ($game->game_slot->id === $last_slot);
?>

			<tr<?= $game->published ? '' : ' class="unpublished"' ?>>
<?php
		if ($is_tournament):
?>
				<td><?= (!$same_slot) ? $game->display_name : '' ?></td>
<?php
		endif;
?>
				<td><?php
					if (!$same_slot) {
						echo $this->Html->link($this->Time->timeRange($game->game_slot), ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]]);
					}
				?></td>
				<td><?= $same_slot ? '' : $this->element('Fields/block', ['field' => $game->game_slot->field]) ?></td>
				<td><?php
					if (empty($game->home_team)) {
						if ($game->has('home_dependency')) {
							echo $game->home_dependency;
						} else {
							echo __('Unassigned');
						}
					} else {
						echo $this->element('Teams/block', ['team' => $game->home_team, 'options' => ['max_length' => 16]]);
					}
				?></td>
				<td><?php
					if (empty($game->away_team)) {
						if ($game->division->schedule_type === 'competition') {
							echo __('N/A');
						} else if ($game->has('away_dependency')) {
							echo $game->away_dependency;
						} else {
							echo __('Unassigned');
						}
					} else {
						echo $this->element('Teams/block', ['team' => $game->away_team, 'options' => ['max_length' => 16]]);
					}
				?></td>
<?php
			if ($has_officials):
?>
				<td><?= $this->element('Games/officials', ['officials' => $game->officials]) ?></td>
<?php
			endif;
?>
				<td class="actions"><?= $this->Game->displayScore($game, $game->division, $game->division->league) ?></td>
			</tr>

<?php
		$last_slot = $game->game_slot->id;
	endforeach;
?>

		</table>
	</div>
<?php
endif;
?>

</div>
