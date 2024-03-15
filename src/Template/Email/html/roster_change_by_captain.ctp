<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Team $team
 * @var string $captain
 * @var string $old_role
 * @var string $role
 * @var string $reply
 */
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('{0} has changed your role on the roster of the {1} team {2} from {3} to {4}.',
	$captain,
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	Configure::read("options.roster_role.$old_role"),
	Configure::read("options.roster_role.$role")
) ?></p>
<p><?= __('This is a notification only, there is no action required on your part.') ?></p>
<p><?= __('If you believe that this has happened in error, please contact {0}.', $reply) ?></p>
<?= $this->element('Email/html/footer');
