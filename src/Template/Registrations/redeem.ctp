<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 */

$this->Html->addCrumb(__('Registration'));
$this->Html->addCrumb(__('Redeem Credit'));
?>

<div class="registrations form">
	<h2><?= __('Redeem Credit') ?></h2>
<?php
$balance = $registration->balance;

echo $this->Html->para(null, __('You have requested to redeem a credit towards payment of your registration for {0}. You have an outstanding balance of {1} on this registration.',
	$this->Html->link($registration->event->name, ['controller' => 'Events', 'action' => 'view', 'event' => $registration->event->id]), $this->Number->currency($balance)));

if (count($registration->person->credits) == 1):
	$credit = $registration->person->credits[0]->balance;
	if ($credit > $balance) {
		echo $this->Html->para(null, __('If you apply your credit, the balance will be covered, and you will still have a credit of {0} remaining.', $this->Number->currency($credit - $balance)));
	} else if ($credit == $balance) {
		echo $this->Html->para(null, __('If you apply your credit, the balance will be covered, and your credit will be used up.'));
	} else {
		echo $this->Html->para(null, __('If you apply your credit, it will be used up, and you will still have a balance of {0} owing on the registration.', $this->Number->currency($balance - $credit)));
	}
	echo $this->Html->para(null, $this->Html->link(__('Apply the credit now'),
		['action' => 'redeem', 'registration' => $registration->id, 'credit' => $registration->person->credits[0]->id],
		['confirm' => __('Are you sure you want to apply this credit? This cannot be undone.')]
	));

else:
	echo $this->Html->para(null, __('You have the following credits to redeem:'));
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Date') ?></th>
					<th><?= __('Initial Amount') ?></th>
					<th><?= __('Amount Used') ?></th>
					<th><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($registration->person->credits as $credit):
?>
				<tr>
					<td><?= $this->Time->date($credit->created) ?></td>
					<td><?= $this->Number->currency($credit->amount) ?></td>
					<td><?= $this->Number->currency($credit->amount_used) ?></td>
					<td class="actions"><?php
					echo $this->Html->link(__('Apply credit'),
						['action' => 'redeem', 'registration' => $registration->id, 'credit' => $credit->id],
						['confirm' => __('Are you sure you want to apply this credit? This cannot be undone.')]
					);
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
