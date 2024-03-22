<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Holiday[] $holidays
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Holidays'));
$this->Breadcrumbs->add(__('List'));
?>

<div class="holidays index">
	<h2><?= __('Holidays') ?></h2>
	<p><?= $this->Paginator->counter(
		__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	) ?></p>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('date') ?></th>
				<th><?= $this->Paginator->sort('name') ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
$affiliate_id = null;
foreach ($holidays as $holiday):
	if (count($affiliates) > 1 && $holiday->affiliate_id != $affiliate_id):
		$affiliate_id = $holiday->affiliate_id;
?>
			<tr>
				<th colspan="3">
					<h3 class="affiliate"><?= h($holiday->affiliate->name) ?></h3>
				</th>
			</tr>
<?php
	endif;
?>
			<tr>
				<td><?= $this->Time->date($holiday->date) ?></td>
				<td><?= h($holiday->name) ?></td>
				<td class="actions"><?php
				echo $this->Html->iconLink('edit_24.png',
					['action' => 'edit', '?' => ['holiday' => $holiday->id]],
					['alt' => __('Edit'), 'title' => __('Edit')]);
				echo $this->Form->iconPostLink('delete_24.png',
					['action' => 'delete', '?' => ['holiday' => $holiday->id]],
					['alt' => __('Delete'), 'title' => __('Delete')],
					['confirm' => __('Are you sure you want to delete this holiday?')]);
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
	['alt' => __('Add'), 'title' => __('Add Holiday')]));
?>
	</ul>
</div>
