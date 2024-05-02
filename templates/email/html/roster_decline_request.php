<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Team $team
 * @var string $captain
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('{0} has declined your request to join the roster of the {1} team {2}.',
	$captain,
	Configure::read('organization.name'),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true))
) ?></p>
<?= $this->element('email/html/footer');
