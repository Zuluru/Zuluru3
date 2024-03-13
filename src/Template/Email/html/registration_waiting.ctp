<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\Event $event
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('You registered for {0} on {1}, but have not yet paid.',
	$event->name,
	$this->Time->date($registration->created)
) ?></p>
<p><?= __('This event has now filled up, and in accordance with {0} policy, your registration has been moved to the waiting list in case a spot opens up.',
		Configure::read('organization.short_name')
) ?></p>
<?php
if (Configure::read('registration.reservation_time') > 0):
?>
<p><?= __('If you are still interested in participating in this event, please monitor your email; if a spot opens up, you will be notified by email, but it will only be held for you for {0} hours.',
	Configure::read('registration.reservation_time')
) ?></p>
<?php
endif;
?>
<p><?= __('If you are no longer interested in participating in this event, please {0}.',
		$this->Html->link(__('remove yourself from the waiting list'),
			Router::url(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registration->id], true))) . ' ' .
	__('This will help to ensure that those who are still interested get served promptly if and when a spot opens up.');
?></p>
<p><?= __('If you have any questions or concerns about this, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('Email/html/footer');
