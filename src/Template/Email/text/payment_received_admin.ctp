<?php
/**
 * @type $person \App\Model\Entity\Person
 * @type $registration \App\Model\Entity\Registration
 * @type $event \App\Model\Entity\Event
 * @type $payment \App\Model\Entity\Payment
 */
use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<?= __('{0} has made an online payment of {1} on their registration for {2}.',
	$person->full_name,
	$this->Number->currency($payment->payment_amount),
	$event->name
) ?>


<?php
if ($registration->balance > 0):
?>
<?= __('This leaves them with a balance owing of {0}.', $registration->balance) ?>
<?php
else:
?>
<?= __('This registration is now fully paid.') ?>
<?php
endif;
?>


<?= __('Details of this registration can be {0}.',
	$this->Html->link(__('viewed any time'), Router::url(['plugin' => false, 'controller' => 'Registrations', 'action' => 'view', 'registration' => $registration->id], true))
) ?>


<?= $this->element('Email/text/footer');
