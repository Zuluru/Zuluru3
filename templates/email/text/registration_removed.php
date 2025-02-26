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


<?= __('You registered for {0} on {1}, but have not yet paid.',
	$event->name,
	$this->Time->date($registration->created)
) ?>


<?= __('This event has now filled up, and in accordance with {0} policy, your registration has been removed.',
		Configure::read('organization.short_name')) . ' ' .
	__('If you wish to be placed on the waiting list in case a spot opens up, you can re-register for this at')
?>

<?= Router::url(['controller' => 'Events', 'action' => 'view', '?' => ['event' => $event->id]], true) ?>


<?= __('If you have any questions or concerns about this, please contact {0}.',
	__('{0} at {1}', Configure::read('email.admin_name'), Configure::read('email.admin_email'))
) ?>


<?= $this->element('email/text/footer');
