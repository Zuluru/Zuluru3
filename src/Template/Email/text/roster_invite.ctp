<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Division $division
 * @type \App\Model\Entity\Team $team
 * @type string $captain
 * @type string $role
 * @type string $sport
 */

$min = Configure::read("sports.{$sport}.roster_requirements.{$division->ratio_rule}");
$min_text = ($min > 0 ? __(' (minimum of {0} rostered players)', $min) : '');
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('{0} has invited you to join the roster of the {1} team {2} as a {3}.',
	$captain,
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.$role")
) ?>


<?= __('{0} plays in the {1}.',
	$team->name,
	$this->element('Email/division')
) ?>


<?= __('More details about {0} may be found at', $team->name) ?>

<?= Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true) ?>


<?= __('We ask that you please accept or decline this invitation at your earliest convenience. The invitation will expire after a couple of weeks.') ?>


<?= __('If you accept the invitation, you will be added to the team\'s roster and your contact information will be made available to the team coaches and captains.') ?>


<?= __('Note that, before accepting the invitation, you must be a registered member of {0}.', Configure::read('organization.short_name')) ?>


<?php
if (isset($accept_warning)) {
	echo __('The system has also generated this warning which must be resolved before you can accept this invitation:');
	echo "\n";
	echo $this->Html->formatMessage($accept_warning, null, true);
	echo "\n";
}
?>
<?= __('Accept the invitation here:') ?>

<?= Router::url(['controller' => 'Teams', 'action' => 'roster_accept', 'team' => $team->id, 'person' => $person->id, 'code' => $code], true) ?>


<?= __('If you decline the invitation you will be removed from this team\'s roster and your contact information will not be made available to the coaches or captains. This protocol is in accordance with the {0} Privacy Policy.',
	Configure::read('organization.short_name'))
?>


<?= __('Decline the invitation here:') ?>

<?= Router::url(['controller' => 'Teams', 'action' => 'roster_decline', 'team' => $team->id, 'person' => $person->id, 'code' => $code], true) ?>


<?= __('Please be advised that players are NOT considered a part of a team roster until they have accepted the invitation to join. The {0} roster must be completed{1} by the team roster deadline ({2}), and all team members must have accepted the invitation.',
	$team->name,
	$min_text,
	$division ? $this->Time->date($division->rosterDeadline()) : __('TBD')
) ?>


<?= $this->element('Email/text/footer');
