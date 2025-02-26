<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Region[] $regions
 * @var string[] $affiliates
 */

$this->Breadcrumbs->add(__('Regions'));
$this->Breadcrumbs->add(__('List'));
?>

<div class="regions index">
	<h2><?= __('Regions') ?></h2>
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
foreach ($regions as $region):
	if (count($affiliates) > 1 && $region->affiliate_id != $affiliate_id):
		$affiliate_id = $region->affiliate_id;
?>
				<tr>
					<th colspan="2">
						<h3 class="affiliate"><?= h($region->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
	endif;
?>
				<tr>
					<td><?= h($region->name) ?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png',
							['action' => 'view', '?' => ['region' => $region->id]],
							['alt' => __('View'), 'title' => __('View')]);
						echo $this->Html->iconLink('edit_24.png',
							['action' => 'edit', '?' => ['region' => $region->id]],
							['alt' => __('Edit'), 'title' => __('Edit')]);
						echo $this->Form->iconPostLink('delete_24.png',
							['action' => 'delete', '?' => ['region' => $region->id]],
							['alt' => __('Delete'), 'title' => __('Delete')],
							['confirm' => __('Are you sure you want to delete this region?')]);
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
<?php
echo $this->Bootstrap->navPills([
	$this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Region')]
	),
]);
?>
</div>
