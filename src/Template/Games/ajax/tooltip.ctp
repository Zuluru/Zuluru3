<?php
use Cake\Core\Configure;
?>

<h2><?= __('Game {0}', $game->id) ?></h2>
<dl class="dl-horizontal">
	<dt><?= __('Date') ?></dt>
	<dd><?= $this->Time->date($game->game_slot->game_date) ?></dd>
	<dt><?= __('Time') ?></dt>
	<dd><?= $this->Time->TimeRange($game->game_slot) ?></dd>
	<dt><?= __(Configure::read('UI.field_cap')) ?></dt>
	<dd><?= $this->Html->link($game->game_slot->field->long_name,
		['controller' => 'Facilities', 'action' => 'view', 'facility' => $game->game_slot->field->facility_id]) ?></dd>
	<dt><?= $game->division->schedule_type == 'competition' ? __('Team') : __('Home Team') ?></dt>
	<dd><?php
		echo $this->Html->link($game->home_team->name,
			['controller' => 'Teams', 'action' => 'view', 'team' => $game->home_team->id]);
		if (Configure::read('feature.shirt_colour') && !empty($game->home_team->shirt_colour)) {
			echo ' ' . $this->element('shirt', ['colour' => $game->home_team->shirt_colour]);
		}
	?></dd>
<?php
if ($game->division->schedule_type != 'competition'):
?>
	<dt><?= __('Away Team') ?></dt>
	<dd><?php
		echo $this->Html->link($game->away_team->name,
			['controller' => 'Teams', 'action' => 'view', 'team' => $game->away_team->id]);
		if (Configure::read('feature.shirt_colour') && !empty($game->away_team->shirt_colour)) {
			echo ' ' . $this->element('shirt', ['colour' => $game->away_team->shirt_colour]);
		}
	?></dd>
<?php
endif;
?>

</dl>
