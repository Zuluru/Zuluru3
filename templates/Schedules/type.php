<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\Pool $pool
 * @var string[] $types
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Division'));
$this->Breadcrumbs->add($division->full_league_name);
$this->Breadcrumbs->add(__('Add Games'));
$this->Breadcrumbs->add(__('Select Type'));
?>

<div class="schedules add">
<?= $this->element('Schedules/exclude') ?>

<?php
$is_tournament = isset($pool) || isset($playoff) || $division->schedule_type == 'tournament';
$create = ($is_tournament ? 'tournament' : 'game(s)');

echo $this->Html->para(null, __('Please enter some information about the {0} to create.', $create));

echo $this->Form->create($division, ['align' => 'horizontal']);
$division->_options->step = 'type';

if (isset($pool)) {
	echo $this->Html->para('warning-message', __('You have defined pool {0} with {1} teams but not yet scheduled games for it. Options below reflect your choices for scheduling this pool.', $pool->name, count($pool->pools_teams)));
	echo $this->Html->para('warning-message', __('If your pool definitions are incorrect, you can {0} and then re-create them.',
		$this->Html->link(__('delete all pools in this stage'),
			['controller' => 'Divisions', 'action' => 'delete_stage', '?' => ['division' => $division->id, 'stage' => $pool->stage]],
			['confirm' => __('Are you sure you want to delete this stage?')])
	));
	$division->_options->pool_id = $pool->id;
}

echo $this->element('hidden', ['model' => '_options', 'fields' => $division->_options]);
?>

<fieldset>
<legend><?= __('Create a ...') ?></legend>
<?php
echo $this->Form->control('_options.type', [
	'label' => false,
	'type' => 'radio',
	'options' => $types,
]);

if ($is_tournament) {
	$help = $this->Html->help(['action' => 'schedules', 'add', 'tournament', 'schedule_type']);
} else {
	$help = $this->Html->help(['action' => 'schedules', 'add', 'schedule_type', $division->schedule_type]);
}
echo $this->Html->para(null, __('Select the type of game or games to add. Note that for auto-generated schedules, {0} will be automatically allocated.', __(Configure::read("sports.{$division->league->sport}.fields"))) . ' ' . $help);

if (!$is_tournament) {
	echo $this->Html->para(null, __('Alternately, you can {0}.',
		$this->Html->link(__('create a playoff schedule'), ['?' => ['division' => $division->id, 'playoff' => true]])) .
		$this->Html->help(['action' => 'schedules', 'playoffs'])
	);
}

echo $this->Form->control('_options.publish', [
	'label' => __('Publish created games for player viewing?'),
	'type' => 'checkbox',
]);
?>

<p><?= __('If this is checked, players will be able to view games immediately after creation. Uncheck it if you wish to make changes before players can view.') ?></p>

<?php
if ($is_tournament):
	echo $this->Form->hidden('_options.double_header', ['value' => false]);
else:
	echo $this->Form->control('_options.double_header', [
		'label' => __('Allow double-headers?'),
		'type' => 'checkbox',
		'checked' => false,
	]);
?>

<p><?= __('If this is checked, you will be allowed to schedule more than the expected number of games. Check it only if you need this, as it disables some safety checks.') ?></p>
<?php
endif;

if ($division->double_booking):
	echo $this->Form->control('_options.double_booking', [
		'label' => __('Allow double-booking?'),
		'type' => 'checkbox',
		'checked' => true,
	]);
?>

<p><?= __('If this is checked, you will be allowed to schedule more than one game in a game slot.') ?></p>
<?php
else:
	echo $this->Form->hidden('_options.double_booking', ['value' => false]);
endif;
?>

</fieldset>

<?php
// TODOLATER: Implement a "back" button here. Will presumably need to track a new "prev_step" option for this, since the flow through pages can be fluid.
echo $this->Form->button(__('Next step'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

</div>
<div class="actions columns">
<?= $this->element('Divisions/actions', [
	'league' => $division->league,
	'division' => $division,
	'format' => 'list',
]) ?>
</div>
