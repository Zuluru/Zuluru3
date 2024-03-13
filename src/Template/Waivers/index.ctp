<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Waiver[] $waivers
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Waivers'));
$this->Html->addCrumb(__('List'));
?>

<div class="waivers index">
	<h2><?= __('Waivers') ?></h2>
	<p><?= $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]) ?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('id') ?></th>
					<th><?= $this->Paginator->sort('name') ?></th>
					<th><?= __('Description') ?></th>
					<th><?= $this->Paginator->sort('active') ?></th>
					<th><?= $this->Paginator->sort('expiry_type') ?></th>
					<th><?= __('Valid For') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$affiliate_id = null;
foreach ($waivers as $waiver):
	if (count($affiliates) > 1 && $waiver->affiliate_id != $affiliate_id):
		$affiliate_id = $waiver->affiliate_id;
?>
				<tr>
					<th colspan="7">
						<h3 class="affiliate"><?= h($waiver->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
	endif;
?>
				<tr>
					<td><?= $waiver->id ?></td>
					<td><?= h($waiver->name) ?></td>
					<td><?= h($waiver->description) ?>&nbsp;</td>
					<td><?= $waiver->active ? __('Yes') : __('No') ?></td>
					<td><?= Configure::read("options.waivers.expiry_type.{$waiver->expiry_type}") ?></td>
					<td><?php
						switch ($waiver->expiry_type) {
							case 'fixed_dates':
								printf('%s - %s',
									$this->Time->format(mktime(12, 0, 0, $waiver->start_month, $waiver->start_day), 'MMM d'),
									$this->Time->format(mktime(12, 0, 0, $waiver->end_month, $waiver->end_day), 'MMM d'));
								break;

							case 'elapsed_time':
								echo $waiver->duration . ' ' . __('days');
								break;

							case 'never':
								echo __('Forever');
								break;
						}
					?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png',
							['action' => 'view', 'waiver' => $waiver->id],
							['alt' => __('View'), 'title' => __('View')]);
						echo $this->Html->iconLink('edit_24.png',
							['action' => 'edit', 'waiver' => $waiver->id],
							['alt' => __('Edit'), 'title' => __('Edit')]);
						echo $this->Form->iconPostLink('delete_24.png',
							['action' => 'delete', 'waiver' => $waiver->id],
							['alt' => __('Delete'), 'title' => __('Delete')],
							['confirm' => __('Are you sure you want to delete this waiver?')]);
					?></td>
				</tr>

<?php
endforeach;
?>
			</tbody>
		</table>
	</div>
	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?= $this->Html->tag('li', $this->Html->iconLink('waiver_add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Waiver')]));
?>
	</ul>
</div>
