<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;

$this->Html->addCrumb(__('Registrations'));
$this->Html->addCrumb(__('Report'));
?>

<div class="registrations index">
	<h2><?= __('Registration Report') ?></h2>

<?php
echo $this->Form->create(false, ['align' => 'horizontal']);
?>

	<fieldset>
		<legend><?= __('Date Range') ?></legend>
<?php
// In January and February, default report range to last year
$now = FrozenDate::now();
$start = $now->startOfYear();
if ($now->month <= 2) {
	$start = $start->subYear();
}
echo $this->Form->input('start_date', [
	'type' => 'date',
	'value' => $start,
	'maxYear' => $now->year,
]);
echo $this->Form->input('end_date', [
	'type' => 'date',
	'value' => $start->endOfYear(),
	'maxYear' => $now->year,
]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
	<div id="RegistrationList" class="zuluru_pagination">

<?= $this->element('Registrations/report') ?>

	</div>
</div>
