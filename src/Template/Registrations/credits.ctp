<?php
$this->Html->addCrumb(__('Registrations'));
$this->Html->addCrumb(__('Credits'));
?>

<div class="registrations index">
	<h2><?= __('Unused Credits') ?></h2>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Person') ?></th>
					<th><?= __('Date') ?></th>
					<th><?= __('Initial Amount') ?></th>
					<th><?= __('Amount Used') ?></th>
					<th><?= __('Notes') ?></th>
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
				</tr>
<?php
endforeach;
?>

			</tbody>
		</table>
	</div>
</div>
