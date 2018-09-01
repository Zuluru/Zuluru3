<?php
$this->Html->addCrumb(__('Categories'));
$this->Html->addCrumb(h($category->name));
$this->Html->addCrumb(__('View'));
?>

<div class="categories view">
	<h2><?= h($category->name) ?></h2>
	<dl class="dl-horizontal">
<?php
if (count($affiliates) > 1):
?>
		<dt><?= __('Affiliate') ?></dt>
		<dd><?= $this->Html->link($category->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $category->affiliate->id]) ?></dd>
<?php
endif;
?>
	</dl>
</div>
<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('Related Tasks') ?></h4>
<?php
if (!empty($category->tasks)):
?>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Name') ?></th>
					<th><?= __('Reporting To') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($category->tasks as $task):
?>
				<tr>
					<td><?= h($task['name']) ?></td>
					<td><?= $this->element('People/block', ['person' => $task->person]) ?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png',
							['controller' => 'Tasks', 'action' => 'view', 'task' => $task->id],
							['alt' => __('View'), 'title' => __('View')]);
						echo $this->Html->iconLink('edit_24.png',
							['controller' => 'Tasks', 'action' => 'edit', 'task' => $task->id],
							['alt' => __('Edit'), 'title' => __('Edit')]);
						echo $this->Form->iconPostLink('delete_24.png',
							['controller' => 'Tasks', 'action' => 'delete', 'task' => $task->id],
							['alt' => __('Delete'), 'title' => __('Delete')],
							['confirm' => __('Are you sure you want to delete this task?')]);
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

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('view_32.png',
	['action' => 'index'],
	['alt' => __('List'), 'title' => __('List Categories')]));
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', 'category' => $category->id],
	['alt' => __('Edit'), 'title' => __('Edit Category')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'category' => $category->id],
	['alt' => __('Delete'), 'title' => __('Delete Category')],
	['confirm' => __('Are you sure you want to delete this category?')]));
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Category')]));
?>
	</ul>
</div>
