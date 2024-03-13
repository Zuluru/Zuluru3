<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge[] $badges
 */

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb(__('Approve Badges'));
?>

<div class="people badges">
	<h2><?= __('Approve Badges') ?></h2>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Badge') ?></th>
					<th><?= __('Player') ?></th>
					<th><?= __('Reason') ?></th>
					<th><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$affiliate_id = null;
foreach ($badges as $badge):
	if (count($affiliates) > 1 && $badge->affiliate_id != $affiliate_id):
		$affiliate_id = $badge->affiliate_id;
?>
				<tr>
					<th colspan="4"><h3 class="affiliate"><?= h($badge->affiliate->name) ?></h3></th>
				</tr>
<?php
	endif;
?>
				<tr>
					<td><?= $this->element('Badges/block', ['badge' => $badge, 'use_name' => true]) ?></td>
					<td><?= $this->element('People/block', ['person' => $badge->_matchingData['People']]) ?></td>
					<td><?= $badge->_matchingData['BadgesPeople']->reason ?></td>
					<td class="actions"><?php
					echo $this->Jquery->ajaxLink(__('Approve'), [
						'url' => ['action' => 'approve_badge', 'badge' => $badge->_matchingData['BadgesPeople']->id],
						'disposition' => 'remove_closest',
						'selector' => 'tr',
					]);
					echo $this->Jquery->ajaxLink(__('Delete'), [
						'url' => ['action' => 'delete_badge', 'badge' => $badge->_matchingData['BadgesPeople']->id],
						'dialog' => 'badge_comment_div',
						'disposition' => 'remove_closest',
						'selector' => 'tr',
					]);
					?></td>
				</tr>
<?php
endforeach;
?>

			</tbody>
		</table>
	</div>
</div>

<?= $this->element('People/badge_div', [
	'message' => __('If you want to add a comment to the nominator about why the nomination was not approved, do so here. The nominee will not receive any message.'),
]);
