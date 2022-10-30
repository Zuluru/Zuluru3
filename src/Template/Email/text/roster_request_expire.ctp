<?php
use Cake\Core\Configure;

/**
 * @type $person \App\Model\Entity\Person
 * @type $team \App\Model\Entity\Team
 * @type $roster \App\Model\Entity\TeamsPerson
 */
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('Your request to join the roster of the {0} team {1} as a {2} was not responded to by a coach or captain within the allotted time, and has been removed.',
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.{$roster->role}")
) ?>


<?= $this->element('Email/text/footer');
