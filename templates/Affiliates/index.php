<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Affiliate[] $affiliates
 */

$this->Breadcrumbs->add(__('Affiliates'));
$this->Breadcrumbs->add(__('List'));
?>

<div class="affiliates index">
	<h2><?= __('Affiliates') ?></h2>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th><?= __('Name') ?></th>
				<th><?= __('Active') ?></th>
				<th><?= __('Managers') ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($affiliates as $affiliate):
?>
			<tr>
				<td><?= h($affiliate->name) ?></td>
				<td><?= $affiliate['active'] ? __('Yes') : __('No') ?></td>
				<td><?php
				$managers = [];
				foreach ($affiliate['people'] as $person) {
					$managers[] = $this->element('People/block', compact('person'));
				}
				if (!empty($managers)) {
					echo implode(', ', $managers);
				} else {
					echo __('None');
				}
				?></td>
				<td class="actions"><?php
				echo $this->Html->iconLink('view_24.png',
					['action' => 'view', 'affiliate' => $affiliate->id],
					['alt' => __('View'), 'title' => __('View')]);
				echo $this->Html->iconLink('edit_24.png',
					['action' => 'edit', 'affiliate' => $affiliate->id],
					['alt' => __('Edit'), 'title' => __('Edit')]);
				echo $this->Html->iconLink('coordinator_add_24.png',
					['action' => 'add_manager', 'affiliate' => $affiliate->id],
					['alt' => __('Add Manager'), 'title' => __('Add Manager')]);
				echo $this->Form->iconPostLink('delete_24.png',
					['action' => 'delete', 'affiliate' => $affiliate->id],
					['alt' => __('Delete'), 'title' => __('Delete')],
					['confirm' => __('Are you sure you want to delete this affiliate?')]);
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
	['alt' => __('Add'), 'title' => __('Add Affiliate')]));
?>
	</ul>
</div>
