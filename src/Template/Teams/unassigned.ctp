<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team[] $teams
 */

$this->Html->addCrumb(__('Teams'));
$this->Html->addCrumb(__('Unassigned Teams'));
?>

<div class="teams index">
	<h2><?= __('Unassigned Teams') ?></h2>
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
foreach ($teams as $team):
?>
				<tr>
					<td><?= $this->element('Teams/block', compact('team')) ?></td>
					<td class="actions"><?= $this->element('Teams/actions', ['team' => $team, 'format' => 'links']) ?></td>
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
