<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Registration'));
$this->Html->addCrumb(__('Checkout'));
?>

<div class="registrations checkout form">
	<h2><?= __('Registration Checkout') ?></h2>

<?php
$order_id_format = Configure::read('registration.order_id_format');

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
	if (Configure::read('registration.online_payments') && ($test_payments <= 1 || (Configure::read('Perm.is_admin') && Configure::read('payment.test_payments') == 2))):
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

	if (!empty($person->credits)):
?>
		<div class="caption">
<?php
		echo $this->Jquery->toggleLink($this->Html->iconImg('redeem.png') . $this->Html->para(null, __('Redeem credit')), [
			'hide' => '.register-help',
			'show' => '.credit_help',
		], [
			'title' => __('Click for instructions'),
			'escape' => false,
		]);
	endif;
?>
		</div>
	</div>

	<div class="clear-float">&nbsp;</div>

	<div class="unregister-help register-help">
		<p><?= $this->Html->iconImg('help_24.png') ?></p>
		<p><?= __('To remove an item, click the "Unregister" button next to it.') ?></p>
		<p><?= __('Note that this will delete all of your preferences and you may lose the spot that is currently tentatively reserved for you.') ?></p>
	</div>

	<div class="online_help register-help">
<?php
	$provider = Configure::read('payment.payment_implementation');
	if ($provider == 'paypal') {
		$button = 'Check out with PayPal';
	} else {
		$button = 'Pay';
	}
?>
		<p><?= $this->Html->iconImg('help_24.png') ?></p>
		<p><?= __('To pay online with {0}, click the "{1}" button below.', Configure::read('payment.options'), $button) ?></p>
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
		<p><?= __('To redeem a credit, click the "Redeem credit" button next to the registration that you want the credit to be applied to.') ?></p>
		<p><?= __('You will be given options on the resulting page, including opting not to redeem the credit at this time.') ?></p>
	</div>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Order ID') ?></th>
					<th><?= __('Event') ?></th>
					<th><?= __('Balance') ?></th>
					<th><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$total = 0;
	foreach ($registrations as $registration):
		list($cost, $tax1, $tax2) = $registration->paymentAmounts();
		$total += $cost + $tax1 + $tax2;
?>
				<tr>
					<td><?= sprintf($order_id_format, $registration->id) ?></td>
					<td><?php
						echo $this->Html->link($registration->long_description, ['controller' => 'Events', 'action' => 'view', 'event' => $registration->event->id]);
						if ($registration->payment == 'Reserved') {
							if ($registration->reservation_expires) {
								$expiry = __('Reserved until {0}', $this->Time->datetime($registration->reservation_expires));
							} else {
								$expiry = __('Reserved');
							}
							echo ' (' . $expiry . ')';
						}
					?></td>
					<td><?= $this->Number->currency($cost + $tax1 + $tax2) ?></td>
					<td class="actions"><?php
						echo $this->Html->link(__('Edit'),
								['action' => 'edit', 'registration' => $registration->id]);
						if (in_array($registration->payment, Configure::read('registration_none_paid'))) {
							echo $this->Html->link(__('Unregister'),
								['action' => 'unregister', 'registration' => $registration->id],
								['confirm' => __('Are you sure you want to unregister from this event? This will delete all of your preferences and you may lose the spot that is currently tentatively reserved for you.')]
							);
						}
						if (!empty($person->credits)) {
							echo $this->Html->link(__('Redeem credit'), ['action' => 'redeem', 'registration' => $registration->id]);
						}
					?></td>
				</tr>
<?php
	endforeach;
?>

				<tr>
					<th></th>
					<th><?= __('Total') ?>:</th>
					<th><?= $this->Number->currency($total) ?></th>
					<th class="actions"><?php
						if (Configure::read('registration.online_payments') && ($test_payments <= 1 || (Configure::read('Perm.is_admin') && Configure::read('payment.test_payments') == 2))) {
							echo $this->element("Payments/forms/$provider");
						}
					?></th>
				</tr>
			</tbody>
		</table>
	</div>
<?php
endif;

if (!empty($other)):
	echo $this->Html->para('error-message', __('You have registered for the following events, but cannot pay right now:'));
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Order ID') ?></th>
					<th><?= __('Event') ?></th>
					<th><?= __('Cost') ?></th>
					<th><?= __('Reason') ?></th>
					<th><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($other as $registration):
		list($cost, $tax1, $tax2) = $registration['registration']->paymentAmounts();
?>
				<tr>
					<td><?= sprintf($order_id_format, $registration['registration']->id) ?></td>
					<td><?= $this->Html->link($registration['registration']->event->name, ['controller' => 'Events', 'action' => 'view', 'event' => $registration['registration']->event->id]) ?></td>
					<td><?= $this->Number->currency($cost + $tax1 + $tax2) ?></td>
					<td><?= $registration['reason'] ?></td>
					<td class="actions"><?php
						if (!empty($registration['change_price'])) {
							echo $this->Html->link(__('Reregister'),
									['action' => 'edit', 'registration' => $registration['registration']->id]);
						}

						if (!in_array($registration['registration']->payment, Configure::read('registration_some_paid'))) {
							echo $this->Html->link(__('Unregister'),
								['action' => 'unregister', 'registration' => $registration['registration']->id],
								['confirm' => __('Are you sure you want to unregister from this event? This will delete all of your preferences and you may lose the spot that is currently tentatively reserved for you.')]
							);
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

echo $this->element('Payments/refund');
if (Configure::read('registration.online_payments') && stripos(Configure::read('payment.options'), 'interac')) {
	echo $this->Html->para('small', __('&reg; Trade-mark of Interac Inc. Used under licence. <a href="https://www.interaconline.com/learn/" target="_blank">Learn more</a> about INTERAC Online.'));
}
?>
</div>
