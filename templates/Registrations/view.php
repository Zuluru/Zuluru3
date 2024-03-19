<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Registration'));
$this->Breadcrumbs->add($registration->person->full_name);
$this->Breadcrumbs->add($registration->event->name);
$this->Breadcrumbs->add(__('View'));
?>

<div class="registrations view">
	<h2><?= __('View Registration') ?></h2>
	<fieldset><legend><?= __('Registration Details') ?></legend>
		<dl class="dl-horizontal">
			<dt><?= __('Order ID') ?></dt>
			<dd><?= sprintf(Configure::read('registration.order_id_format'), $registration->id) ?></dd>
			<dt><?= __('Name') ?></dt>
			<dd><?= $this->element('People/block', ['person' => $registration->person]) ?></dd>
			<dt><?= __('User ID') ?></dt>
			<dd><?= $registration->person->id ?></dd>
			<dt><?= __('Event') ?></dt>
			<dd><?= $this->Html->link($registration->event->name, ['controller' => 'Events', 'action' => 'view', 'event' => $registration->event->id]) ?></dd>
			<dt><?= __('Total Amount') ?></dt>
			<dd><?= $this->Number->currency($registration->total_amount) ?></dd>
			<dt><?= __('Created') ?></dt>
			<dd><?= $this->Time->datetime($registration->created) ?></dd>
			<dt><?= __('Modified') ?></dt>
			<dd><?= $this->Time->datetime($registration->modified) ?></dd>
			<dt><?= __('Payment') ?></dt>
			<dd><?php
				echo $registration->payment;
				if ($registration->payment == 'Reserved' && $registration->reservation_expires) {
					echo ' ' . __('until {0}', $this->Time->datetime($registration->reservation_expires));
				}
			?></dd>
			<dt><?= __('Notes') ?></dt>
			<dd><?= $registration->notes ?>&nbsp;</dd>
		</dl>
	</fieldset>

<?php
if ($this->Authorize->getIdentity()->isManagerOf($registration->event)):
	foreach ($registration->payments as $payment):
?>
	<fieldset><legend><?= __('Payment') ?></legend>
		<div class="related row">
			<dl class="dl-horizontal">
			<dt><?= __('Payment Type') ?></dt>
			<dd><?= $payment->payment_type ?></dd>
			<dt><?= __('Payment Method') ?></dt>
			<dd><?= $payment->payment_method ?></dd>
			<dt><?= __('Payment Amount') ?></dt>
			<dd><?php
				echo $this->Number->currency($payment->payment_amount);
				if ($payment->refunded_amount != 0) {
					echo ' ' . $this->Html->tag('span', __('({0} refunded/credited; see below)', $this->Number->currency($payment->refunded_amount)), ['class' => 'warning-message']);
				}
			?></dd>
			<dt><?= __('Payment Date') ?></dt>
			<dd><?= $this->Time->date($payment->created) ?></dd>
<?php
		if (!empty($payment->notes)):
?>
			<dt><?= __('Notes') ?></dt>
			<dd><?= $payment->notes ?></dd>
<?php
		endif;

		if ($payment->created_person_id !== null):
?>
			<dt><?= __('Entered By') ?></dt>
			<dd><?= $this->UserCache->read('Person.full_name', $payment->created_person_id) ?></dd>
<?php
		endif;

		if ($payment->updated_person_id !== null):
?>
			<dt><?= __('Updated By') ?></dt>
			<dd><?= $this->UserCache->read('Person.full_name', $payment->updated_person_id) ?></dd>
<?php
		endif;

		if (!empty($payment->registration_audit)):
?>
			<dt><?= __('Response Code') ?></dt>
			<dd><?= $payment->registration_audit->response_code ?>&nbsp;</dd>
			<dt><?= __('ISO Code') ?></dt>
			<dd><?= $payment->registration_audit->iso_code ?>&nbsp;</dd>
			<dt><?= __('Date') ?></dt>
			<dd><?= $payment->registration_audit->date ?>&nbsp;</dd>
			<dt><?= __('Time') ?></dt>
			<dd><?= $payment->registration_audit->time ?>&nbsp;</dd>
			<dt><?= __('Transaction ID') ?></dt>
			<dd><?= $payment->registration_audit->transaction_id ?>&nbsp;</dd>
			<dt><?= __('Approval Code') ?></dt>
			<dd><?= $payment->registration_audit->approval_code ?>&nbsp;</dd>
			<dt><?= __('Transaction Name') ?></dt>
			<dd><?= $payment->registration_audit->transaction_name ?>&nbsp;</dd>
			<dt><?= __('Charge Total') ?></dt>
			<dd><?= $this->Number->currency($payment->registration_audit->charge_total) ?></dd>
			<dt><?= __('Cardholder') ?></dt>
			<dd><?= $payment->registration_audit->cardholder ?>&nbsp;</dd>
			<dt><?= __('Expiry') ?></dt>
			<dd><?= $payment->registration_audit->expiry ?>&nbsp;</dd>
			<dt><?= __('F4L4') ?></dt>
			<dd><?= $payment->registration_audit->f4l4 ?>&nbsp;</dd>
			<dt><?= __('Card') ?></dt>
			<dd><?= $payment->registration_audit->card ?>&nbsp;</dd>
			<dt><?= __('Message') ?></dt>
			<dd><?= $payment->registration_audit->message ?>&nbsp;</dd>
			<dt><?= __('Issuer') ?></dt>
			<dd><?= $payment->registration_audit->issuer ?>&nbsp;</dd>
			<dt><?= __('Issuer Invoice') ?></dt>
			<dd><?= $payment->registration_audit->issuer_invoice ?>&nbsp;</dd>
			<dt><?= __('Issuer Confirmation') ?></dt>
			<dd><?= $payment->registration_audit->issuer_confirmation ?>&nbsp;</dd>
<?php
		endif;
?>
		</dl>
	</div>
<?php
		if ($payment->payment_amount != $payment->refunded_amount && in_array($payment->payment_type, Configure::read('payment_payment'))):
?>
	<div class="actions columns">
		<ul class="nav nav-pills">
<?php
			echo $this->Html->tag('li', $this->Html->link(__('Issue Refund'), ['action' => 'refund_payment', 'payment' => $payment->id]));
			echo $this->Html->tag('li', $this->Html->link(__('Issue Credit'), ['action' => 'credit_payment', 'payment' => $payment->id]));
?>
		</ul>
	</div>
<?php
		endif;
?>
	</fieldset>

<?php
	endforeach;

	$unpaid = in_array($registration->payment, Configure::read('registration_unpaid')) && $registration->balance > 0;
	$total_payment = $registration->total_payment;
	$unaccounted = $registration->payment == 'Paid' && $total_payment != $registration->total_amount;
	if ($unpaid || $unaccounted):
?>
	<fieldset><legend><?= __('Balance') ?></legend>
		<div class="related">
			<p class="warning-message"><?php
				if ($unpaid) {
					echo __('There is an outstanding balance of {0}.', $this->Number->currency($registration->balance));
				} else {
					echo __('Although this registration is marked as "Paid", the total of the payments received ({0}) does not match the registration cost ({1}).',
						$this->Number->currency($total_payment), $this->Number->currency($registration->total_amount));
				}
			?></p>
		</div>
	</fieldset>
<?php
	endif;
endif;

if ($registration->event->questionnaire->has('questions')):
?>
	<fieldset><legend><?= __('Registration Answers') ?></legend>
		<div class="related">
			<?= $this->element('Questionnaires/view', ['questionnaire' => $registration->event->questionnaire, 'responses' => $registration->responses]) ?>
		</div>
	</fieldset>
<?php
endif;
?>
</div>

<div class="actions columns">
	<ul class="nav nav-pills">
<?= $this->element('Registrations/actions', ['registration' => $registration, 'format' => 'list']) ?>
	</ul>
</div>
