<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\GameSlot $game_slot
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Game Slot'));
$this->Breadcrumbs->add(__('View'));
?>

<div class="gameSlots view">
	<h2><?= __('Game Slot') ?></h2>
	<dl class="row">
<?php
if (!empty($game_slot->games)):
?>
		<dt class="col-sm-3 text-end"><?= __('Division') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Divisions/block', ['division' => $game_slot->games[0]->division, 'field' => 'full_league_name']) ?></dd>
<?php
endif;
?>
		<dt class="col-sm-3 text-end"><?= Configure::read('UI.field_cap') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Fields/block', ['field' => $game_slot->field, 'display_field' => 'long_name']) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Game Date') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Time->date($game_slot->game_date) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Game Start') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Time->time($game_slot->game_start) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Game End') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Time->time($game_slot->display_game_end) ?></dd>
		<dt class="col-sm-3 text-end"><?= __n('Game', 'Games', count($game_slot->games)) ?></dt>
		<dd class="col-sm-9 mb-0"><?php
			if (empty($game_slot->games)) {
				echo __('Unassigned');
			} else {
				$games = [];
				foreach ($game_slot->games as $game) {
					$game->readDependencies();
					$line = $this->Html->link($game->id, ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]]) . ': ';

					if ($game->home_team_id === null) {
						$line .= $game->home_dependency;
					} else {
						$line .= $this->element('Teams/block', ['team' => $game->home_team]);
					}

					if ($game->division->schedule_type !== 'competition') {
						$line .= __(' vs ');

						if ($game->away_team_id === null) {
							$line .= $game->away_dependency;
						} else {
							$line .= $this->element('Teams/block', ['team' => $game->away_team]);
						}
					}

					$games[] = $line;
				}
				echo implode('<br />', $games);
			}
		?></dd>
	</dl>
</div>
<?php
if (!empty($game_slot->divisions)):
?>
<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('Available to Divisions') ?></h4>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<tbody>
<?php
	foreach ($game_slot->divisions as $division):
?>
				<tr>
					<td><?= $this->element('Divisions/block', ['division' => $division, 'field' => 'full_league_name']) ?></td>
				</tr>

<?php
endforeach;
?>
			</tbody>
		</table>
		</div>
	</div>
</div>
<?php
endif;
?>
<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['slot' => $game_slot->id]],
		['alt' => __('Edit'), 'title' => __('Edit Game Slot')]
	),
	$this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['slot' => $game_slot->id]],
		['alt' => __('Delete'), 'title' => __('Delete Game Slot')],
		['confirm' => __('Are you sure you want to delete this game_slot?')]
	),
]);
?>
</div>
