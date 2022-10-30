<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type $person \App\Model\Entity\Person
 * @type $team \App\Model\Entity\Team
 * @type $captain string
 * @type $old_role string
 * @type $role string
 * @type $reply string
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
