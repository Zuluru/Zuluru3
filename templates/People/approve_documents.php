<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Upload $documents
 */

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add(__('Approve Documents'));
?>

<div class="people documents">
	<h2><?= __('Approve Documents') ?></h2>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<tbody>
<?php
foreach ($documents as $document):
?>
				<tr>
					<td><?= $this->element('People/block', ['person' => $document->person]) ?></td>
					<td><?= $document->upload_type->name ?></td>
					<td class="actions"><?php
					echo $this->Html->link(__('View'),
						['action' => 'document', '?' => ['document' => $document->id]],
						['target' => 'preview']
					);
					echo $this->Html->link(__('Approve'),
						['action' => 'approve_document', '?' => ['document' => $document->id]]
					);
					echo $this->Jquery->ajaxLink(__('Delete'), [
						'url' => ['action' => 'delete_document', '?' => ['document' => $document->id]],
						'dialog' => 'document_comment_div',
						'disposition' => 'remove_closest',
						'selector' => 'tr',
					]);
					?></td>
				</tr>
<?php
endforeach;
?>
			</tbody>
		</table>
	</div>
</div>

<?= $this->element('People/document_div');
