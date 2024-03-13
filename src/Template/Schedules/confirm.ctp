<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

use Cake\I18n\FrozenDate;

$this->Html->addCrumb(__('Division'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Add Games'));
$this->Html->addCrumb(__('Confirm Selections'));
?>

<div class="schedules add">
	<p><?= __('The following information will be used to create your games:') ?></p>
	<h3><?= __('What') ?>:</h3>
	<p><?php
	echo $desc;
	if (!empty($division->_options->pool_id)) {
		echo __(' (pool {0})', collection($division->pools)->firstMatch(['id' => $division->_options->pool_id])->name);
	}
	?></p>
<?php
if (is_array($start_date)):
	// Seems that the asort algorithm will reverse the order of things that are equal.
	// We'll reverse the starting array, so that when asort re-reverses it, it will be
	// in the right order.
	// TODO: A more robust solution using usort
	$start_date = array_reverse($start_date, true);
	asort($start_date);
?>
	<h3><?= __('Rounds to be scheduled at') ?>:</h3>
	<ol>
<?php
	foreach ($start_date as $round => $date):
?>
		<li value="<?= $round ?>"><?= $this->Time->fulldatetime($date) ?></li>
<?php
	endforeach;
?>

	</ol>
<?php
else:
?>
	<h3><?= __('Start date') ?>:</h3>
	<p><?= $this->Time->fulldate(new FrozenDate($start_date)) ?></p>
<?php
endif;
?>

<?= $this->element('Schedules/exclude') ?>

	<h3><?= __('Publication') ?>:</h3>
	<p><?= __('Games will{0} be published.', $division->_options->publish ? '' : ' ' . __('NOT')) ?></p>

<?php
echo $this->Form->create($division, ['align' => 'horizontal']);
$division->_options->step = 'finalize';
echo $this->element('hidden', ['model' => '_options', 'fields' => $division->_options]);

echo $this->Form->button(__('Create games'), ['class' => 'btn-success']);
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
