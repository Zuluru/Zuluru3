<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UploadType $upload_type
 * @var string[] $affiliates
 */

$this->Breadcrumbs->add(__('Upload Type'));
$this->Breadcrumbs->add(h($upload_type->name));
$this->Breadcrumbs->add(__('View'));
?>

<div class="upload_types view">
	<h2><?= h($upload_type->name) ?></h2>
<?php
if (count($affiliates) > 1):
?>
	<dl class="row">
		<dt class="col-sm-3 text-end"><?= __('Affiliate') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Html->link($upload_type->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $upload_type->affiliate->id]]) ?></dd>
	</dl>
<?php
endif;
?>
</div>

<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Html->iconLink('view_32.png',
		['action' => 'index'],
		['alt' => __('List'), 'title' => __('List Upload Types')]
	),
	$this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['type' => $upload_type->id]],
		['alt' => __('Edit'), 'title' => __('Edit Upload Type')]
	),
	$this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['type' => $upload_type->id]],
		['alt' => __('Delete'), 'title' => __('Delete Upload Type')],
		['confirm' => __('Are you sure you want to delete this uploadType?')]
	),
	$this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Upload Type')]
	),
]);
?>
</div>

<div class="related">
	<h3><?= __('Documents') ?></h3>
<?php
if (!empty($upload_type->uploads)):
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Document') ?></th>
					<th><?= __('Valid From') ?></th>
					<th><?= __('Valid Until') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($upload_type->uploads as $document):
?>
				<tr>
					<td><?= $this->element('People/block', ['person' => $document->person]) ?></td>
<?php
		if ($document['approved']):
?>
					<td><?= $this->Time->date($document['valid_from']) ?></td>
					<td><?= $this->Time->date($document['valid_until']) ?></td>
<?php
		else:
?>
					<td colspan="2" class="highlight-message"><?= __('Unapproved') ?></td>
<?php
		endif;
?>
					<td class="actions"><?php
						echo $this->Html->link(__('View'), ['controller' => 'People', 'action' => 'document', '?' => ['document' => $document->id]], ['target' => 'preview']);
						if ($document['approved']) {
							echo $this->Html->link(__('Edit'), ['controller' => 'People', 'action' => 'edit_document', '?' => ['document' => $document->id]]);
						} else {
							echo $this->Html->link(__('Approve'), ['controller' => 'People', 'action' => 'approve_document', '?' => ['document' => $document->id]]);
						}
						echo $this->Jquery->ajaxLink($this->Html->iconImg('delete_24.png', ['alt' => __('Delete'), 'title' => __('Delete')]), [
							'url' => ['controller' => 'People', 'action' => 'delete_document', '?' => ['document' => $document->id]],
							'confirm' => __('Are you sure you want to delete this document?'),
							'disposition' => 'remove_closest',
							'selector' => 'tr',
						], [
							'escape' => false,
						]);
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
