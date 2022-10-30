<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type $person \App\Model\Entity\Person
 * @type $team \App\Model\Entity\Team
 * @type $captains string
 * @type $role string
 */
?>

<p><?= __('Dear {0},', $captains) ?></p>
<p><?= __('Your invitation for {0} to join the roster of the {1} team {2} as a {3} has been accepted.',
	$person->full_name,
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	Configure::read("options.roster_role.$role")
) ?></p>
<p><?= __('You need to be logged into the website to update this.') ?></p>
<?= $this->element('Email/html/footer');
