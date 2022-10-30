<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type $person \App\Model\Entity\Person
 * @type $team \App\Model\Entity\Team
 * @type $role string
 * @type $reply string
 */
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('You have been added to the roster of the {0} team {1} as a {2}.',
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	Configure::read("options.roster_role.$role")
) ?></p>
<p><?= __('{0} plays in the {1}.',
	$team->name,
	$this->element('Email/division')
) ?></p>
<p><?= __('If you believe that this has happened in error, please contact {0}.', $reply) ?></p>
<?= $this->element('Email/html/footer');
