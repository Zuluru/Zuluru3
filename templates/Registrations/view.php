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
		<dl class="row">
			<dt class="col-sm-2 text-end"><?= __('Order ID') ?></dt>
			<dd class="col-sm-10 mb-0"><?= sprintf(Configure::read('registration.order_id_format'), $registration->id) ?></dd>
			<dt class="col-sm-2 text-end"><?= __('Name') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $this->element('People/block', ['person' => $registration->person]) ?></dd>
			<dt class="col-sm-2 text-end"><?= __('User ID') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $registration->person->id ?></dd>
			<dt class="col-sm-2 text-end"><?= __('Event') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $this->Html->link($registration->event->name, ['controller' => 'Events', 'action' => 'view', '?' => ['event' => $registration->event->id]]) ?></dd>
			<dt class="col-sm-2 text-end"><?= __('Total Amount') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $this->Number->currency($registration->total_amount) ?></dd>
			<dt class="col-sm-2 text-end"><?= __('Created') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $this->Time->datetime($registration->created) ?></dd>
			<dt class="col-sm-2 text-end"><?= __('Modified') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $this->Time->datetime($registration->modified) ?></dd>
			<dt class="col-sm-2 text-end"><?= __('Payment') ?></dt>
			<dd class="col-sm-10 mb-0"><?php
				echo $registration->payment;
				if ($registration->payment == 'Reserved' && $registration->reservation_expires) {
					echo ' ' . __('until {0}', $this->Time->datetime($registration->reservation_expires));
				}
			?></dd>
			<dt class="col-sm-2 text-end"><?= __('Notes') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $registration->notes ?>&nbsp;</dd>
		</dl>
	</fieldset>

<?php
if ($this->Authorize->getIdentity()->isManagerOf($registration->event)):
	foreach ($registration->payments as $payment):
?>
	<fieldset><legend><?= __('Payment') ?></legend>
		<div class="related row">
			<dl class="row">
			<dt class="col-sm-2 text-end"><?= __('Payment Type') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->payment_type ?></dd>
			<dt class="col-sm-2 text-end"><?= __('Payment Method') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->payment_method ?></dd>
			<dt class="col-sm-2 text-end"><?= __('Payment Amount') ?></dt>
			<dd class="col-sm-10 mb-0"><?php
				echo $this->Number->currency($payment->payment_amount);
				if ($payment->refunded_amount != 0) {
					echo ' ' . $this->Html->tag('span', __('({0} refunded/credited; see below)', $this->Number->currency($payment->refunded_amount)), ['class' => 'warning-message']);
				}
			?></dd>
			<dt class="col-sm-2 text-end"><?= __('Payment Date') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $this->Time->date($payment->created) ?></dd>
<?php
		if (!empty($payment->notes)):
?>
			<dt class="col-sm-2 text-end"><?= __('Notes') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->notes ?></dd>
<?php
		endif;

		if ($payment->created_person_id !== null):
?>
			<dt class="col-sm-2 text-end"><?= __('Entered By') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $this->UserCache->read('Person.full_name', $payment->created_person_id) ?></dd>
<?php
		endif;

		if ($payment->updated_person_id !== null):
?>
			<dt class="col-sm-2 text-end"><?= __('Updated By') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $this->UserCache->read('Person.full_name', $payment->updated_person_id) ?></dd>
<?php
		endif;

		if (!empty($payment->registration_audit)):
?>
			<dt class="col-sm-2 text-end"><?= __('Response Code') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->response_code ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('ISO Code') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->iso_code ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Date') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->date ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Time') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->time ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Transaction ID') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->transaction_id ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Approval Code') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->approval_code ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Transaction Name') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->transaction_name ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Charge Total') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $this->Number->currency($payment->registration_audit->charge_total) ?></dd>
			<dt class="col-sm-2 text-end"><?= __('Cardholder') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->cardholder ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Expiry') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->expiry ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('F4L4') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->f4l4 ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Card') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->card ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Message') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->message ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Issuer') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->issuer ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Issuer Invoice') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->issuer_invoice ?>&nbsp;</dd>
			<dt class="col-sm-2 text-end"><?= __('Issuer Confirmation') ?></dt>
			<dd class="col-sm-10 mb-0"><?= $payment->registration_audit->issuer_confirmation ?>&nbsp;</dd>
<?php
		endif;
?>
		</dl>
	</div>
<?php
		if ($payment->payment_amount != $payment->refunded_amount && in_array($payment->payment_type, Configure::read('payment_payment'))):
?>
	<div class="actions columns">
<?php
			echo $this->Bootstrap->navPills([
				$this->Html->link(__('Issue Refund'),
					['action' => 'refund_payment', '?' => ['payment' => $payment->id]],
					['class' => $this->Bootstrap->navPillLinkClasses()]
				),
				$this->Html->link(__('Issue Credit'),
					['action' => 'credit_payment', '?' => ['payment' => $payment->id]],
					['class' => $this->Bootstrap->navPillLinkClasses()]
				),
			]);
?>
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
<?= $this->element('Registrations/actions', ['registration' => $registration, 'format' => 'list']) ?>
</div>
