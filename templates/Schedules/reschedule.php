<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var FrozenDate $date
 * @var FrozenDate[] $dates
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;

$this->Breadcrumbs->add(__('Division'));
$this->Breadcrumbs->add($division->full_league_name);
$this->Breadcrumbs->add(__('Reschedule'));
?>

<div class="schedules reschedule">
<h2><?= __('Reschedule') ?></h2>

<p><?= __('You are about to reschedule {0} games originally scheduled for {1}.',
	count($division->games), $this->Time->fulldate(new FrozenDate($date))) ?></p>

<?php
echo $this->Form->create($division, ['align' => 'horizontal']);

echo $this->Form->control('new_date', [
	'label' => __('Reschedule games to'),
	// We have an array like 0 => date, and need SQL => readable
	'options' => array_combine(
		array_map(function ($date) { return $date->toDateString(); }, $dates),
		array_map([$this->Time, 'fulldate'], $dates)
	),
]);

echo $this->Form->control('publish', [
	'label' => __('Publish rescheduled games for player viewing?'),
	'type' => 'checkbox',
]);

echo $this->Html->para('warning-message', __('Note that no attempt is made to preserve {0} or time assignments; game slots will be assigned as per the normal algorithms.', Configure::read('UI.field')));

echo $this->Form->button(__('Continue'), ['class' => 'btn-success']);
echo $this->Form->end();
$confirm = __('Are you sure you want to reschedule these games? This cannot be undone.');
$this->Html->scriptBlock("zjQuery(':submit').bind('click', function (event) { return confirm('$confirm'); return false; })", ['buffer' => true]);
?>

</div>
<div class="actions columns">
<?= $this->element('Divisions/actions', [
	'league' => $division->league,
	'division' => $division,
	'format' => 'list',
]) ?>
</div>
