<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\TeamsPerson $roster
 */
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('You requested to join the roster of the {0} team {1} as a {2}.',
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	Configure::read("options.roster_role.{$roster->role}")
) ?></p>
<p><?= __('This request has not yet been responded to by a coach or captain, and will expire {0} days from now. An email has been sent to remind them, but you might want to get in touch directly as well.', $days) ?></p>
<?= $this->element('Email/html/footer');
