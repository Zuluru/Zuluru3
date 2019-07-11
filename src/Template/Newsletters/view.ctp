<?php
use App\Controller\AppController;

$this->Html->addCrumb(__('Newsletter'));
$this->Html->addCrumb(h($newsletter->name));
$this->Html->addCrumb(__('Preview'));
?>

<div class="newsletters view">
	<h2><?= h($newsletter->name) . __(' ({0})', __('Preview')) ?></h2>
	<dl class="dl-horizontal">
		<dt><?= __('Subject') ?></dt>
		<dd><?= h($newsletter->subject) ?></dd>
		<dt><?= __('Mailing List') ?></dt>
		<dd><?= $this->Html->link($newsletter->mailing_list->name, ['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => $newsletter->mailing_list->id]) ?></dd>
		<dt><?= __('Target') ?></dt>
		<dd><?= $this->Time->date($newsletter->target) ?></dd>
<?php
if (!empty($newsletter->from_email)):
?>
		<dt><?= __('From') ?></dt>
		<dd><?= h($newsletter->from_email) ?></dd>
<?php
endif;

if (!empty($newsletter->to_email)):
?>
		<dt><?= __('To') ?></dt>
		<dd><?= h($newsletter->to_email) ?></dd>
<?php
endif;

if (!empty($newsletter->reply_to)):
?>
		<dt><?= __('Reply To') ?></dt>
		<dd><?= h($newsletter->reply_to) ?></dd>
<?php
endif;
?>
		<dt><?= __('Delay') ?></dt>
		<dd><?= $this->Number->format($newsletter->delay) . ' ' . __('minutes') ?></dd>
		<dt><?= __('Batch Size') ?></dt>
		<dd><?= $this->Number->format($newsletter->batch_size) ?></dd>
		<dt><?= __('Personalize') ?></dt>
		<dd><?= $newsletter->personalize ? __('Yes') : __('No') ?></dd>
		<dt><?= __('Text') ?></dt>
		<dd><?= $newsletter->text ?></dd>
	</dl>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Delivery Report'),
	['action' => 'delivery', 'newsletter' => $newsletter->id]));
echo $this->Html->tag('li', $this->Html->iconLink('newsletter_send_32.png',
	['action' => 'send', 'newsletter' => $newsletter->id],
	['alt' => __('Send'), 'title' => __('Send')]));
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', 'newsletter' => $newsletter->id, 'return' => AppController::_return()],
	['alt' => __('Edit'), 'title' => __('Edit Newsletter')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'newsletter' => $newsletter->id],
	['alt' => __('Delete'), 'title' => __('Delete Newsletter')],
	['confirm' => __('Are you sure you want to delete this newsletter?')]));
echo $this->Html->tag('li', $this->Html->iconLink('view_32.png',
	['action' => 'index'],
	['alt' => __('List'), 'title' => __('List Newsletters')]));
echo $this->Html->tag('li', $this->Html->iconLink('newsletter_add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Newsletter')]));
?>
	</ul>
</div>
