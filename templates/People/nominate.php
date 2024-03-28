<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge[] $badges
 */

$this->Breadcrumbs->add(__('Badges'));
$this->Breadcrumbs->add(__('Nominate'));
?>

<div class="badges form">
<?= $this->Form->create(null, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Nominate for a Badge') ?></legend>
<?php
	echo $this->Form->control('badge', [
			'options' => $badges,
			'empty' => __('Select one:'),
	]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Continue'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
