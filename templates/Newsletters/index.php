<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Newsletter[] $newsletters
 */

$this->Breadcrumbs->add(__('Newsletters'));
$this->Breadcrumbs->add(__('List'));
?>

<div class="newsletters index">
	<h2><?= $current ? __('Recent and Upcoming Newsletters List') : __('Complete Newsletters List') ?></h2>
	<p><?= $this->Paginator->counter(
		__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	) ?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('name') ?></th>
					<th><?= $this->Paginator->sort('mailing_list_id') ?></th>
					<th><?= $this->Paginator->sort('subject') ?></th>
					<th><?= $this->Paginator->sort('target') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$affiliate_id = null;
foreach ($newsletters as $newsletter):
	if (count($affiliates) > 1 && $newsletter->mailing_list->affiliate_id != $affiliate_id):
		$affiliate_id = $newsletter->mailing_list->affiliate_id;
?>
				<tr>
					<th colspan="5">
						<h3 class="affiliate"><?= h($newsletter->mailing_list->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
	endif;
?>
				<tr>
					<td><?= h($newsletter->name) ?></td>
					<td><?= h($newsletter->mailing_list->name) ?></td>
					<td><?= h($newsletter->subject) ?></td>
					<td><?= $this->Time->date($newsletter->target) ?></td>
					<td class="actions"><?php
					echo $this->Html->iconLink('view_24.png',
						['action' => 'view', '?' => ['newsletter' => $newsletter->id]],
						['alt' => __('Preview'), 'title' => __('Preview')]);
					echo $this->Html->iconLink('edit_24.png',
						['action' => 'edit', '?' => ['newsletter' => $newsletter->id]],
						['alt' => __('Edit'), 'title' => __('Edit')]);
					echo $this->Html->link(__('Delivery Report'), ['action' => 'delivery', '?' => ['newsletter' => $newsletter->id]]);
					echo $this->Html->iconLink('newsletter_send_24.png',
						['action' => 'send', '?' => ['newsletter' => $newsletter->id]],
						['alt' => __('Send'), 'title' => __('Send')]);
					echo $this->Form->iconPostLink('delete_24.png',
						['action' => 'delete', '?' => ['newsletter' => $newsletter->id]],
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
	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('newsletter_add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Newsletter')]));
echo $this->Html->tag('li', $this->Html->link($current ? __('All Newsletters') : __('Upcoming Newsletters'),
	['action' => $current ? 'past' : 'index']));
?>
	</ul>
</div>
