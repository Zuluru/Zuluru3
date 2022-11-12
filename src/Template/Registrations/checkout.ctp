<?php
/**
 * @type $this \App\View\AppView
 * @type $registrations \App\Model\Entity\Registration[]
 * @type $other \App\Model\Entity\Registration[]
 * @type $person \App\Model\Entity\Person
 * @type $plugin_elements ArrayObject
 */

use App\Controller\AppController;
use App\Model\Entity\Credit;
use Cake\Core\Configure;

$this->Html->addCrumb(__('Registration'));
$this->Html->addCrumb(__('Checkout'));

$credit_split = collection($person->credits ?? [])->groupBy(function (Credit $credit) {
	return $credit->balance > 0;
})->toArray();
/** @var Credit[] $credits */
$credits = $credit_split[true] ?? [];
/** @var Credit[] $debits */
$debits = $credit_split[false] ?? [];
?>

<div class="registrations checkout form">
	<h2><?= __('Registration Checkout') ?></h2>

<?php
$order_id_format = Configure::read('registration.order_id_format');
$debit_id_format = Configure::read('registration.debit_id_format');

if (!empty($registrations)):
	echo $this->Html->para(null, __('These are your current unpaid registrations. <span class="highlight-message">Payment completes your registration and confirms your booking/purchase.</span>'));
?>
	<div>
		<div class="caption">
<?php
	if ($this->UserCache->currentId() != $this->UserCache->realId()) {
		$title = __('Add something else for {0}', $this->UserCache->read('Person.full_name'));
	} else {
		$title = __('Add something else');
	}
	echo $this->Html->iconLink('cart_add.png', ['controller' => 'Events', 'action' => 'wizard'], ['title' => $title]);
	echo $this->Html->para(null, $this->Html->link($title, ['controller' => 'Events', 'action' => 'wizard']));
?>
		</div>

		<div class="caption">
<?php
	echo $this->Jquery->toggleLink($this->Html->iconImg('cart_remove.png') . $this->Html->para(null, __('Remove something')), [
		'hide' => '.register-help',
		'show' => '.unregister-help',
	], [
		'title' => __('Click for instructions'),
		'escape' => false,
	]);
?>
		</div>

<?php
	$test_payments = Configure::read('payment.test_payments');
	$take_payments = Configure::read('registration.online_payments') && (
		$test_payments != TEST_PAYMENTS_ADMINS ||
		($this->Authorize->getIdentity()->isManagerOf(current($registrations)) && $test_payments == TEST_PAYMENTS_ADMINS)
	);
	if ($take_payments):
?>
		<div class="caption">
<?php
		echo $this->Jquery->toggleLink($this->Html->iconImg('pay_online.png') . $this->Html->para(null, __('Pay online')), [
			'hide' => '.register-help',
			'show' => '.online_help',
		], [
			'title' => __('Click for instructions'),
			'escape' => false,
		]);
?>
		</div>
<?php
	endif;

	if (Configure::read('registration.offline_payment_text')):
?>
		<div class="caption">
<?php
		echo $this->Jquery->toggleLink($this->Html->iconImg('pay_offline.png') . $this->Html->para(null, __('Pay offline')), [
			'hide' => '.register-help',
			'show' => '.offline_help',
		], [
			'title' => __('Click for instructions'),
			'escape' => false,
		]);
?>
		</div>
<?php
	endif;

	if (!empty($credits)):
?>
		<div class="caption">
<?php
		echo $this->Jquery->toggleLink($this->Html->iconImg('redeem.png') . $this->Html->para(null, __('Redeem Credit')), [
			'hide' => '.register-help',
			'show' => '.credit_help',
		], [
			'title' => __('Click for instructions'),
			'escape' => false,
		]);
?>
		</div>
<?php
	endif;
?>
	</div>

	<div class="clear-float">&nbsp;</div>

	<div class="unregister-help register-help">
		<p><?= $this->Html->iconImg('help_24.png') ?></p>
		<p><?= __('To remove an item, click the {0} button next to it.', $this->Html->iconImg('delete_24.png', ['style' => 'float: none; margin-left: 0;'])) ?></p>
		<p><?= __('Note that this will delete all of your preferences and you may lose the spot that is currently tentatively reserved for you.') ?></p>
	</div>

	<div class="online_help register-help">
		<p><?= $this->Html->iconImg('help_24.png') ?></p>
		<p><?= __('To pay online with {0}, click the payment button below.', Configure::read('payment.options')) ?></p>
		<?= Configure::read('registration.online_payment_text') ?>
	</div>

	<div class="offline_help register-help">
<?php
	echo $this->Html->para(null, $this->Html->iconImg('help_24.png'));
	echo $this->element('Payments/offline');
?>
	</div>

	<div class="credit_help register-help">
		<p><?= $this->Html->iconImg('help_24.png') ?></p>
		<p><?= __('To redeem a credit, click the "{0}" button next to the registration that you want the credit to be applied to.',
			__('Redeem Credit')
		) ?></p>
		<p><?= __('You will be given options on the resulting page, including opting not to redeem the credit at this time.') ?></p>
	</div>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th></th>
					<th><?= __('Order ID') ?></th>
					<th><?= __('Event') ?></th>
					<th><?= __('Balance') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$total = 0;
	foreach ($registrations as $registration):
		[$cost, $tax1, $tax2] = $registration->paymentAmounts();
		$total += $cost + $tax1 + $tax2;
?>
				<tr>
					<td class="actions"><?php
						if ($this->Authorize->can('edit', $registration)) {
							echo $this->Html->iconLink('edit_32.png',
								['action' => 'edit', 'registration' => $registration->id, 'return' => AppController::_return()],
								['alt' => __('Edit'), 'title' => __('Edit Registration')]
							);
						}

						if ($this->Authorize->can('unregister', $registration)) {
							echo $this->Html->iconLink('delete_32.png',
								['action' => 'unregister', 'registration' => $registration->id, 'return' => AppController::_return()],
								['confirm' => __('Are you sure you want to unregister from this event? This will delete all of your preferences and you may lose the spot that is currently tentatively reserved for you.')]
							);
						}

						if (!empty($credits)) {
							echo $this->Html->link(__('Redeem Credit'), ['action' => 'redeem', 'registration' => $registration->id]);
						}
					?></td>
					<td><?= sprintf($order_id_format, $registration->id) ?></td>
					<td><?php
						echo $this->Html->link($registration->long_description, ['controller' => 'Events', 'action' => 'view', 'event' => $registration->event->id]);
						if ($registration->payment === 'Reserved') {
							if ($registration->reservation_expires) {
								$expiry = __('Reserved until {0}', $this->Time->datetime($registration->reservation_expires));
							} else {
								$expiry = __('Reserved');
							}
							echo __(' ({0})', $expiry);
						}
					?></td>
					<td><?= $this->Number->currency($cost + $tax1 + $tax2) ?></td>
				</tr>
<?php
	endforeach;

	foreach ($debits as $debit):
		$total -= $debit->balance;
?>
				<tr>
					<td class="actions"><?php
						if (!empty($credits)) {
							echo $this->Html->link(__('Redeem Credit'), ['action' => 'redeem', 'registration' => $registration->id]);
						}
					?></td>
					<td><?= sprintf($debit_id_format, $debit->id) ?></td>
					<td><?= $debit->notes ?></td>
					<td><?= $this->Number->currency(-$debit->balance) ?></td>
				</tr>
<?php
	endforeach;
?>

				<tr>
					<th></th>
					<th></th>
					<th><?= __('Total') ?>:</th>
					<th><?= $this->Number->currency($total) ?></th>
				</tr>
			</tbody>
		</table>
	</div>
<?php

	if ($plugin_elements->count() > 0) {
		foreach ($plugin_elements as $element => $params) {
			if (is_numeric($element)) {
				$element = $params;
				$params = [];
			}
			echo $this->element($element, array_merge($params, ['number_of_providers' => $plugin_elements->count(), 'debits' => $debits]));
		}
	} else {
		echo $this->Html->para('warning-message', __('Online payments have been enabled on this system, but no payment provider has been configured.'));
	}

endif;

if (!empty($other)):
	echo $this->Html->para('error-message', __('You have registered for the following events, but cannot pay right now:'));
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th></th>
					<th><?= __('Order ID') ?></th>
					<th><?= __('Event') ?></th>
					<th><?= __('Cost') ?></th>
					<th><?= __('Reason') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($other as $registration):
		[$cost, $tax1, $tax2] = $registration['registration']->paymentAmounts();
?>
				<tr>
					<td class="actions"><?php
						if (!empty($registration['change_price'])) {
							echo $this->Html->link(__('Reregister'),
									['action' => 'edit', 'registration' => $registration['registration']->id]);
						}

						if ($this->Authorize->can('unregister', $registration['registration'])) {
							echo $this->Html->iconLink('delete_32.png',
								['action' => 'unregister', 'registration' => $registration['registration']->id, 'return' => AppController::_return()],
								['confirm' => __('Are you sure you want to unregister from this event? This will delete all of your preferences and you may lose the spot that is currently tentatively reserved for you.')]
							);
						}
					?></td>
					<td><?= sprintf($order_id_format, $registration['registration']->id) ?></td>
					<td><?= $this->Html->link($registration['registration']->event->name, ['controller' => 'Events', 'action' => 'view', 'event' => $registration['registration']->event->id]) ?></td>
					<td><?= $this->Number->currency($cost + $tax1 + $tax2) ?></td>
					<td><?= $registration['reason'] ?></td>
				</tr>
<?php
	endforeach;
?>

			</tbody>
		</table>
	</div>
<?php
endif;

echo $this->element('Payments/refund');
if (Configure::read('registration.online_payments') && stripos(Configure::read('payment.options'), 'interac')) {
	echo $this->Html->para('small', __('&reg; Trade-mark of Interac Inc. Used under licence. <a href="https://www.interaconline.com/learn/" target="_blank">Learn more</a> about INTERAC Online.'));
}
?>
</div>
