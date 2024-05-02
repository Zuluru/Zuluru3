<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\Event $event
 * @var \App\Model\Entity\Payment $payment
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


<?= __('Details of this registration can be viewed any time at') ?>

<?= Router::url(['plugin' => false, 'controller' => 'Registrations', 'action' => 'view', '?' => ['registration' => $registration->id]], true) ?>


<?= $this->element('email/text/footer');
