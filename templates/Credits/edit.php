<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Credit $credit
 */

$this->Breadcrumbs->add(__('Credits'));
if ($credit->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(__('Edit'));
}
$this->Breadcrumbs->add($credit->person->full_name);
?>

<div class="credits form">
	<?= $this->Form->create($credit, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= ($credit->isNew() ? __('Create Credit') : __('Edit Credit')) . ': ' . $credit->person->full_name ?></legend>
<?php
echo $this->Form->control('amount', [
	'help' => $credit->payment_id ? __('Editing this amount will also change the amount of the related refund.') : false,
]);
if ($credit->isNew()) {
	echo $this->Form->hidden('person_id', ['value' => $credit->person->id]);
} else {
	echo $this->Form->control('amount_used');
}
echo $this->Form->control('notes', [
	'help' => $credit->isNew() ? __('These notes will be included in the notification email to the recipient.') : false,
]);
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Credits'), ['action' => 'index']));
if (!$credit->isNew()) {
	$confirm = __('Are you sure you want to delete this credit?');
	if ($credit->payment_id) {
		$confirm .= "\n\n" . __('Doing so will also delete the related refund, but will NOT change the payment status of the registration.');
	}
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['credit' => $credit->id]],
		['alt' => __('Delete'), 'title' => __('Delete Credit')],
		['confirm' => $confirm]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Credit')]));
}
?>
	</ul>
</div>
