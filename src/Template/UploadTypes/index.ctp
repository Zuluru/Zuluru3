<?php
$this->Html->addCrumb(__('Upload Types'));
$this->Html->addCrumb(__('List'));
?>

<div class="upload_types index">
	<h2><?= __('Upload Types') ?></h2>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Id') ?></th>
					<th><?= __('Name') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$affiliate_id = null;
foreach ($uploadTypes as $upload_type):
	if (count($affiliates) > 1 && $upload_type->affiliate_id != $affiliate_id):
		$affiliate_id = $upload_type->affiliate_id;
?>
				<tr>
					<th colspan="3">
						<h3 class="affiliate"><?= h($upload_type->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
	endif;
?>
				<tr>
					<td><?= $upload_type->id ?></td>
					<td><?= h($upload_type->name) ?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png',
							['action' => 'view', 'type' => $upload_type->id],
							['alt' => __('View'), 'title' => __('View')]);
						echo $this->Html->iconLink('edit_24.png',
							['action' => 'edit', 'type' => $upload_type->id],
							['alt' => __('Edit'), 'title' => __('Edit')]);
						echo $this->Form->iconPostLink('delete_24.png',
							['action' => 'delete', 'type' => $upload_type->id],
							['alt' => __('Delete'), 'title' => __('Delete')],
							['confirm' => __('Are you sure you want to delete this uploadType?')]);
					?></td>
				</tr>

<?php
endforeach;
?>
			</tbody>
		</table>
	</div>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Upload Type')]));
?>
	</ul>
</div>
