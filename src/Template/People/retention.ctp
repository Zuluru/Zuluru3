<?php
use Cake\I18n\FrozenTime;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb(__('Membership Retention Statistics'));
?>

<div class="people retention">
	<h2><?= __('Membership Retention Statistics') ?></h2>

<?php
echo $this->Form->create(null, ['align' => 'horizontal']);
$year = FrozenTime::now()->year;
$years = array_combine(range($year, $min->year), range($year, $min->year));
echo $this->Form->input('start', [
    'label' => __('Include details starting in'),
    'options' => $years,
]);
echo $this->Form->input('end', [
    'label' => __('Up to and including'),
    'options' => $years,
]);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
