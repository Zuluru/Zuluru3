<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Team $team
 * @var string $captains
 */
?>

<p><?= __('Dear {0},', $captains) ?></p>
<p><?= __('Your invitation for {0} to join the roster of the {1} team {2} has been declined.',
	$person->full_name,
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true))
) ?></p>
<p><?= __('You need to be logged into the website to update this.') ?></p>
<?= $this->element('Email/html/footer');
