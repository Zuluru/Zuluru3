<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Affiliate $affiliate
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Affiliates'));
$this->Breadcrumbs->add(h($affiliate->name));
$this->Breadcrumbs->add(__('View'));
?>

<div class="affiliates view">
	<h2><?= h($affiliate->name) ?></h2>
	<dl class="dl-horizontal">
		<dt><?= __('Active') ?></dt>
		<dd><?= $affiliate->active ? __('Yes') : __('No') ?></dd>
	</dl>
</div>
<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('Managers') ?></h4>
<?php
if (!empty($affiliate->people)):
?>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= Configure::read('profile.legal_name') ? __('Preferred Name') : __('First Name') ?></th>
					<th><?= __('Last Name') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($affiliate->people as $person):
?>
				<tr>
					<td><?= $this->element('People/block', ['person' => $person, 'display_field' => 'first_name']) ?></td>
					<td><?= $this->element('People/block', ['person' => $person, 'display_field' => 'last_name']) ?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png',
							['controller' => 'People', 'action' => 'view', '?' => ['person' => $person->id]],
							['alt' => __('View'), 'title' => __('View')]);
						echo $this->Html->iconLink('edit_24.png',
							['controller' => 'People', 'action' => 'edit', '?' => ['person' => $person->id]],
							['alt' => __('Edit'), 'title' => __('Edit')]);
						echo $this->Form->iconPostLink('coordinator_delete_24.png',
							['action' => 'remove_manager', '?' => ['affiliate' => $affiliate->id, 'person' => $person->id]],
							['alt' => __('Remove'), 'title' => __('Remove')],
							['confirm' => __('Are you sure you want to remove {0} as a manager?', $person->full_name)]);
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
	['alt' => __('List'), 'title' => __('List Affiliates')]));
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', '?' => ['affiliate' => $affiliate->id]],
	['alt' => __('Edit'), 'title' => __('Edit Affiliate')]));
echo $this->Html->tag('li', $this->Html->iconLink('coordinator_add_32.png',
	['action' => 'add_manager', '?' => ['affiliate' => $affiliate->id]],
	['alt' => __('Add Manager'), 'title' => __('Add Manager')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', '?' => ['affiliate' => $affiliate->id]],
	['alt' => __('Delete'), 'title' => __('Delete Affiliate')],
	['confirm' => __('Are you sure you want to delete this affiliate?')]));
?>
	</ul>
</div>
