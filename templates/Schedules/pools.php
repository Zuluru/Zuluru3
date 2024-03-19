<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var int $stage
 * @var string[] $types
 */

$this->Breadcrumbs->add(__('Division'));
$this->Breadcrumbs->add($division->full_league_name);
$this->Breadcrumbs->add(__('Add Games'));
$this->Breadcrumbs->add(__('Create Pools'));
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
echo $this->Form->control('_options.pool_type', [
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
