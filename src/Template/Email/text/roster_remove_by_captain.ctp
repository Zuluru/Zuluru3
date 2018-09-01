<?php
use Cake\Core\Configure;

/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Team $team
 * @type string $captain
 * @type string $old_role
 * @type string $reply
 */
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('{0} has removed you from the roster of the {1} team {2}. You were previously listed as a {3}.',
	$captain,
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.$old_role")
) ?>


<?= __('This is a notification only, there is no action required on your part.') ?>


<?= __('If you believe that this has happened in error, please contact {0}.', $reply) ?>


<?= $this->element('Email/text/footer');
