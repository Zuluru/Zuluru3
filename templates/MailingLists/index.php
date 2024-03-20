<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MailingList[] $mailingLists
 */

$this->Breadcrumbs->add(__('Mailing Lists'));
$this->Breadcrumbs->add(__('List'));
?>

<div class="mailingLists index">
	<h2><?= __('Mailing Lists') ?></h2>
	<p><?= $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]) ?></p>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('name') ?></th>
				<th><?= $this->Paginator->sort('opt_out') ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($mailingLists as $mailing_list):
?>
			<tr>
				<td><?= h($mailing_list->name) ?></td>
				<td><?= $mailing_list->opt_out ? __('Yes') : __('No') ?></td>
				<td class="actions"><?php
				echo $this->Html->iconLink('view_24.png',
					['action' => 'view', '?' => ['mailing_list' => $mailing_list->id]],
					['alt' => __('View'), 'title' => __('View')]);
				echo $this->Html->iconLink('edit_24.png',
					['action' => 'edit', '?' => ['mailing_list' => $mailing_list->id]],
					['alt' => __('Edit'), 'title' => __('Edit')]);
				echo $this->Html->iconLink('email_24.png',
					['action' => 'preview', '?' => ['mailing_list' => $mailing_list->id]],
					['alt' => __('Preview'), 'title' => __('Preview')]);
				echo $this->Form->iconPostLink('delete_24.png',
					['action' => 'delete', '?' => ['mailing_list' => $mailing_list->id]],
					['alt' => __('Delete'), 'title' => __('Delete')],
					['confirm' => __('Are you sure you want to delete this mailingList?')]);
				?></td>
			</tr>

<?php
endforeach;
?>
		</tbody>
	</table>
	</div>
	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>
</div>
<?php
if ($this->Authorize->can('add', \App\Controller\MailingListsController::class)):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('mailing_list_add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Mailing List')]));
?>
	</ul>
</div>
<?php
endif;
