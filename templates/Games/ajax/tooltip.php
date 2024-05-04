<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 */

use Cake\Core\Configure;
?>

<h2><?= __('Game {0}', $game->id) ?></h2>
<dl class="row">
	<dt class="col-sm-3 text-end"><?= __('Date') ?></dt>
	<dd class="col-sm-9 mb-0"><?= $this->Time->date($game->game_slot->game_date) ?></dd>
	<dt class="col-sm-3 text-end"><?= __('Time') ?></dt>
	<dd class="col-sm-9 mb-0"><?= $this->Time->TimeRange($game->game_slot) ?></dd>
	<dt class="col-sm-3 text-end"><?= Configure::read('UI.field_cap') ?></dt>
	<dd class="col-sm-9 mb-0"><?= $this->Html->link($game->game_slot->field->long_name,
		['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game->game_slot->field->facility_id]]) ?></dd>
	<dt class="col-sm-3 text-end"><?= $game->division->schedule_type == 'competition' ? __('Team') : __('Home Team') ?></dt>
	<dd class="col-sm-9 mb-0"><?php
		echo $this->Html->link($game->home_team->name,
			['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $game->home_team->id]]);
		if (Configure::read('feature.shirt_colour') && !empty($game->home_team->shirt_colour)) {
			echo ' ' . $this->element('shirt', ['colour' => $game->home_team->shirt_colour]);
		}
	?></dd>
<?php
if ($game->division->schedule_type != 'competition'):
?>
	<dt class="col-sm-3 text-end"><?= __('Away Team') ?></dt>
	<dd class="col-sm-9 mb-0"><?php
		echo $this->Html->link($game->away_team->name,
			['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $game->away_team->id]]);
		if (Configure::read('feature.shirt_colour') && !empty($game->away_team->shirt_colour)) {
			echo ' ' . $this->element('shirt', ['colour' => $game->away_team->shirt_colour]);
		}
	?></dd>
<?php
endif;
?>

</dl>
