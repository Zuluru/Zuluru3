<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

$this->Html->addCrumb(__('Divisions'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Adjust Initial Seeds'));
?>

<div class="divisions seeds">
	<h2><?= __('Adjust Initial Seeds') . ': ' . $division->full_league_name ?></h2>

	<p><?= __('Use the form below to adjust a team\'s initial seeds for \'better\' or for \'worse\' by entering a new seed into the box beside each team. Changes are <strong>not</strong> saved until you click \'Save Changes\' below. Multiple teams cannot have the same seed.') ?></p>
	<p><?= __('Note that this adjusts a team\'s <strong>initial</strong> seed; their <strong>current</strong> seed will be unaffected, as it is determined by game results. Initial seeds are typically unimportant for standard leagues, but are required for non-playoff tournaments.') ?></p>
	<p><?= __('For the seed values, a <strong>LOWER</strong> numbered seed is <strong>BETTER</strong>, and a <strong>HIGHER</strong> numbered seed is <strong>WORSE</strong>.') ?></p>

	<?= $this->Form->create($division) ?>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team Name') ?></th>
					<th><?= __('Avg. Skill') ?></th>
<?php
if ($division->is_playoff):
?>
					<th><?= __('Rating') ?></th>
<?php
endif;
?>
					<th><?= __('Initial Seed') ?></th>
					<th><?= __('New Initial Seed') ?></th>
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
<?php
if ($division->is_playoff):
?>
					<td><?= $team->rating ?></td>
<?php
endif;
?>
					<td><?= $team->initial_seed ?></td>
					<td><?php
						echo $this->Form->control("teams.$key.id", [
							'value' => $team->id,
						]);
						echo $this->Form->control("teams.$key.initial_seed", [
							'label' => false,
							'size' => 3,
							'tabindex' => 1,
							'value' => $team->initial_seed,
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
