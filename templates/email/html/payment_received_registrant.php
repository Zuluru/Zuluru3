<?php
/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\Event $event
 * @var \App\Model\Entity\Payment $payment
 */
use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('A payment of {0} has been received on your registration for {1} with {2}.',
	$this->Number->currency($payment->payment_amount),
	$event->name,
	Configure::read('organization.name')
) ?></p>
<?php
if ($registration->balance > 0):
?>
<p><?= __('This leaves you with a balance owing of {0}.', $registration->balance) ?></p>
<?php
else:
?>
<p><?= __('This registration is now fully paid.') ?></p>
<?php
endif;
?>
<p><?= __('Details of this registration can be {0}.',
	$this->Html->link(__('viewed any time'), Router::url(['plugin' => false, 'controller' => 'Registrations', 'action' => 'invoice', 'registration' => $registration->id], true))
) ?></p>
<p><?= __('If you have any questions or concerns about this, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('Email/html/footer');
