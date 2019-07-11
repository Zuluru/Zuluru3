<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Game Slot'));
$this->Html->addCrumb(__('View'));
?>

<div class="gameSlots view">
	<h2><?= __('Game Slot') ?></h2>
	<dl class="dl-horizontal">
		<dt><?= __(Configure::read('UI.field_cap')) ?></dt>
		<dd><?= $this->element('Fields/block', ['field' => $game_slot->field, 'display_field' => 'long_name']) ?></dd>
		<dt><?= __('Game Date') ?></dt>
		<dd><?= $this->Time->date($game_slot->game_date) ?></dd>
		<dt><?= __('Game Start') ?></dt>
		<dd><?= $this->Time->time($game_slot->game_start) ?></dd>
		<dt><?= __('Game End') ?></dt>
		<dd><?= $this->Time->time($game_slot->display_game_end) ?></dd>
		<dt><?= __n('Game', 'Games', count($game_slot->games)) ?></dt>
		<dd><?php
			if (empty($game_slot->games)) {
				echo __('Unassigned');
			} else {
				$games = [];
				foreach ($game_slot->games as $game) {
					$game->readDependencies();
					$line = $this->Html->link($game->id, ['controller' => 'Games', 'action' => 'view', 'game' => $game->id]) . ': ';

					if ($game->home_team_id === null) {
						$line .= $game->home_dependency;
					} else {
						$line .= $this->element('Teams/block', ['team' => $game->home_team]);
					}

					$line .= __(' vs ');

					if ($game->away_team_id === null) {
						$line .= $game->away_dependency;
					} else {
						$line .= $this->element('Teams/block', ['team' => $game->away_team]);
					}

					$line .= __(' ({0})', $this->element('Divisions/block', ['division' => $game->division, 'field' => 'full_league_name']));
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
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', 'slot' => $game_slot->id],
	['alt' => __('Edit'), 'title' => __('Edit Game Slot')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'slot' => $game_slot->id],
	['alt' => __('Delete'), 'title' => __('Delete Game Slot')],
	['confirm' => __('Are you sure you want to delete this game_slot?')]));
?>
	</ul>
</div>
