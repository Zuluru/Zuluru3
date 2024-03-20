<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Waiver $waiver
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Waiver'));
$this->Breadcrumbs->add(h($waiver->name));
$this->Breadcrumbs->add(__('View'));
?>

<div class="waivers view">
	<h2><?= h($waiver->name) ?></h2>
	<dl class="dl-horizontal">
<?php
if (count($affiliates) > 1):
?>
		<dt><?= __('Affiliate') ?></dt>
		<dd><?= $this->Html->link($waiver->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $waiver->affiliate->id]]) ?></dd>
<?php
endif;
?>
		<dt><?= __('Description') ?></dt>
		<dd><?= h($waiver->description) ?>&nbsp;</dd>
		<dt><?= __('Text') ?></dt>
		<dd><?= $waiver->text ?></dd>
		<dt><?= __('Active') ?></dt>
		<dd><?= $waiver->active ? __('Yes') : __('No') ?></dd>
		<dt><?= __('Expiry Type') ?></dt>
		<dd><?= __(Configure::read("options.waivers.expiry_type.{$waiver->expiry_type}")) ?></dd>
<?php
if ($waiver->expiry_type == 'fixed_dates'):
?>
		<dt><?= __('Start') ?></dt>
		<dd><?= $this->Time->format(mktime(12, 0, 0, $waiver->start_month), 'MMM') . ' ' . $waiver->start_day ?></dd>
		<dt><?= __('End') ?></dt>
		<dd><?= $this->Time->format(mktime(12, 0, 0, $waiver->end_month), 'MMM') . ' ' . $waiver->end_day ?></dd>
<?php
elseif ($waiver->expiry_type == 'elapsed_time'):
?>
		<dt><?= __('End') ?></dt>
		<dd><?= $waiver->duration . ' ' . __('days') ?></dd>
<?php
endif;
?>
	</dl>
</div>

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('view_32.png',
	['action' => 'index'],
	['alt' => __('List'), 'title' => __('List Waivers')]));
if ($this->Authorize->can('edit', $waiver)) {
	echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['waiver' => $waiver->id]],
		['alt' => __('Edit'), 'title' => __('Edit Waiver')]));
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['waiver' => $waiver->id]],
		['alt' => __('Delete'), 'title' => __('Delete Waiver')],
		['confirm' => __('Are you sure you want to delete this waiver?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('waiver_add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Waiver')]));
}
?>
	</ul>
</div>
