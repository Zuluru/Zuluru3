<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\Team $team
 * @var string $captain
 * @var string $role
 * @var string $sport
 */

$min = $division ? Configure::read("sports.{$sport}.roster_requirements.{$division->ratio_rule}") : 0;
$min_text = ($min > 0 ? __(' (minimum of {0} rostered players)', $min) : '');
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('{0} has invited you to join the roster of the {1} team {2} as a {3}.',
	$captain,
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
	Configure::read("options.roster_role.$role")
) ?></p>
<?php
if ($division):
?>
<p><?= __('{0} plays in the {1}.',
	$team->name,
	$this->element('email/division')
) ?></p>
<?php
endif;
?>
<p><?= __('We ask that you please accept or decline this invitation at your earliest convenience. The invitation will expire after a couple of weeks.') ?></p>
<p><?= __('If you accept the invitation, you will be added to the team\'s roster and your contact information will be made available to the team coaches and captains.') ?></p>
<p><?= __('Note that, before accepting the invitation, you must be a registered member of {0}.', Configure::read('organization.short_name')) ?></p>
<?php
if (isset($accept_warning)):
?>
<p><?= __('The system has also generated this warning which must be resolved before you can accept this invitation:') ?>
<br /><?= $this->Html->formatMessage($accept_warning, null, false, true) ?></p>
<?php
endif;
?>
<p><?= $this->Html->link(__('Accept the invitation'), Router::url(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['team' => $team->id, 'person' => $person->id, 'code' => $code]], true)) ?></p>
<p><?= __('If you decline the invitation you will be removed from this team\'s roster and your contact information will not be made available to the coaches or captains. This protocol is in accordance with the {0} Privacy Policy.',
	Configure::read('organization.short_name'))
?></p>
<p><?= $this->Html->link(__('Decline the invitation'), Router::url(['controller' => 'Teams', 'action' => 'roster_decline', '?' => ['team' => $team->id, 'person' => $person->id, 'code' => $code]], true)) ?></p>
<p><?= __('Please be advised that players are NOT considered a part of a team roster until they have accepted the invitation to join. The {0} roster must be completed{1} by the team roster deadline ({2}), and all team members must have accepted the invitation.',
	$team->name,
	$min_text,
	$division ? $this->Time->date($division->rosterDeadline()) : __('TBD')
) ?></p>
<?= $this->element('email/html/footer');
