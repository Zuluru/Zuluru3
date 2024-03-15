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
<p><?= __('Your reservation for {0} has expired.', $event->name) ?></p>
<?php
if ($registration->payment == 'Unpaid'):
?>
<p><?= __('There may still be openings available, but these are now on a first-come, first-served basis.') . ' ' .
	__('You can {0} or to confirm your position, {1}.',
		$this->Html->link(__('check availability'), Router::url(['controller' => 'Events', 'action' => 'view', 'event' => $event->id], true)),
		$this->Html->link(__('pay for this registration'), Router::url(['controller' => 'Registrations', 'action' => 'checkout'], true))
	) . ' ' .
	__('Remember that this may fill up at any time.')
?></p>
<p><?= __('If you are no longer interested in participating in this event, please {0}.',
		$this->Html->link(__('remove yourself from the waiting list'),
			Router::url(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registration->id], true))) . ' ' .
	__('This will help to ensure that those who are still interested get offered the spot promptly.');
?></p>
<?php
elseif ($registration->payment == 'Waiting'):
?>
<p><?= __('This event has now filled up, and your registration has been moved to the waiting list in case a spot opens up.') ?></p>
<p><?= __('If you are no longer interested in participating in this event, please {0}.',
		$this->Html->link(__('remove yourself from the waiting list'),
			Router::url(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registration->id], true))) . ' ' .
	__('This will help to ensure that those who are still interested get served promptly if and when a spot opens up.');
?></p>
<?php
else:
?>
<p><?= __('As you did not confirm your position with a payment in time, your registration has been removed.') . ' ' .
	__('If you wish to be placed on the waiting list in case a spot opens up, you can {0}.',
		$this->Html->link(__('re-register for this'),
			Router::url(['controller' => 'Events', 'action' => 'view', 'event' => $event->id], true))
	)
?></p>
<?php
endif;
?>
<p><?= __('If you have any questions or concerns about this, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('Email/html/footer');
