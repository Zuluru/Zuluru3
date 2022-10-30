<?php
use Cake\Core\Configure;

/**
 * @type $person \App\Model\Entity\Person
 * @type $team \App\Model\Entity\Team
 * @type $captain string
 */
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('{0} has declined your request to join the roster of the {1} team {2}.',
	$captain,
	Configure::read('organization.name'),
	$team->name
) ?>


<?= $this->element('Email/text/footer');
