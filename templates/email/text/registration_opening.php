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

<?= __('Dear {0},', $person->first_name) ?>


<?= __('You are first on the waiting list for {0}, and a spot has opened up. This spot has been reserved for you{1}.',
	$event->name, $reservation_text
) ?>


<?= __('To confirm your position, simply pay for this registration at') ?>

<?= Router::url(['controller' => 'Registrations', 'action' => 'checkout'], true) ?>


<?= __('If you are no longer interested in participating in this event, please remove yourself from the waiting list at') ?>

<?= Router::url(['controller' => 'Registrations', 'action' => 'unregister', '?' => ['registration' => $registration->id]], true) ?>


<?= ('This will help to ensure that those who are still interested get offered the spot promptly.') ?>


<?= __('If you have any questions or concerns about this, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('email/text/footer');
