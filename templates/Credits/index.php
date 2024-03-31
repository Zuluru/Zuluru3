<?php
/**
 * @var \App\Model\Entity\Credit[] $credits
 * @var \App\Model\Entity\Affiliate[] $affiliates
 * @var bool $all
 */

$title = ($all ? __('Credits') : __('Unused Credits'));
$this->Breadcrumbs->add($title);
?>

<div class="registrations index">
	<h2><?= $title ?></h2>
	<p><?= $this->Paginator->counter(
		__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	) ?></p>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Person') ?></th>
					<th><?= __('Date') ?></th>
					<th><?= __('Initial Amount') ?></th>
					<th><?= __('Amount Used') ?></th>
					<th><?= __('Notes') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$affiliate_id = null;
foreach ($credits as $credit):
	if (count($affiliates) > 1 && $credit->affiliate_id != $affiliate_id):
		$affiliate_id = $credit->affiliate_id;
?>

				<tr>
					<th colspan="5"><h3 class="affiliate"><?= h($credit->affiliate->name) ?></h3></th>
				</tr>
<?php
	endif;
?>

				<tr>
					<td><?= $this->element('People/block', ['person' => $credit->person]) ?></td>
					<td><?= $this->Time->date($credit->created) ?></td>
					<td><?= $this->Number->currency($credit->amount) ?></td>
					<td><?= $this->Number->currency($credit->amount_used) ?></td>
					<td><?= str_replace("\n", '<br />', $credit->notes) ?></td>
					<td class="actions"><?php
						if ($this->Authorize->can('view', $credit)) {
							echo $this->Html->iconLink('view_24.png',
								['action' => 'view', '?' => ['credit' => $credit->id]],
								['alt' => __('View'), 'title' => __('View')]);
						}
						if ($this->Authorize->can('edit', $credit)) {
							echo $this->Html->iconLink('edit_24.png',
								['action' => 'edit', '?' => ['credit' => $credit->id]],
								['alt' => __('Edit'), 'title' => __('Edit')]);
						}
						if ($this->Authorize->can('delete', $credit)) {
							$confirm = __('Are you sure you want to delete this credit?');
							if ($credit->payment_id) {
								$confirm .= "\n\n" . __('Doing so will also delete the related refund, but will NOT change the payment status of the registration.');
							}
							echo $this->Form->iconPostLink('delete_24.png',
								['action' => 'delete', '?' => ['credit' => $credit->id]],
								['alt' => __('Delete'), 'title' => __('Delete')],
								['confirm' => $confirm]);
						}
						if ($this->Authorize->can('transfer', $credit)) {
							echo $this->Html->iconLink('move_24.png',
								['action' => 'transfer', '?' => ['credit' => $credit->id]],
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
	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
if ($all) {
	echo $this->Html->tag('li', $this->Html->link(__('Unused Credits'), ['action' => 'index']));
} else {
	echo $this->Html->tag('li', $this->Html->link(__('All Credits'), ['action' => 'index', '?' => ['all' => true]]));
}
$params = $this->getRequest()->getQueryParams();
unset($params['page']);
echo $this->Html->tag('li', $this->Html->link(__('Download'), ['?' => $params, '_ext' => 'csv']));
?>
	</ul>
</div>
