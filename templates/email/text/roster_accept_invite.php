<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Team $team
 * @var string $captains
 * @var string $role
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<?= __('Dear {0},', $captains) ?>


<?= __('Your invitation for {0} to join the roster of the {1} team {2} as a {3} has been accepted.',
	$person->full_name,
	Configure::read('organization.name'),
	$team->name,
	Configure::read("options.roster_role.$role")
) ?>


<?= __('The {0} roster may be accessed at', $team->name) ?>

<?= Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true) ?>


<?= __('You need to be logged into the website to update this.') ?>

<?= $this->element('email/text/footer');
