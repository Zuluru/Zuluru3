<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add($person->full_name);
$this->Breadcrumbs->add(__('Note'));
if ($note->isNew()) {
	$this->Breadcrumbs->add(__('Add'));
} else {
	$this->Breadcrumbs->add(__('Edit'));
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
echo $this->Form->control('visibility', [
	'options' => $options,
	'hide_single' => true,
]);
echo $this->Form->control('note', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
