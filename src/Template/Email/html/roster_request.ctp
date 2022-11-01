<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type $person \App\Model\Entity\Person
 * @type $division \App\Model\Entity\Division
 * @type $team \App\Model\Entity\Team
 * @type $roster \App\Model\Entity\TeamsPerson
 * @type $captains string
 * @type $role string
 * @type $sport string
 */

$min = Configure::read("sports.{$sport}.roster_requirements.{$division->ratio_rule}");
$min_text = ($min > 0 ? __(' (minimum of {0} rostered players)', $min) : '');
?>

<p><?= __('Dear {0},', $captains) ?></p>
<p><?= __('{0} has requested to join the roster of the {1} team {2} as a {3}.',
	$person->full_name,
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	Configure::read("options.roster_role.$role")
) ?></p>
<p><?= __('You need to be logged into the website to update this.') ?></p>
<p><?= __('We ask that you please accept or decline this request at your earliest convenience.') . ' ' . __('The request will expire after a couple of weeks.') ?></p>
<p><?= __('If you accept the request, {0} will be added to the team\'s roster as a {1}. You have the option of changing their role on the team afterwards.',
	$person->first_name,
	Configure::read("options.roster_role.$role")
) ?></p>
<p><?= $this->Html->link(__('Accept the request'), Router::url(['controller' => 'Teams', 'action' => 'roster_accept', 'team' => $team->id, 'person' => $person->id, 'code' => $code], true)) ?></p>
<p><?= __('If you decline the request they will be removed from this team\'s roster.') ?></p>
<p><?= $this->Html->link(__('Decline the request'), Router::url(['controller' => 'Teams', 'action' => 'roster_decline', 'team' => $team->id, 'person' => $person->id, 'code' => $code], true)) ?></p>
<p><?= __('Please be advised that players are NOT considered a part of a team roster until their request to join has been accepted by a coach or captain. The {0} roster must be completed{1} by the team roster deadline ({2}), and all team members must have been accepted by a coach or captain.',
	$team->name,
	$min_text,
	$division ? $this->Time->date($division->rosterDeadline()) : __('TBD')
) ?></p>
<?= $this->element('Email/html/footer');
