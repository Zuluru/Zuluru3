<?php
use Cake\I18n\FrozenTime;

/**
 * @var int $min
 */

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb(__('Participation Statistics'));
?>

<div class="people participation">
	<h2><?= __('Participation Statistics') ?></h2>

<?php
echo $this->form->create(null, ['align' => 'horizontal']);
$year = FrozenTime::now()->year;
$years = array_combine(range($year, $min), range($year, $min));
echo $this->Form->control('start', [
	'label' => __('Include details starting in'),
	'options' => $years,
]);
echo $this->Form->control('end', [
	'label' => __('Up to and including'),
	'options' => $years,
]);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
