<?php
use Cake\Core\Configure;

/**
 * @type $person \App\Model\Entity\Person
 * @type $team \App\Model\Entity\Team
 * @type $roster \App\Model\Entity\TeamsPerson
 */
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('You requested to join the roster of the {0} team {1} as a {2}.',
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.{$roster->role}")
) ?>


<?= __('This request has not yet been responded to by a coach or captain, and will expire {0} days from now. An email has been sent to remind them, but you might want to get in touch directly as well.', $days) ?>


<?= $this->element('Email/text/footer');
