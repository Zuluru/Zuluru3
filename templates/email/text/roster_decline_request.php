<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Team $team
 * @var string $captain
 */

use Cake\Core\Configure;
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('{0} has declined your request to join the roster of the {1} team {2}.',
	$captain,
	Configure::read('organization.name'),
	$team->name
) ?>


<?= $this->element('email/text/footer');
