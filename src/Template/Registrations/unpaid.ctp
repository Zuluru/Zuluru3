<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration[] $registrations
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Registrations'));
$this->Breadcrumbs->add(__('Unpaid'));
?>

<div class="registrations index">
	<h2><?= __('Unpaid Registrations') ?></h2>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Registration') ?></th>
					<th><?= __('Person / Event') ?></th>
					<th><?= __('Date') ?></th>
					<th><?= __('Payment') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$total = array_fill_keys(Configure::read('registration_delinquent'), 0);
$order_id_format = Configure::read('registration.order_id_format');
$affiliate_id = null;
foreach ($registrations as $registration):
	if (count($affiliates) > 1 && $registration->event->affiliate_id != $affiliate_id):
		$affiliate_id = $registration->event->affiliate_id;
?>
				<tr>
					<th colspan="5">
						<h3 class="affiliate"><?= h($registration->event->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
	endif;

	$order_id = sprintf($order_id_format, $registration->id);
?>

				<tr>
					<td><?= $this->Html->link($order_id, ['action' => 'view', 'registration' => $registration->id]) ?></td>
					<td><?= $this->element('People/block', ['person' => $registration->person]) ?></td>
					<td><?= $this->Time->datetime($registration->modified) ?></td>
					<td><?= $registration->payment ?></td>
					<td class="actions"><?= $this->element('Registrations/actions', ['registration' => $registration]) ?></td>
				</tr>
				<tr>
					<td></td>
					<td colspan="4"><?= $this->Html->link($registration->event->name, ['controller' => 'Events', 'action' => 'view', 'event' => $registration->event->id]) ?></td>
				</tr>
<?php
	if (!empty($registration->notes)):
?>

				<tr>
					<td></td>
					<td colspan="4"><?= $registration->notes ?></td>
				</tr>
<?php
	endif;
?>

				<tr><td colspan="5">&nbsp;</td></tr>
<?php
	$total[$registration->payment] ++;
endforeach;
?>
			</tbody>
		</table>

<?php
$total_rows = [];
foreach ($total as $key => $value) {
	$total_rows[] = [$key, $value];
}

echo $this->Html->tag('table', $this->Html->tableCells($total_rows), ['class' => 'table table-striped table-hover table-condensed']);
?>

	</div>
</div>
