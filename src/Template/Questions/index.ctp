<?php
$this->Html->addCrumb(__('Questions'));
$this->Html->addCrumb(__('List'));
?>

<div class="questions index">
	<h2><?= $active ? __('Questions List') : __('Deactivated Questions List') ?></h2>
	<p><?= $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]) ?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('question') ?></th>
					<th><?= $this->Paginator->sort('type') ?></th>
					<th><?= $this->Paginator->sort('anonymous') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$affiliate_id = null;
foreach ($questions as $question):
	if (count($affiliates) > 1 && $question->affiliate_id != $affiliate_id):
		$affiliate_id = $question->affiliate_id;
?>
				<tr>
					<th colspan="4">
						<h3 class="affiliate"><?= h($question->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
endif;
?>
				<tr>
					<td><?= $question->question ?></td>
					<td><?= $question->type ?></td>
					<td><?= $question->anonymous ? __('Yes') : __('No') ?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png',
							['action' => 'view', 'question' => $question->id],
							['alt' => __('Preview'), 'title' => __('Preview')]);
						echo $this->Html->iconLink('edit_24.png',
							['action' => 'edit', 'question' => $question->id],
							['alt' => __('Edit'), 'title' => __('Edit')]);
						echo $this->Form->iconPostLink('delete_24.png',
							['action' => 'delete', 'question' => $question->id],
							['alt' => __('Delete'), 'title' => __('Delete')],
							['confirm' => __('Are you sure you want to delete this question?')]);
						if ($question->active) {
							echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', 'question' => $question->id]]);
						} else {
							echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', 'question' => $question->id]]);
						}
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
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Question')]));
if ($this->request->action == 'index') {
	echo $this->Html->tag('li', $this->Html->link(__('Deactivated'), ['action' => 'deactivated']));
} else {
	echo $this->Html->tag('li', $this->Html->link(__('List'), ['action' => 'index']));
}
?>
	</ul>
</div>
