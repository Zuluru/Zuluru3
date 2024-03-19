<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MailingList $mailing_list
 */

use App\Controller\AppController;

$this->Breadcrumbs->add(__('Mailing List'));
$this->Breadcrumbs->add(h($mailing_list->name));
$this->Breadcrumbs->add(__('View'));
?>

<div class="mailingLists view">
	<h2><?= h($mailing_list->name) ?></h2>
	<dl class="dl-horizontal">
<?php
if (count($affiliates) > 1):
?>
		<dt><?= __('Affiliate') ?></dt>
		<dd><?= $this->Html->link($mailing_list->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $mailing_list->affiliate->id]) ?></dd>
<?php
endif;
?>
		<dt><?= __('Opt Out') ?></dt>
		<dd><?= $mailing_list->opt_out ? __('Yes') : __('No') ?></dd>
		<dt><?= __('Rule') ?></dt>
		<dd><pre><?= $mailing_list->rule ?></pre></dd>
	</dl>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Mailing Lists'), ['action' => 'index']));
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', 'mailing_list' => $mailing_list->id, 'return' => AppController::_return()],
	['alt' => __('Edit'), 'title' => __('Edit Mailing List')]));
echo $this->Html->tag('li', $this->Html->iconLink('email_32.png',
	['action' => 'preview', 'mailing_list' => $mailing_list->id],
	['alt' => __('Preview'), 'title' => __('Preview')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'mailing_list' => $mailing_list->id],
	['alt' => __('Delete'), 'title' => __('Delete Mailing List')],
	['confirm' => __('Are you sure you want to delete this mailingList?')]));
echo $this->Html->tag('li', $this->Html->iconLink('mailing_list_add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Mailing List')]));
?>
	</ul>
</div>
<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('Related Newsletters') ?></h4>
<?php
if (!empty($mailing_list->newsletters)):
?>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Name') ?></th>
					<th><?= __('Target') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($mailing_list->newsletters as $newsletter):
?>
				<tr>
					<td><?= h($newsletter->name) ?></td>
					<td><?= $this->Time->date($newsletter->target) ?></td>
					<td class="actions"><?php
					echo $this->Html->iconLink('view_24.png',
						['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => $newsletter->id],
						['alt' => __('Preview'), 'title' => __('Preview')]);
					echo $this->Html->iconLink('edit_24.png',
						['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => $newsletter->id, 'return' => AppController::_return()],
						['alt' => __('Edit'), 'title' => __('Edit')]);
					echo $this->Html->link(__('Delivery Report'), ['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => $newsletter->id]);
					echo $this->Html->iconLink('newsletter_send_24.png',
						['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => $newsletter->id],
						['alt' => __('Send'), 'title' => __('Send')]);
					echo $this->Form->iconPostLink('delete_24.png',
						['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => $newsletter->id, 'return' => AppController::_return()],
						['alt' => __('Delete'), 'title' => __('Delete')],
						['confirm' => __('Are you sure you want to delete this newsletter?')]);
					?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
		</div>
<?php
endif;
?>
	</div>
</div>
