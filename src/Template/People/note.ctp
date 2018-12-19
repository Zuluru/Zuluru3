<?php
$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Note'));
if ($note->isNew()) {
	$this->Html->addCrumb(__('Add'));
} else {
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="people form">
<h2><?= __('Person Note') . ': ' . $person->full_name ?></h2>
<?php
echo $this->Form->create($note, ['align' => 'horizontal']);
$options = [
	VISIBILITY_PRIVATE => __('Only I will be able to see this'),
];
if ($this->Authorize->getIdentity()->isManagerOf($person)) {
	$options[VISIBILITY_ADMIN] = __('Administrators only');
}
echo $this->Form->input('visibility', [
	'options' => $options,
	'hide_single' => true,
]);
echo $this->Form->input('note', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
