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

<?= __('Dear {0},', $person->first_name) ?>


<?= __('You have been added to the roster of the {0} team {1} as a {2}.',
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.$role")
) ?>


<?= __('{0} plays in the {1}.',
	$team->name,
	$this->element('Email/division')
) ?>


<?= __('More details about {0} may be found at', $team->name) ?>

<?= Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true) ?>


<?= __('If you believe that this has happened in error, please contact {0}.', $reply) ?>


<?= $this->element('Email/text/footer');
