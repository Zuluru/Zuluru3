<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\TeamsPerson $roster
 * @var string $captains
 * @var string $sport
 */

$min = Configure::read("sports.{$sport}.roster_requirements.{$division->ratio_rule}");
$min_text = ($min > 0 ? __(' (minimum of {0} rostered players)', $min) : '');
?>

<?= __('Dear {0},', $captains) ?>


<?= __('{0} has requested to join the roster of the {1} team {2} as a {3}.',
	$person->full_name,
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.{$roster->role}")
) ?>


<?= __('The {0} roster may be accessed at', $team->name) ?>

<?= Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true) ?>


<?= __('You need to be logged into the website to update this.') ?>


<?= __('We ask that you please accept or decline this request at your earliest convenience.') . ' ' . __('The request will expire {0} days from now.', $days) ?>


<?= __('If you accept the request, {0} will be added to the team\'s roster as a {1}. You have the option of changing their role on the team afterwards.',
	$person->first_name,
	Configure::read("options.roster_role.{$roster->role}")
) ?>


<?= __('Accept the request here:') ?>

<?= Router::url(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['team' => $team->id, 'person' => $person->id, 'code' => $code]], true) ?>


<?= __('If you decline the request they will be removed from this team\'s roster.') ?>


<?= __('Decline the request here:') ?>

<?= Router::url(['controller' => 'Teams', 'action' => 'roster_decline', '?' => ['team' => $team->id, 'person' => $person->id, 'code' => $code]], true) ?>


<?= __('Please be advised that players are NOT considered a part of a team roster until their request to join has been accepted by a coach or captain. The {0} roster must be completed{1} by the team roster deadline ({2}), and all team members must have been accepted by a coach or captain.',
	$team->name,
	$min_text,
	$division ? $this->Time->date($division->rosterDeadline()) : __('TBD')
) ?>


<?= $this->element('Email/text/footer');
