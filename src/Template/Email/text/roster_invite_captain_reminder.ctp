<?php
use Cake\Core\Configure;

/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Division $division
 * @type \App\Model\Entity\Team $team
 * @type \App\Model\Entity\TeamsPerson $roster
 * @type string $captains
 * @type string $sport
 */

$min = $division ? Configure::read("sports.{$sport}.roster_requirements.{$division->ratio_rule}") : 0;
$min_text = ($min > 0 ? __(' (minimum of {0} rostered players)', $min) : '');
?>

<?= __('Dear {0},', $captains) ?>


<?= __('You invited {0} to join the roster of the {1} team {2} as a {3}.',
	$person->full_name,
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.{$roster->role}")
) ?>


<?= __('This invitation has not yet been responded to by the player, and will expire {0} days from now. An email has been sent to remind them, but you might want to get in touch directly as well.',
	$days
) ?>


<?= __('Please be advised that players are NOT considered a part of a team roster until your invitation to join has been accepted. The {0} roster must be completed{1} by the team roster deadline ({2}), and all team members must have been accepted by the captain.',
	$team->name,
	$min_text,
	$division ? $this->Time->date($division->rosterDeadline()) : __('TBD')
) ?>


<?= $this->element('Email/text/footer');
