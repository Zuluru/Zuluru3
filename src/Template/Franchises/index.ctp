<?php
use App\Controller\AppController;
use Cake\Core\Configure;

$this->Html->addCrumb(__('Franchises'));
$this->Html->addCrumb(__('List'));
?>

<div class="franchises index">
	<h2><?= __('Franchises') ?></h2>
<?php
if (empty($franchises)):
?>
	<p class="warning-message"><?= __('There are no franchises in the system. Please check back periodically for updates.') ?></p>
<?php
else:
?>
	<p><?php
		echo __('Locate by letter: ');
		$links = [];
		foreach ($letters as $l) {
			$l = strtoupper($l['letter']);
			$links[] = $this->Html->link($l, ['action' => 'letter', 'letter' => $l], ['rel' => 'nofollow']);
		}
		echo implode('&nbsp;&nbsp;', $links);
	?></p>
	<p><?= $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]) ?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('name') ?></th>
					<th><?= __('Owner(s)') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$affiliate_id = null;
	foreach ($franchises as $franchise):
		if (count($affiliates) > 1 && $franchise->affiliate_id != $affiliate_id):
			$affiliate_id = $franchise->affiliate_id;
?>
				<tr>
					<th colspan="3">
						<h3 class="affiliate"><?= h($franchise->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
		endif;
?>
				<tr>
					<td><?php
						echo $this->Html->link($franchise->name, ['action' => 'view', 'franchise' => $franchise->id]);
						// TODO: Link to website, if any
					?></td>
					<td><?php
						$owners = [];
						foreach ($franchise->people as $person) {
							$owners[] = $this->element('People/block', compact('person'));
						}
						echo implode(', ', $owners);
					?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png',
							['action' => 'view', 'franchise' => $franchise->id],
							['alt' => __('View'), 'title' => __('View')]);
						if ($this->Authorize->can('add_team', $franchise)) {
							echo $this->Html->iconLink('team_add_24.png',
								['action' => 'add_team', 'franchise' => $franchise->id],
								['alt' => __('Add Team'), 'title' => __('Add Team')]);
						}
						if ($this->Authorize->can('edit', $franchise)) {
							echo $this->Html->iconLink('edit_24.png',
								['action' => 'edit', 'franchise' => $franchise->id, 'return' => AppController::_return()],
								['alt' => __('Edit'), 'title' => __('Edit')]);
						}
						if ($this->Authorize->can('add_owner', $franchise)) {
							echo $this->Html->iconLink('move_24.png',
								['action' => 'add_owner', 'franchise' => $franchise->id, 'return' => AppController::_return()],
								['alt' => __('Add Owner'), 'title' => __('Add Owner')]);
						}
						if ($this->Authorize->can('delete', $franchise)) {
							echo $this->Form->iconPostLink('delete_24.png',
								['action' => 'delete', 'franchise' => $franchise->id],
								['alt' => __('Delete'), 'title' => __('Delete')],
								['confirm' => __('Are you sure you want to delete this franchise?')]);
						}
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
<?php
endif;
?>
</div>
<?php
if ($this->Authorize->can('add', \App\Controller\FranchisesController::class)):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Franchise')]));
?>
	</ul>
</div>
<?php
endif;
