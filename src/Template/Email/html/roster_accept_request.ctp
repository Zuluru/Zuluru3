<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Team $team
 * @type string $captain
 * @type string $role
 */
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('{0} has accepted your request to join the roster of the {1} team {2} as a {3}.',
	$captain,
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	Configure::read("options.roster_role.$role")
) ?></p>
<?= $this->element('Email/html/footer');
