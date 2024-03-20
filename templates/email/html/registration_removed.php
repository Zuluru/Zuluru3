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
<p><?= __('This event has now filled up, and in accordance with {0} policy, your registration has been removed.',
		Configure::read('organization.short_name')) . ' ' .
	__('If you wish to be placed on the waiting list in case a spot opens up, you can {0}.',
		$this->Html->link(__('re-register for this'), Router::url(['controller' => 'Events', 'action' => 'view', '?' => ['event' => $event->id]], true)))
?></p>
<p><?= __('If you have any questions or concerns about this, please contact {0}.',
	$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
) ?></p>
<?= $this->element('Email/html/footer');
