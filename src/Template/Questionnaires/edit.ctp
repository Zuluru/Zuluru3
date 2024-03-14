<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Questionnaire $questionnaire
 */

$this->Breadcrumbs->add(__('Questionnaire'));
if ($questionnaire->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($questionnaire->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="questionnaires form">
	<?= $this->Form->create($questionnaire, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $questionnaire->isNew() ? __('Create Questionnaire') : __('Edit Questionnaire') ?></legend>
<?php
echo $this->Form->control('name', ['size' => 60]);
if ($questionnaire->isNew()) {
	echo $this->Form->control('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
} else {
	echo $this->Form->control('active');
}
?>
	</fieldset>
<?php
if (!$questionnaire->isNew()):
?>
	<fieldset>
		<legend><?= __('Questions') ?></legend>
<?php
	echo $this->element('Questionnaires/edit', ['questionnaire' => $questionnaire]);
?>
	</fieldset>
<?php
endif;
?>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Questionnaires'), ['action' => 'index']));
if (!$questionnaire->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'questionnaire' => $questionnaire->id],
		['alt' => __('Delete'), 'title' => __('Delete Questionnaire')],
		['confirm' => __('Are you sure you want to delete this questionnaire?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Questionnaire')]));
}
?>
	</ul>
</div>
