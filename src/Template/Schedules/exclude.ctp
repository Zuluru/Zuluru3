<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

$this->Breadcrumbs->add(__('Division'));
$this->Breadcrumbs->add($division->full_league_name);
$this->Breadcrumbs->add(__('Add Games'));
$this->Breadcrumbs->add(__('Select Exclusions'));
?>

<div class="schedules add">
<p><?= __('The "exclude teams" option is set for this division.') ?> <?= $this->Html->help(['action' => 'divisions', 'edit', 'exclude_teams']) ?></p>
<p><?= __('Please select the teams you wish to EXCLUDE from scheduling. You must ensure that you leave an even number of teams.') ?></p>
<?php
echo $this->Form->create($division, ['align' => 'horizontal']);
echo $this->Form->hidden('_options.step', ['value' => 'exclude']);

foreach ($division->teams as $team) {
	// TODO: See discussion of CakePHP bug in date.ctp
	echo $this->Form->control("_options.exclude.t{$team->id}", [
		'label' => $team->name,
		'type' => 'checkbox',
		'value' => $team->id,
		'hiddenField' => false,
		'secure' => false,
	]);
}

echo $this->Form->button(__('Next step'), ['class' => 'btn-success']);
echo $this->Form->end();

$is_tournament = ($division->schedule_type == 'tournament');
if (!$is_tournament) {
	echo $this->Html->para(null, __('Alternately, you can {0}.',
		$this->Html->link(__('create a playoff schedule'), ['division' => $division->id, 'playoff' => true])) .
		$this->Html->help(['action' => 'schedules', 'playoffs'])
	);
}
?>

</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?= $this->element('Divisions/actions', [
	'league' => $division->league,
	'division' => $division,
	'format' => 'list',
]) ?>
	</ul>
</div>
