<?php
use App\Controller\AppController;

$this->Html->addCrumb(__('Regions'));
$this->Html->addCrumb(h($region->name));
$this->Html->addCrumb(__('View'));
?>

<div class="regions view">
	<h2><?= h($region->name) ?></h2>
<?php
if (count($affiliates) > 1):
?>
	<dl class="dl-horizontal">
		<dt><?= __('Affiliate') ?></dt>
		<dd><?= $this->Html->link($region->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $region->affiliate->id]) ?></dd>
	</dl>
<?php
endif;
?>
</div>
<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('Facilities') ?></h4>
<?php
if (!empty($region->facilities)):
?>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th><?= __('Name') ?></th>
						<th><?= __('Code') ?></th>
						<th><?= __('Is Open') ?></th>
						<th class="actions"><?= __('Actions') ?></th>
					</tr>
				</thead>
				<tbody>
<?php
	foreach ($region->facilities as $facility):
?>
					<tr>
						<td><?= h($facility->name) ?></td>
						<td><?= h($facility->code) ?></td>
						<td><?= $facility->is_open ? __('Yes') : __('No') ?></td>
						<td class="actions"><?php
							echo $this->Html->iconLink('view_24.png',
								['controller' => 'Facilities', 'action' => 'view', 'facility' => $facility->id],
								['alt' => __('View'), 'title' => __('View')]);
							echo $this->Html->iconLink('edit_24.png',
								['controller' => 'Facilities', 'action' => 'edit', 'facility' => $facility->id, 'return' => AppController::_return()],
								['alt' => __('Edit'), 'title' => __('Edit')]);
							echo $this->Form->iconPostLink('delete_24.png',
								['controller' => 'Facilities', 'action' => 'delete', 'facility' => $facility->id, 'return' => AppController::_return()],
								['alt' => __('Delete'), 'title' => __('Delete')],
								['confirm' => __('Are you sure you want to delete this facility?')]);
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
echo $this->Html->tag('li', $this->Html->link(__('New Facility'),
	['controller' => 'Facilities', 'action' => 'add', 'region' => $region->id]));
echo $this->Html->tag('li', $this->Html->link(__('List Regions'),
	['action' => 'index']));
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', 'region' => $region->id, 'return' => AppController::_return()],
	['alt' => __('Edit'), 'title' => __('Edit Region')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'region' => $region->id],
	['alt' => __('Delete'), 'title' => __('Delete Region')],
	['confirm' => __('Are you sure you want to delete this region?')]));
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('New Region')]));
?>
	</ul>
</div>
