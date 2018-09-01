<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Categories'));
$this->Html->addCrumb(__('List'));
?>

<div class="categories index">
	<h2><?= __('Categories') ?></h2>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
			<th><?= __('Name') ?></th>
			<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
$affiliate_id = null;
foreach ($categories as $category):
	if (count($affiliates) > 1 && $category->affiliate_id != $affiliate_id):
		$affiliate_id = $category->affiliate_id;
?>
			<tr>
				<th colspan="2">
					<h3 class="affiliate"><?= h($category->affiliate->name) ?></h3>
				</th>
			</tr>
<?php
	endif;
?>
			<tr>
				<td><?= h($category->name) ?></td>
				<td class="actions"><?php
				echo $this->Html->iconLink('view_24.png',
					['action' => 'view', 'category' => $category->id],
					['alt' => __('View'), 'title' => __('View')]);
				echo $this->Html->iconLink('edit_24.png',
					['action' => 'edit', 'category' => $category->id],
					['alt' => __('Edit'), 'title' => __('Edit')]);
				echo $this->Form->iconPostLink('delete_24.png',
					['action' => 'delete', 'category' => $category->id],
					['alt' => __('Delete'), 'title' => __('Delete')],
					['confirm' => __('Are you sure you want to delete this category?')]);
				?></td>
			</tr>

<?php
endforeach;
?>
		</tbody>
	</table>
	</div>
</div>
<?php
if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Category')]));
?>
	</ul>
</div>
<?php
endif;
