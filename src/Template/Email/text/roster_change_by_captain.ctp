<?php
use Cake\Core\Configure;

/**
 * @type $person \App\Model\Entity\Person
 * @type $team \App\Model\Entity\Team
 * @type $captain string
 * @type $old_role string
 * @type $role string
 * @type $reply string
 */
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('{0} has changed your role on the roster of the {1} team {2} from {3} to {4}.',
	$captain,
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.$old_role"),
	Configure::read("options.roster_role.$role")
) ?>


<?= __('This is a notification only, there is no action required on your part.') ?>


<?= __('If you believe that this has happened in error, please contact {0}.', $reply) ?>


<?= $this->element('Email/text/footer');
