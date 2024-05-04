<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Contact $contact
 * @var string[] $affiliates
 */

$this->Breadcrumbs->add(__('Contacts'));
if ($contact->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($contact->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="contacts form">
	<?= $this->Form->create($contact, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $contact->isNew() ? __('Create Contact') : __('Edit Contact') ?></legend>
<?php
echo $this->Form->i18nControls('name', [
	'help' => __('The name of your contact.'),
]);
echo $this->Form->control('email', [
	'help' => __('The email address for your contact. This will not be shown to users, only used to deliver messages.'),
]);
if ($contact->isNew()) {
	echo $this->Form->control('affiliate_id', [
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
<?php
$links = [$this->Html->link(__('List Contacts'), ['action' => 'index'], ['class' => $this->Bootstrap->navPillLinkClasses()])];
if (!$contact->isNew()) {
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['contact' => $contact->id]],
		['alt' => __('Delete'), 'title' => __('Delete Contact')],
		['confirm' => __('Are you sure you want to delete this contact?')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Contact')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>
