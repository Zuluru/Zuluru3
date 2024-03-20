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

<?= __('Dear {0},', $person->first_name) ?>


<?= __('A payment of {0} has been received on your registration for {1} with {2}.',
	$this->Number->currency($payment->payment_amount),
	$event->name,
	Configure::read('organization.name')
) ?>


<?php
if ($registration->balance > 0):
?>
<?= __('This leaves you with a balance owing of {0}.', $registration->balance) ?>
<?php
else:
?>
<?= __('This registration is now fully paid.') ?>
<?php
endif;
?>


<?= __('Details of this registration can be {0}.',
	$this->Html->link(__('viewed any time'), Router::url(['plugin' => false, 'controller' => 'Registrations', 'action' => 'invoice', '?' => ['registration' => $registration->id]], true))
) ?>


<?= __('If you have any questions or concerns about this, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('Email/text/footer');
