<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Event $event
 * @var \App\Model\Entity\Registration $registration
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('Your reservation for {0} has expired.', $event->name) ?>


<?php
if ($registration->payment == 'Unpaid'):
?>
<?= __('There may still be openings available, but these are now on a first-come, first-served basis.') . ' ' .
	__('You can check availability at')
?>

<?= Router::url(['controller' => 'Events', 'action' => 'view', 'event' => $event->id], true) ?>

<?= __('or to confirm your position, pay for this registration at') ?>

<?= Router::url(['controller' => 'Registrations', 'action' => 'checkout'], true) ?>


<?= __('Remember that this may fill up at any time.') ?>


<?= __('If you are no longer interested in participating in this event, please unregister at') ?>

<?= Router::url(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registration->id], true) ?>


<?= ('This will help to ensure that those who are still interested get offered the spot promptly.') ?>


<?php
elseif ($registration->payment == 'Waiting'):
?>
<?= __('This event has now filled up, and your registration has been moved to the waiting list in case a spot opens up.') ?>


<?= __('If you are no longer interested in participating in this event, please remove yourself from the waiting list at') ?>

<?= Router::url(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registration->id], true) ?>


<?= ('This will help to ensure that those who are still interested get served promptly if and when a spot opens up.') ?>


<?php
else:
?>
<?= __('As you did not confirm your position with a payment in time, your registration has been removed.') . ' ' .
	__('If you wish to be placed on the waiting list in case a spot opens up, you can re-register for this at')
?>

<?= Router::url(['controller' => 'Events', 'action' => 'view', 'event' => $event->id], true) ?>


<?php
endif;
?>
<?= __('If you have any questions or concerns about this, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('Email/text/footer');
