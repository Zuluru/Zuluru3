<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Category[] $categories
 * @var \App\Model\Entity\Affiliate[] $affiliates
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Categories'));
$this->Breadcrumbs->add(__('List'));

$types = Configure::read('options.category_types');
$multiple_types = (count($types) > 1);
?>

<div class="categories index">
	<h2><?= __('Categories') ?></h2>
	<div class="table-responsive">
	<?= $this->form->create(null) ?>
	<table class="table table-striped table-hover table-condensed sortable">
		<thead>
			<tr>
<?php
if ($multiple_types):
?>
			<th><?= __('Type') ?></th>
<?php
endif;
?>
			<th></th>
			<th><?= __('Name') ?></th>
			<th><?= __('Slug') ?></th>
			<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
$affiliate_id = $type = null;
foreach ($categories as $i => $category):
	$url = null;
	if ($category->type === 'Leagues') {
		$url = ['controller' => 'Events', 'action' => 'index', $category->slug];
	}

	if (count($affiliates) > 1 && $category->affiliate_id != $affiliate_id):
		$affiliate_id = $category->affiliate_id;
?>
			<tr>
				<th colspan="<?= 4 + $multiple_types ?>>">
					<h3 class="affiliate"><?= h($category->affiliate->name) ?></h3>
				</th>
			</tr>
<?php
	endif;
?>
			<tr>
<?php
	if ($multiple_types):
?>
				<td class="handle"><?php
					if ($category->type !== $type) {
						echo $types[$category->type];
						$type = $category->type;
					}
				?></td>
<?php
	endif;
?>
				<td class="handle"><?= $category->image_url ? $this->Html->image($category->image_url) : '' ?></td>
				<td class="handle"><?= h($category->name) ?></td>
				<td class="handle"><?= $url ? $this->Html->link($category->slug, $url) : h($category->slug) ?></td>
				<td class="actions"><?php
				echo $this->Form->hidden("$i.id", ['value' => $category->id]);
				echo $this->Form->hidden("$i.sort", ['value' => $category->sort]);
				echo $this->Html->iconLink('view_24.png',
					['action' => 'view', '?' => ['category' => $category->id]],
					['alt' => __('View'), 'title' => __('View')]);
				echo $this->Html->iconLink('edit_24.png',
					['action' => 'edit', '?' => ['category' => $category->id]],
					['alt' => __('Edit'), 'title' => __('Edit')]);
				echo $this->Form->iconPostLink('delete_24.png',
					['action' => 'delete', '?' => ['category' => $category->id]],
					['alt' => __('Delete'), 'title' => __('Delete')],
					['confirm' => __('Are you sure you want to delete this category?')]);
				?></td>
			</tr>

<?php
endforeach;
?>
		</tbody>
	</table>
<?php
	echo $this->Form->button(__('Save Changes'), ['class' => 'btn-success']);
	echo $this->Form->end();
?>
	</div>
</div>
<?php
if ($this->Authorize->can('add', \App\Controller\CategoriesController::class)):
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
