<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\Event $event
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

if (Configure::read('registration.reservation_time') > 0) {
	$reservation_text = __(' for {0} hours (your reservation will expire and your registration will be deleted at {1})',
		Configure::read('registration.reservation_time'),
		$this->Time->datetime($registration->reservation_expires)
	);
} else {
	$reservation_text = '';
}
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('You are first on the waiting list for {0}, and a spot has opened up. This spot has been reserved for you{1}.',
	$event->name, $reservation_text
) ?></p>
<p><?= __('To confirm your position, simply {0}.',
	$this->Html->link(__('pay for this registration'), Router::url(['controller' => 'Registrations', 'action' => 'checkout'], true))
) ?></p>
<p><?= __('If you are no longer interested in participating in this event, please {0}.',
		$this->Html->link(__('remove yourself from the waiting list'),
			Router::url(['controller' => 'Registrations', 'action' => 'unregister', '?' => ['registration' => $registration->id]], true))) . ' ' .
	__('This will help to ensure that those who are still interested get offered the spot promptly.');
?></p>
<p><?= __('If you have any questions or concerns about this, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('email/html/footer');
