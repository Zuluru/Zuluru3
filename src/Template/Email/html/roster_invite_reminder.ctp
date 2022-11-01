<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type $person \App\Model\Entity\Person
 * @type $division \App\Model\Entity\Division
 * @type $team \App\Model\Entity\Team
 * @type $roster \App\Model\Entity\TeamsPerson
 * @type $sport string
 */

$min = $division ? Configure::read("sports.{$sport}.roster_requirements.{$division->ratio_rule}") : 0;
$min_text = ($min > 0 ? __(' (minimum of {0} rostered players)', $min) : '');
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('This is a reminder that you have been invited to join the roster of the {0} team {1} as a {2}.',
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	Configure::read("options.roster_role.{$roster->role}")
) ?></p>
<?php
if ($division):
?>
<p><?= __('{0} plays in the {1}.',
	$team->name,
	$this->element('Email/division')
) ?></p>
<?php
endif;
?>
<p><?= __('We ask that you please accept or decline this invitation at your earliest convenience. The invitation will expire {0} days from now.', $days) ?></p>
<p><?= __('If you accept the invitation, you will be added to the team\'s roster and your contact information will be made available to the team coaches and captains.') ?></p>
<p><?= __('Note that, before accepting the invitation, you must be a registered member of {0}.', Configure::read('organization.short_name')) ?></p>
<p><?= $this->Html->link(__('Accept the invitation'), Router::url(['controller' => 'Teams', 'action' => 'roster_accept', 'team' => $team->id, 'person' => $person->id, 'code' => $code], true)) ?></p>
<p><?= __('If you decline the invitation you will be removed from this team\'s roster and your contact information will not be made available to the coaches or captains. This protocol is in accordance with the {0} Privacy Policy.',
	Configure::read('organization.short_name'))
?></p>
<p><?= $this->Html->link(__('Decline the invitation'), Router::url(['controller' => 'Teams', 'action' => 'roster_decline', 'team' => $team->id, 'person' => $person->id, 'code' => $code], true)) ?></p>
<p><?= __('Please be advised that players are NOT considered a part of a team roster until they have accepted the invitation to join. The {0} roster must be completed{1} by the team roster deadline ({2}), and all team members must have accepted the invitation.',
	$team->name,
	$min_text,
	$division ? $this->Time->date($division->rosterDeadline()) : __('TBD')
) ?></p>
<?= $this->element('Email/html/footer');
