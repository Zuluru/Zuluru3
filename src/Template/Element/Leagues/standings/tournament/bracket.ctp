<?php
use App\Controller\AppController;
use Cake\Core\Configure;

$teams = collection($teams)->indexBy('id')->toArray();

$init_pools = [];

foreach ($games as $bracket_details):
	$bracket = $bracket_details['bracket'];
	$pool_id = $bracket_details['pool_id'];

	if (!in_array($pool_id, $init_pools) && (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator)) {
		$init_pools[] = $pool_id;
		echo $this->Form->iconPostLink('delete_24.png',
			['controller' => 'Schedules', 'action' => 'delete', 'division' => $division->id, 'pool' => $pool_id, 'return' => AppController::_return()],
			['alt' => __('Delete'), 'title' => __('Delete pool games')]);
		echo $this->Html->iconLink('initialize_24.png',
			['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $division->id, 'pool' => $pool_id, 'return' => AppController::_return()],
			['alt' => __('Initialize'), 'title' => __('Initialize schedule dependencies')]);
		echo $this->Html->iconLink('reset_24.png',
			['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $division->id, 'pool' => $pool_id, 'reset' => true, 'return' => AppController::_return()],
			['alt' => __('Reset'), 'title' => __('Reset schedule dependencies')]);
	}
?>
<div class="bracket rounds<?= count($bracket) ?>">
<?php
	foreach ($bracket as $round => $round_games):
?>
	<div class="round round<?= count($bracket) - $round ?>">
<?php
		foreach ($round_games as $game) {
			echo $this->element('Leagues/standings/tournament/bracket_game', compact('game', 'teams'));
		}
?>

	</div>
<?php
	endforeach;
?>
	<div class="round round0">
		<div class="winner">
<?php
	// Whatever game we have here will be the final one in this bracket
	if ($game->isFinalized()) {
		if ($game->home_score >= $game->away_score) {
			echo $this->element('Teams/block', ['team' => $teams[$game->home_team_id], 'options' => ['max_length' => 16]]);
		} else {
			echo $this->element('Teams/block', ['team' => $teams[$game->away_team_id], 'options' => ['max_length' => 16]]);
		}
	}
?>
		</div>
	</div>

</div>
<?php
endforeach;
