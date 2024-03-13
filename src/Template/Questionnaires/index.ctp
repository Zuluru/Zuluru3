<?php
$this->Html->addCrumb(__('Questionnaires'));
$this->Html->addCrumb(__('List'));
?>

<div class="questionnaires index">
	<h2><?= $active ? __('Questionnaires List') : __('Deactivated Questionnaires List') ?></h2>
	<p><?= $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]) ?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('name') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$affiliate_id = null;
foreach ($questionnaires as $questionnaire):
	if (count($affiliates) > 1 && $questionnaire->affiliate_id != $affiliate_id):
		$affiliate_id = $questionnaire->affiliate_id;
?>
				<tr>
					<th colspan="2">
						<h3 class="affiliate"><?= h($questionnaire->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
	endif;
?>
				<tr>
					<td><?= h($questionnaire->name) ?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png',
							['action' => 'view', 'questionnaire' => $questionnaire->id],
							['alt' => __('Preview'), 'title' => __('Preview')]);
						echo $this->Html->iconLink('edit_24.png',
							['action' => 'edit', 'questionnaire' => $questionnaire->id],
							['alt' => __('Edit'), 'title' => __('Edit')]);
						echo $this->Form->iconPostLink('delete_24.png',
							['action' => 'delete', 'questionnaire' => $questionnaire->id],
							['alt' => __('Delete'), 'title' => __('Delete')],
							['confirm' => __('Are you sure you want to delete this questionnaire?')]);
						if ($questionnaire->active) {
							echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', 'questionnaire' => $questionnaire->id]]);
						} else {
							echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', 'questionnaire' => $questionnaire->id]]);
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
	['alt' => __('Add'), 'title' => __('Add Questionnaire')]));
if ($this->getRequest()->getParam('action') == 'index') {
	echo $this->Html->tag('li', $this->Html->link(__('Deactivated'), ['action' => 'deactivated']));
} else {
	echo $this->Html->tag('li', $this->Html->link(__('List'), ['action' => 'index']));
}
?>
	</ul>
</div>
