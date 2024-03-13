<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var int $stage
 * @var string[] $types
 */

$this->Html->addCrumb(__('Division'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Add Games'));
$this->Html->addCrumb(__('Create Pools'));
?>

<div class="schedules add">

<p><?php
if ($stage > 1) {
	echo __('You have scheduled games for all of the existing team pools, up to stage {0} of the tournament. To proceed, you will need to define new pools.', $stage);
} else {
	echo __('To schedule a tournament, you must first define how the teams are broken into pools for the first round.');
}
echo ' ';
echo __('Options below reflect your choices for creating these pools.');

echo $this->Html->help(['action' => 'schedules', 'add', 'tournament', 'pools']) ?>
</p>

<?php
echo $this->Form->create($division, ['align' => 'horizontal']);
$division->_options->step = 'pools';
echo $this->element('hidden', ['model' => '_options', 'fields' => $division->_options]);
?>

<fieldset>
<legend><?= __('Create a ...') ?></legend>
<?php
echo $this->Form->input('_options.pool_type', [
	'label' => false,
	'type' => 'radio',
	'options' => $types,
]);
?>

<p><?= __('Select the number of pools to create. You will then be given options for setting the details of these pools.') ?></p>

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
