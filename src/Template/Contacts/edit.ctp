<?php
$this->Html->addCrumb(__('Contacts'));
if ($contact->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($contact->name));
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="contacts form">
	<?= $this->Form->create($contact, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $contact->isNew() ? __('Create Contact') : __('Edit Contact') ?></legend>
<?php
echo $this->Form->input('name', [
	'help' => __('The name of your contact.'),
]);
echo $this->Form->input('email', [
	'help' => __('The email address for your contact. This will not be shown to users, only used to deliver messages.'),
]);
if ($contact->isNew()) {
	echo $this->Form->input('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Contacts'), ['action' => 'index']));
if (!$contact->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'contact' => $contact->id],
		['alt' => __('Delete'), 'title' => __('Delete Contact')],
		['confirm' => __('Are you sure you want to delete this contact?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Contact')]));
}
?>
	</ul>
</div>
