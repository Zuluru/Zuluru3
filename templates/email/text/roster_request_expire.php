<?php
use Cake\Core\Configure;

/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\TeamsPerson $roster
 */
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('Your request to join the roster of the {0} team {1} as a {2} was not responded to by a coach or captain within the allotted time, and has been removed.',
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.{$roster->role}")
) ?>


<?= $this->element('Email/text/footer');
