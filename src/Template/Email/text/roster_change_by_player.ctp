<?php
use Cake\Core\Configure;

/**
 * @type $person \App\Model\Entity\Person
 * @type $team \App\Model\Entity\Team
 * @type $captains string
 * @type $old_role string
 * @type $role string
 * @type $reply string
 */
?>

<?= __('Dear {0},', $captains) ?>


<?= __('{0} has changed their role on the roster of the {1} team {2} from {3} to {4}.',
	$person->full_name,
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.$old_role"),
	Configure::read("options.roster_role.$role")
) ?>


<?= __('This is a notification only, there is no action required on your part.') ?>


<?= __('If you believe that this has happened in error, please contact {0}.', $reply) ?>


<?= $this->element('Email/text/footer');
