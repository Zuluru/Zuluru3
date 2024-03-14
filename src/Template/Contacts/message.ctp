<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Contact $contact
 */

$this->Html->addCrumb(__('Contacts'));
$this->Html->addCrumb(__('Message'));
if (isset($contact)) {
	$this->Html->addCrumb($contact->name);
}
?>

<div class="contacts form">
	<?= $this->Form->create($message, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Message Details') ?></legend>
<?php
if (isset($contacts)) {
	echo $this->Form->control('contact_id', [
		'label' => __('To'),
		'options' => $contacts,
		'empty' => '---',
	]);
} else {
	echo $this->Form->control('To', [
		'size' => 60,
		'value' => $contact->name,
		'disabled' => true,
	]);
	echo $this->Form->hidden('contact_id', ['value' => $contact->id]);
}
echo $this->Form->control('subject', ['size' => 60]);
echo $this->Form->control('message', ['rows' => 6, 'cols' => 60]);
echo $this->Form->control('cc', [
	'label' => __('Send a copy to your email address'),
	'type' => 'checkbox',
]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
