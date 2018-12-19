<?php
use App\Controller\AppController;
use Cake\Core\Configure;

$this->Html->addCrumb(__('Franchises'));
$this->Html->addCrumb(__('Starting with {0}', $letter));
?>

<div class="franchises index">
	<h2><?= __('List Franchises') ?></h2>
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
			if ($l != $letter) {
				$links[] = $this->Html->link($l, ['action' => 'letter', 'letter' => $l]);
			} else {
				$links[] = $letter;
			}
		}
		echo implode('&nbsp;&nbsp;', $links);
	?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Name') ?></th>
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
					<th colspan="3"><h3 class="affiliate"><?= h($franchise->affiliate->name) ?></h3></th>
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
						if ($this->Authorize->can('add_team', $franchise)) {
							echo $this->Html->iconLink('team_add_24.png',
								['action' => 'add_team', 'franchise' => $franchise->id],
								['alt' => __('Add Team'), 'title' => __('Add Team')]);
						}
						if ($this->Authorize->can('edit', $franchise)) {
							echo $this->Html->iconLink('edit_24.png',
								['action' => 'edit', 'franchise' => $franchise->id, 'return' => AppController::_return()],
								['alt' => __('Edit'), 'title' => __('Edit')]);
							echo $this->Html->iconLink('move_24.png',
								['action' => 'add_owner', 'franchise' => $franchise->id],
								['alt' => __('Add Owner'), 'title' => __('Add an Owner')]);
							echo $this->Form->iconPostLink('delete_24.png',
								['action' => 'delete', 'franchise' => $franchise->id, 'return' => AppController::_return()],
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
