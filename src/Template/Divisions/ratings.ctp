<?php
$this->Html->addCrumb(__('Divisions'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Adjust Ratings'));
?>

<div class="divisions ratings">
	<h2><?= __('Adjust Ratings') . ': ' . $division->full_league_name ?></h2>

	<p><?= __('Use the form below to adjust a team\'s initial ratings for \'better\' or for \'worse\' by entering a new rating into the box beside each team. Changes are <strong>not</strong> saved until you click \'Save Changes\' below. Multiple teams can have the same ratings, and likely will at the start of the season.') ?></p>
	<p><?= __('Note that this adjusts a team\'s <strong>initial</strong> rating; their <strong>current</strong> rating will be recalculated. Such adjustments are not typically needed mid-season, as ladder systems take care of mis-seedings eventually, but this can speed the process if you belatedly realize that a team was grossly mis-rated to start the season.') ?></p>
	<p><?= __('For the rating values, a <strong>HIGHER</strong> numbered rating is <strong>BETTER</strong>, and a <strong>LOWER</strong> numbered rating is <strong>WORSE</strong>.') ?></p>

	<?= $this->Form->create($division) ?>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team Name') ?></th>
					<th><?= __('Avg. Skill') ?></th>
					<th><?= __('Current Rating') ?></th>
					<th><?= __('Initial Rating') ?></th>
					<th><?= __('New Initial Rating') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
foreach ($division->teams as $key => $team):
?>
				<tr>
					<td><?= $this->element('Teams/block', ['team' => $team, 'show_shirt' => false]) ?></td>
					<td><?php
						$team->consolidateRoster($division->league->sport);
						echo $team->average_skill;
					?></td>
					<td><?= $team->rating ?></td>
					<td><?= $team->initial_rating ?></td>
					<td><?php
						echo $this->Form->input("teams.$key.id", [
							'value' => $team->id,
						]);
						echo $this->Form->input("teams.$key.initial_rating", [
							'label' => false,
							'size' => 3,
							'tabindex' => 1,
							'value' => $team->initial_rating,
						]);
					?></td>
				</tr>
<?php
endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
echo $this->Form->button(__('Save Changes'), ['class' => 'btn-success']);
echo $this->Form->button(__('Reset'), ['type' => 'reset']);
echo $this->Form->end();
?>

</div>
