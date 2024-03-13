<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Question $question
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Question'));
$this->Html->addCrumb(__('Add'));
?>

<div class="questions form">
	<?= $this->Form->create($question, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Create Question') ?></legend>
<?php
echo $this->Form->input('name', [
	'size' => 60,
	'help' => __('A short name for this question, to be used as a heading in administrative reports.'),
]);
echo $this->Form->input('affiliate_id', [
	'options' => $affiliates,
	'hide_single' => true,
	'empty' => '---',
]);
echo $this->Form->input('question', [
	'cols' => 60,
	'help' => __('The full text of the question, to be shown to users.'),
	'class' => 'wysiwyg_advanced',
]);
echo $this->Form->input('type', [
	'options' => Configure::read('options.question_types'),
	'empty' => true,
]);
echo $this->Form->input('anonymous', [
	'label' => __('Anonymous results'),
	'help' => __('Will responses to this question be kept anonymous?'),
]);
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Questions'), ['action' => 'index']));
?>
	</ul>
</div>
