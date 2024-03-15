<?php
/**
 * @var \App\Model\Entity\Person $person
 * @var int[] $affiliates
 */

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add($person->full_name);
$this->Breadcrumbs->add(__('Credits'));
?>

<div class="players index">
	<h2><?= __('Credits') ?></h2>
	<p><?= __('You can use these credits to pay for things in the checkout page.') ?></p>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Date') ?></th>
					<th><?= __('Initial Amount') ?></th>
					<th><?= __('Amount Used') ?></th>
					<th><?= __('Notes') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
<?php
$affiliate_id = null;
foreach ($person->credits as $credit):
	if (count($affiliates) > 1 && $credit->affiliate_id != $affiliate_id):
		$affiliate_id = $credit->affiliate_id;
?>

			<tbody>
				<tr>
					<th colspan="4"><h3 class="affiliate"><?= h($credit->affiliate->name) ?></h3></th>
				</tr>
<?php
	endif;
?>

				<tr>
					<td><?= $this->Time->date($credit->created) ?></td>
					<td><?= $this->Number->currency($credit->amount) ?></td>
					<td><?= $this->Number->currency($credit->amount_used) ?></td>
					<td><?= str_replace("\n", '<br />', $credit->notes) ?></td>
					<td class="actions"><?php
						if ($this->Authorize->can('view', $credit)) {
							echo $this->Html->iconLink('view_24.png',
								['action' => 'view', 'credit' => $credit->id],
								['alt' => __('View'), 'title' => __('View')]);
						}
						if ($this->Authorize->can('edit', $credit)) {
							echo $this->Html->iconLink('edit_24.png',
								['controller' => 'Credits', 'action' => 'edit', 'credit' => $credit->id],
								['alt' => __('Edit'), 'title' => __('Edit')]);
						}
						if ($this->Authorize->can('delete', $credit)) {
							$confirm = __('Are you sure you want to delete this credit?');
							if ($credit->payment_id) {
								$confirm .= "\n\n" . __('Doing so will also delete the related refund, but will NOT change the payment status of the registration.');
							}
							echo $this->Form->iconPostLink('delete_24.png',
								['controller' => 'Credits', 'action' => 'delete', 'credit' => $credit->id],
								['alt' => __('Delete'), 'title' => __('Delete')],
								['confirm' => $confirm]);
						}
						if ($this->Authorize->can('transfer', $credit)) {
							echo $this->Html->iconLink('move_24.png',
								['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credit->id],
								['alt' => __('Transfer'), 'title' => __('Transfer')]);
						}
					?></td>
				</tr>
<?php
endforeach;
?>

			</tbody>
		</table>
	</div>
</div>
