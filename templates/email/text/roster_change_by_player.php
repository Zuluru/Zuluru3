<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Team $team
 * @var string $captains
 * @var string $old_role
 * @var string $role
 * @var string $reply
 */

use Cake\Core\Configure;
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


<?= $this->element('email/text/footer');
