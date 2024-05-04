<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Waiver $waiver
 * @var string[] $affiliates
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Waiver'));
$this->Breadcrumbs->add(h($waiver->name));
$this->Breadcrumbs->add(__('View'));
?>

<div class="waivers view">
	<h2><?= h($waiver->name) ?></h2>
	<dl class="row">
<?php
if (count($affiliates) > 1):
?>
		<dt class="col-sm-2 text-end"><?= __('Affiliate') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Html->link($waiver->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $waiver->affiliate->id]]) ?></dd>
<?php
endif;
?>
		<dt class="col-sm-2 text-end"><?= __('Description') ?></dt>
		<dd class="col-sm-10 mb-0"><?= h($waiver->description) ?>&nbsp;</dd>
		<dt class="col-sm-2 text-end"><?= __('Text') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $waiver->text ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Active') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $waiver->active ? __('Yes') : __('No') ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Expiry Type') ?></dt>
		<dd class="col-sm-10 mb-0"><?= __(Configure::read("options.waivers.expiry_type.{$waiver->expiry_type}")) ?></dd>
<?php
if ($waiver->expiry_type == 'fixed_dates'):
?>
		<dt class="col-sm-2 text-end"><?= __('Start') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Time->format(mktime(12, 0, 0, $waiver->start_month), 'MMM') . ' ' . $waiver->start_day ?></dd>
		<dt class="col-sm-2 text-end"><?= __('End') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Time->format(mktime(12, 0, 0, $waiver->end_month), 'MMM') . ' ' . $waiver->end_day ?></dd>
<?php
elseif ($waiver->expiry_type == 'elapsed_time'):
?>
		<dt class="col-sm-2 text-end"><?= __('End') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $waiver->duration . ' ' . __('days') ?></dd>
<?php
endif;
?>
	</dl>
</div>

<div class="actions columns">
<?php
$links = [
	$this->Html->iconLink('view_32.png',
		['action' => 'index'],
		['alt' => __('List'), 'title' => __('List Waivers')]
	),
];
if ($this->Authorize->can('edit', $waiver)) {
	$links[] = $this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['waiver' => $waiver->id]],
		['alt' => __('Edit'), 'title' => __('Edit Waiver')]
	);
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['waiver' => $waiver->id]],
		['alt' => __('Delete'), 'title' => __('Delete Waiver')],
		['confirm' => __('Are you sure you want to delete this waiver?')]
	);
	$links[] = $this->Html->iconLink('waiver_add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Waiver')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>
