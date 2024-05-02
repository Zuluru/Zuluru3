<?php
/**
 * @var \App\View\AppView $this
 * @var int $min
 */

use Cake\I18n\FrozenTime;

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add(__('Membership Retention Statistics'));
?>

<div class="people retention">
	<h2><?= __('Membership Retention Statistics') ?></h2>

<?php
echo $this->Form->create(null, ['align' => 'horizontal']);
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
