<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MailingList $mailing_list
 */

$this->Breadcrumbs->add(__('Mailing List'));
if ($mailing_list->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($mailing_list->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="mailingLists form">
	<?= $this->Form->create($mailing_list, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $mailing_list->isNew() ? __('Create Mailing List') : __('Edit Mailing List') ?></legend>
<?php
	echo $this->Form->i18nControls('name', [
		'size' => 60,
	]);
	if ($mailing_list->isNew()) {
		echo $this->Form->control('affiliate_id', [
			'options' => $affiliates,
			'hide_single' => true,
			'empty' => '---',
		]);
	}
	echo $this->Form->control('opt_out', [
		'help' => __('Check this to allow recipients to unsubscribe from this mailing list. Be sure that your local privacy laws allow you to uncheck this before doing so.'),
	]);
	echo $this->Form->control('rule', [
		'cols' => 70,
		'help' => $this->Html->para(null, __('Rules that must be passed to include a person on this mailing list.') .
			' ' . $this->Html->help(['action' => 'rules', 'rules'])),
	]);
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Mailing Lists'), ['action' => 'index']));
if (!$mailing_list->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['mailing_list' => $mailing_list->id]],
		['alt' => __('Delete'), 'title' => __('Delete Mailing List')],
		['confirm' => __('Are you sure you want to delete this mailingList?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('mailing_list_add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Mailing List')]));
}
?>
	</ul>
</div>
