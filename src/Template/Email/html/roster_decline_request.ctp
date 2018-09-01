<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Team $team
 * @type string $captain
 */
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('{0} has declined your request to join the roster of the {1} team {2}.',
	$captain,
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true))
) ?></p>
<?= $this->element('Email/html/footer');
