<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Question $question
 * @var string[] $affiliates
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Question'));
$this->Breadcrumbs->add(__('Add'));
?>

<div class="questions form">
	<?= $this->Form->create($question, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Create Question') ?></legend>
<?php
echo $this->Form->i18nControls('name', [
	'size' => 60,
	'help' => __('A short name for this question, to be used as a heading in administrative reports.'),
]);
echo $this->Form->control('affiliate_id', [
	'options' => $affiliates,
	'hide_single' => true,
	'empty' => '---',
]);
echo $this->Form->i18nControls('question', [
	'cols' => 60,
	'help' => __('The full text of the question, to be shown to users.'),
	'class' => 'wysiwyg_advanced',
]);
echo $this->Form->control('type', [
	'options' => Configure::read('options.question_types'),
	'empty' => true,
]);
echo $this->Form->control('anonymous', [
	'label' => __('Anonymous results'),
	'help' => __('Will responses to this question be kept anonymous?'),
]);
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Html->link(__('List Questions'), ['action' => 'index'], ['class' => $this->Bootstrap->navPillLinkClasses()]),
]);
?>
</div>
