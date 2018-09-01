<?php
$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Credits'));
?>

<div class="players index">
	<h2><?= __('Unused Credits') ?></h2>
	<p><?= __('You can use these credits to pay for things in the checkout page.') ?></p>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Date') ?></th>
					<th><?= __('Initial Amount') ?></th>
					<th><?= __('Amount Used') ?></th>
					<th><?= __('Notes') ?></th>
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
				</tr>
<?php
endforeach;
?>

			</tbody>
		</table>
	</div>
</div>
