<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('You registered for {0} on {1}, but have not yet paid.',
	$event->name,
	$this->Time->date($registration->created)
) ?>


<?= __('This event has now filled up, and in accordance with {0} policy, your registration has been moved to the waiting list in case a spot opens up.',
		Configure::read('organization.short_name')
) ?>


<?php
if (Configure::read('registration.reservation_time') > 0):
?>
<?= __('If you are still interested in participating in this event, please monitor your email; if a spot opens up, you will be notified by email, but it will only be held for you for {0} hours.',
	Configure::read('registration.reservation_time')
) ?>


<?php
endif;
?>
<?= __('If you are no longer interested in participating in this event, please remove yourself from the waiting list at') ?>

<?= Router::url(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registration->id], true) ?>


<?= ('This will help to ensure that those who are still interested get served promptly if and when a spot opens up.') ?>


<?= __('If you have any questions or concerns about this, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('Email/text/footer');
