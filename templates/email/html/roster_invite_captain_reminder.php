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

$min = $division ? Configure::read("sports.{$sport}.roster_requirements.{$division->ratio_rule}") : 0;
$min_text = ($min > 0 ? __(' (minimum of {0} rostered players)', $min) : '');
?>

<p><?= __('Dear {0},', $captains) ?></p>
<p><?= __('You invited {0} to join the roster of the {1} team {2} as a {3}.',
	$person->full_name,
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
	Configure::read("options.roster_role.{$roster->role}")
) ?></p>
<p><?= __('This invitation has not yet been responded to by the player, and will expire {0} days from now. An email has been sent to remind them, but you might want to get in touch directly as well.',
	$days
) ?></p>
<p><?= __('Please be advised that players are NOT considered a part of a team roster until your invitation to join has been accepted. The {0} roster must be completed{1} by the team roster deadline ({2}), and all team members must have been accepted by the captain.',
	$team->name,
	$min_text,
	$division ? $this->Time->date($division->rosterDeadline()) : __('TBD')
) ?></p>
<?= $this->element('email/html/footer');
