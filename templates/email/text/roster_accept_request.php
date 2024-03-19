<?php
use Cake\Core\Configure;

/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Team $team
 * @var string $captain
 * @var string $role
 */
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('{0} has accepted your request to join the roster of the {1} team {2} as a {3}.',
	$captain,
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.$role")
) ?>


<?= $this->element('Email/text/footer');
