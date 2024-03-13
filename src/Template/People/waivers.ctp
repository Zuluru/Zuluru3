<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

use Cake\I18n\FrozenDate;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Waiver History'));
?>

<div class="waivers index">
<h2><?= __('Waiver History') . ': ' . $person->full_name ?></h2>
<?php
if (empty($person->waivers)):
?>
<p><?= $person->id == $this->UserCache->read('Person.id') ? __('You have never signed a waiver.') : __('This person has never signed a waiver.') ?></p>
<?php
else:
?>

<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th><?= __('Waiver') ?></th>
				<th><?= __('Signed') ?></th>
				<th><?= __('Valid From') ?></th>
				<th><?= __('Valid Until') ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$affiliate_id = null;
	foreach ($person->waivers as $waiver):
		if (count($affiliates) > 1 && $waiver->affiliate_id != $affiliate_id):
			$affiliate_id = $waiver->affiliate_id;
?>
			<tr>
				<th colspan="5">
					<h3 class="affiliate"><?= h($waiver->affiliate->name) ?></h3>
				</th>
			</tr>
<?php
		endif;
?>
			<tr>
				<td><?= $waiver->name ?></td>
				<td><?= $this->Time->fulldate($waiver->_joinData->created) ?></td>
				<td><?= $this->Time->fulldate($waiver->_joinData->valid_from) ?></td>
				<td><?php
				if ($waiver->_joinData->valid_until != '9999-12-31') {
					echo $this->Time->fulldate($waiver->_joinData->valid_until);
				} else {
					echo __('Never expires');
				}
				?></td>
				<td class="actions"><?= $this->Html->iconLink('view_24.png', ['controller' => 'Waivers', 'action' => 'review', 'waiver' => $waiver->id, 'date' => $waiver->_joinData->valid_from->toDateString()]) ?></td>
			</tr>
<?php
	endforeach;
?>

		</tbody>
	</table>
</div>

<?php
endif;

if (!empty($waivers)):
?>
<h3><?= __('You have not signed the following waivers for a period covering today\'s date.') ?></h3>
<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<tbody>
<?php
	$affiliate_id = null;
	foreach ($waivers as $waiver):
		list($valid_from, $valid_until) = $waiver->validRange();
		if ($valid_from):
			if (count($affiliates) > 1 && $waiver->affiliate_id != $affiliate_id):
				$affiliate_id = $waiver->affiliate_id;
?>
			<tr>
				<th colspan="2">
					<h3 class="affiliate"><?= h($waiver->affiliate->name) ?></h3>
				</th>
			</tr>
<?php
			endif;
?>
			<tr>
				<td><?php
				echo $waiver->name . ' ';
				if ($waiver->expiry_type != 'never') {
					echo __('covering') . ' ' .
						$this->Time->date($valid_from) . ' ' . __('to') . ' ' .
						$this->Time->date($valid_until);
				} else {
					echo __('from') . ' ' . $this->Time->date($valid_from);
				}
				?></td>
				<td class="actions"><?= $this->Html->link(__('Sign'), ['controller' => 'Waivers', 'action' => 'sign', 'waiver' => $waiver->id, 'date' => FrozenDate::now()->toDateString()]) ?></td>
			</tr>
<?php
		endif;
	endforeach;
?>

		</tbody>
	</table>
</div>
<?php
endif;
?>

</div>
