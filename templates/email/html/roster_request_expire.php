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
<p><?= __('Your request to join the roster of the {0} team {1} as a {2} was not responded to by a coach or captain within the allotted time, and has been removed.',
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	Configure::read("options.roster_role.{$roster->role}")
) ?></p>
<?= $this->element('Email/html/footer');
