<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\League $league
 * @var string $date
 * @var \App\Model\Entity\GameSlot[] $slots
 * @var bool $is_tournament
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
?>

<p><?= $this->Time->fulldate(new FrozenDate($date)) ?></p>
<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
<?php
$competition = collection($league->divisions)->every(function ($division) { return $division->schedule_type === 'competition'; });
?>
			<tr>
				<th><?= __('ID') ?></th>
				<th><?= __(Configure::read("sports.{$league->sport}.field_cap")) ?></th>
				<th><?= __('{0} Region', __(Configure::read("sports.{$league->sport}.field_cap"))) ?></th>
				<th><?= __('Start Time') ?></th>
				<th><?= __('Game') ?></th>
				<th><?= __('Division') ?></th>
<?php
if ($is_tournament):
?>
				<th><?= __('Pool') ?></th>
<?php
endif;
?>
				<th><?= $competition ? __('Team') : __('Home') ?></th>
<?php
if (!$competition):
?>
				<th><?= __('Away') ?></th>
<?php
endif;

if (Configure::read('feature.region_preference') || Configure::read('feature.facility_preference')):
?>
				<th><?= __('Home Pref') ?></th>
<?php
endif;
?>
			</tr>
		</thead>
		<tbody>
<?php
$unused = 0;
foreach ($slots as $slot):
	$rows = max(count($slot->games), 1);
	$cols = 3 + $is_tournament + !$competition + (Configure::read('feature.region_preference') || Configure::read('feature.facility_preference'));
?>
			<tr>
				<td rowspan="<?= $rows ?>"><?= $slot->id ?></td>
				<td rowspan="<?= $rows ?>"><?= $this->element('Fields/block', ['field' => $slot->field]) ?></td>
				<td rowspan="<?= $rows ?>"><?= __($slot->field->facility->region->name) ?></td>
				<td rowspan="<?= $rows ?>"><?= $this->Time->time($slot->game_start) ?></td>
<?php
	if (empty($slot->games)):
		++$unused;
?>
				<td colspan="<?= $cols ?>">---- <?= __('{0} open', __(Configure::read("sports.{$league->sport}.field"))) ?> ----</td>
<?php
	else:
		$first = true;
		foreach ($slot->games as $game):
			$game->readDependencies();
			if (!$first) {
				echo '<tr>';
			}
?>
				<td><?= $this->Html->link($game->id,
					['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]]) ?></td>
				<td><?= $this->element('Divisions/block', ['division' => $game->division]) ?></td>
<?php
			if ($is_tournament):
?>
				<td><?php
					echo $game->pool->name;
					if ($game->pool->type !== 'crossover') {
						echo __(' (round&nbsp;{0})', $game->round);
					}
				?></td>
<?php
			endif;
?>
				<td><?php
					if (empty($game->home_team_id)) {
						if ($game->has('home_dependency')) {
							echo $game->home_dependency;
						} else {
							echo __('Unassigned');
						}
					} else {
						echo $this->element('Teams/block', ['team' => $game->home_team, 'max_length' => 16, 'show_shirt' => false]);
					}
				?></td>
<?php
			if (!$competition):
?>
				<td><?php
					if (empty($game->away_team_id)) {
						if ($game->has('away_dependency')) {
							echo $game->away_dependency;
						} else {
							echo __('Unassigned');
						}
					} else {
						echo $this->element('Teams/block', ['team' => $game->away_team, 'max_length' => 16, 'show_shirt' => false]);
					}
				?></td>
<?php
			endif;

			if (Configure::read('feature.region_preference') || Configure::read('feature.facility_preference')):
?>
				<td><?php
				if ($game->id && $game->has('home_team')) {
					$prefs = [];
					if (Configure::read('feature.facility_preference')) {
						foreach ($game->home_team->facilities as $facility) {
							$prefs[] = $facility->code;
						}
					}
					if (Configure::read('feature.region_preference') && !empty($game->home_team->region)) {
						$prefs[] = __($game->home_team->region->name);
					}
					echo implode(', ', $prefs);
				}
				?></td>
<?php
			endif;

			if ($first) {
				$first = false;
			} else {
				echo '</tr>';
			}

		endforeach;
	endif;
?>
			</tr>
<?php
endforeach;
?>
		</tbody>
	</table>
</div>
<?= __('There are {0} {1} available for use, currently {2} of these are unused.', count($slots), __(Configure::read("sports.{$league->sport}.fields")), $unused);
