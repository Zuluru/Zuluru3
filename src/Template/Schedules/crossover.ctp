<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var int $teams
 */

$this->Html->addCrumb(__('Division'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Add Games'));
$this->Html->addCrumb(__('Number of Crossovers'));
?>

<div class="schedules add">
<?= $this->element('Schedules/exclude') ?>

<?php
echo $this->Form->create($division, ['align' => 'horizontal']);
$division->_options->step = 'crosscount';
echo $this->element('hidden', ['model' => '_options', 'fields' => $division->_options]);
?>

<fieldset>
<legend><?= __('Select number of crossover games') ?></legend>

<?php
$options = [];
for ($i = 1; $i <= floor($teams / 2); ++ $i) {
	$options["crossover_$i"] = $i;
}
echo $this->Form->control('_options.pool_type', [
	'label' => __('How many crossover games do you want?'),
	'options' => $options,
	'help' => __('This is the total number of crossover games for all pools in this division.'),
]);
?>

</fieldset>

<?php
echo $this->Form->button(__('Next step'), ['class' => 'btn-success']);
echo $this->Form->end();
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
