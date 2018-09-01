<?php
use Cake\Core\Configure;

/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Team $team
 * @type \App\Model\Entity\TeamsPerson $roster
 * @type string $captains
 */
?>

<?= __('Dear {0},', $captains) ?>


<?= __('Your invitation to {0} to join the roster of the {1} team {2} as a {3} was not responded to by the player in the allotted time, and has been removed.',
	$person->full_name,
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.{$roster->role}")
) ?>


<?= $this->element('Email/text/footer');
