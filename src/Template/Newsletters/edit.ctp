<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Newsletter $newsletter
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Newsletter'));
if ($newsletter->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($newsletter->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="newsletters form">
	<?= $this->Form->create($newsletter, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $newsletter->isNew() ? __('Create Newsletter') : __('Edit Newsletter') ?></legend>
<?php
	echo $this->Form->control('name', [
		'size' => 60,
		'help' => __('A short name for this newsletter, to be used as a heading in administrative reports.'),
	]);
	echo $this->Form->control('mailing_list_id', ['empty' => 'Select one:']);
	echo $this->Form->control('from_email', [
		'size' => 60,
		'help' => __('Email address that this newsletter should come from.'),
	]);
	echo $this->Form->control('to_email', [
		'size' => 60,
		'help' => __('Email address that this newsletter should be sent to, if different than the From address. If the "Personalize" box is checked, this is ignored.'),
	]);
	echo $this->Form->control('reply_to', [
		'size' => 60,
		'help' => __('Email address that replies to this newsletter should be sent to, if different than the From address.'),
	]);
	echo $this->Form->control('subject', [
		'size' => 60,
		'help' => __('Subject line for emailing this newsletter.'),
	]);
	echo $this->Form->control('text', [
		'cols' => 60,
		'rows' => 30,
		'help' => __('The full text of the newsletter.'),
		'class' => 'wysiwyg_newsletter',
	]);
	echo $this->Form->control('target', [
		'minYear' => Configure::read('options.year.event.min'),
		'maxYear' => Configure::read('options.year.event.max'),
		'looseYears' => true,
		'help' => __('Target date for sending this newsletter. For display purposes only; does not cause the newsletter to be sent on this date.'),
	]);
	echo $this->Form->control('delay', [
		'help' => __('Time (in minutes) between batches. Larger delays decrease the chance that sites like Hotmail will consider your email to be spam.'),
	]);
	echo $this->Form->control('batch_size', [
		'help' => __('Maximum number of newsletters to send in a single batch. Smaller batches decrease the chance that sites like Hotmail will consider your email to be spam.'),
	]);
	echo $this->Form->control('personalize', [
		'help' => __('Check this to personalize each email. This slows down the sending process and increases the amount of internet traffic your newsletter will generate.'),
	]);
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Newsletters'), ['action' => 'index']));
if (!$newsletter->isNew()) {
	echo $this->Html->tag('li', $this->Html->link(__('Delivery Report'), ['action' => 'delivery', 'newsletter' => $newsletter->id]));
	echo $this->Html->tag('li', $this->Html->iconLink('newsletter_send_32.png',
		['action' => 'send', 'newsletter' => $newsletter->id],
		['alt' => __('Send'), 'title' => __('Send')]));
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'newsletter' => $newsletter->id],
		['alt' => __('Delete'), 'title' => __('Delete Newsletter')],
		['confirm' => __('Are you sure you want to delete this newsletter?')]));
}
?>
	</ul>
</div>
