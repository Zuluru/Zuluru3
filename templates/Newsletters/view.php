<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Newsletter $newsletter
 */

use App\Controller\AppController;

$this->Breadcrumbs->add(__('Newsletter'));
$this->Breadcrumbs->add(h($newsletter->name));
$this->Breadcrumbs->add(__('Preview'));
?>

<div class="newsletters view">
	<h2><?= h($newsletter->name) . __(' ({0})', __('Preview')) ?></h2>
	<dl class="row">
		<dt class="col-sm-2 text-end"><?= __('Subject') ?></dt>
		<dd class="col-sm-10 mb-0"><?= h($newsletter->subject) ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Mailing List') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Html->link($newsletter->mailing_list->name, ['controller' => 'MailingLists', 'action' => 'view', '?' => ['mailing_list' => $newsletter->mailing_list->id]]) ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Target') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Time->date($newsletter->target) ?></dd>
<?php
if (!empty($newsletter->from_email)):
?>
		<dt class="col-sm-2 text-end"><?= __('From') ?></dt>
		<dd class="col-sm-10 mb-0"><?= h($newsletter->from_email) ?></dd>
<?php
endif;

if (!empty($newsletter->to_email)):
?>
		<dt class="col-sm-2 text-end"><?= __('To') ?></dt>
		<dd class="col-sm-10 mb-0"><?= h($newsletter->to_email) ?></dd>
<?php
endif;

if (!empty($newsletter->reply_to)):
?>
		<dt class="col-sm-2 text-end"><?= __('Reply To') ?></dt>
		<dd class="col-sm-10 mb-0"><?= h($newsletter->reply_to) ?></dd>
<?php
endif;
?>
		<dt class="col-sm-2 text-end"><?= __('Delay') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Number->format($newsletter->delay) . ' ' . __('minutes') ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Batch Size') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Number->format($newsletter->batch_size) ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Personalize') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $newsletter->personalize ? __('Yes') : __('No') ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Text') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $newsletter->text ?></dd>
	</dl>
</div>
<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Html->link(__('Delivery Report'),
		['action' => 'delivery', '?' => ['newsletter' => $newsletter->id]],
		['class' => $this->Bootstrap->navPillLinkClasses()]
	),
	$this->Html->iconLink('newsletter_send_32.png',
		['action' => 'send', '?' => ['newsletter' => $newsletter->id]],
		['alt' => __('Send'), 'title' => __('Send')]
	),
	$this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['newsletter' => $newsletter->id, 'return' => AppController::_return()]],
		['alt' => __('Edit'), 'title' => __('Edit Newsletter')]
	),
	$this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['newsletter' => $newsletter->id]],
		['alt' => __('Delete'), 'title' => __('Delete Newsletter')],
		['confirm' => __('Are you sure you want to delete this newsletter?')]
	),
	$this->Html->iconLink('view_32.png',
		['action' => 'index'],
		['alt' => __('List'), 'title' => __('List Newsletters')]
	),
	$this->Html->iconLink('newsletter_add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Newsletter')]
	),
]);
?>
</div>
