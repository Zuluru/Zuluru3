<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var bool $can_edit
 */

?>
<div class="game">
<?php
if ($game->has('id')):
?>
	<div class="home">
		<div class="team<?php
			if ($game->isFinalized()) {
				if ($game->home_score > $game->away_score) {
					echo ' winner';
				} else if ($game->home_score < $game->away_score) {
					echo ' loser';
				}
			}
		?>">
<?php
	if ($game->home_team_id !== null) {
		if ($game->home_dependency_type == 'seed') {
			echo "({$game->home_dependency_id}) ";
		}
		echo $this->element('Teams/block', ['team' => $teams[$game->home_team_id], 'options' => ['max_length' => 16]]);
	} else {
		$game->readDependencies();
		echo $game->home_dependency;
	}
?>
		</div>
		<div class="score"><?= $game->home_score ?></div>
	</div>
	<div class="details">
		<div class="name">
<?php
	if ($game->published || $can_edit) {
		echo $this->Html->link($game->display_name, ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]]);
	} else {
		echo $game->display_name;
	}
?>
		</div>
<?php
	if ($game->game_slot->start_time->isFuture()) {
		$date = $this->Time->date($game->game_slot->game_date) . '<br/>' . $this->Time->time($game->game_slot->game_start);
		if ($game->published) {
			echo $this->Html->tag('div', $date, ['class' => 'date']);
		} else if ($can_edit) {
			echo $this->Html->tag('div', $date, ['class' => 'date unpublished']);
		}
	}
?>
	</div>
	<div class="away">
		<div class="team<?php
			if ($game->isFinalized()) {
				if ($game->away_score > $game->home_score) {
					echo ' winner';
				} else if ($game->away_score < $game->home_score) {
					echo ' loser';
				}
			}
		?>">
<?php
	if ($game->away_team_id !== null) {
		if ($game->away_dependency_type == 'seed') {
			echo "({$game->away_dependency_id}) ";
		}
		echo $this->element('Teams/block', ['team' => $teams[$game->away_team_id], 'options' => ['max_length' => 16]]);
	} else {
		$game->readDependencies();
		echo $game->away_dependency;
	}
?>
		</div>
		<div class="score"><?= $game->away_score ?></div>
	</div>
<?php
endif;
?>
</div>
