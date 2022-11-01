<?php
use Cake\Core\Configure;

/**
 * @type $person \App\Model\Entity\Person
 * @type $team \App\Model\Entity\Team
 * @type $captain string
 * @type $role string
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
