<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Team $team
 * @var string $captains
 * @var string $old_role
 * @var string $reply
 */
?>

<p><?= __('Dear {0},', $captains) ?></p>
<p><?= __('{0} has removed themselves from the roster of the {1} team {2}. They were previously listed as a {3}.',
	$person->full_name,
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
	Configure::read("options.roster_role.$old_role")
) ?></p>
<p><?= __('This is a notification only, there is no action required on your part.') ?></p>
<p><?= __('If you believe that this has happened in error, please contact {0}.', $reply) ?></p>
<?= $this->element('email/html/footer');
