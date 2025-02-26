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

<p><?= __('{0} has made an online payment of {1} on their registration for {2}.',
	$person->full_name,
	$this->Number->currency($payment->payment_amount),
	$event->name
) ?></p>
<?php
if ($registration->balance > 0):
?>
<p><?= __('This leaves them with a balance owing of {0}.', $registration->balance) ?></p>
<?php
else:
?>
<p><?= __('This registration is now fully paid.') ?></p>
<?php
endif;
?>
<p><?= __('Details of this registration can be {0}.',
	$this->Html->link(__('viewed any time'), Router::url(['plugin' => false, 'controller' => 'Registrations', 'action' => 'view', '?' => ['registration' => $registration->id]], true))
) ?></p>
<?= $this->element('email/html/footer');
