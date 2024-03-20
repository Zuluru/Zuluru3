<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Person $relative
 * @var string $code
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<?= __('Dear {0},', $relative->first_name) ?>


<?= __('{0} has indicated on the {1} web site that you are related to them. You have the opportunity to accept or decline this.',
	$person->full_name,
	Configure::read('organization.name')
) ?>


<?= __('If you accept, {0} will be granted access to see your schedule and perform various tasks in the system on your behalf. You can always remove this later on if you change your mind.',
	$person->first_name
) ?>


<?= __('Accept the request here:') ?>

<?= Router::url(['controller' => 'People', 'action' => 'approve_relative', '?' => ['person' => $relative->id, 'relative' => $person->id, 'code' => $code]], true) ?>


<?= __('If you decline, {0} will not have any additional access to your account.', $person->first_name) ?>


<?= __('Decline the request here:') ?>

<?= Router::url(['controller' => 'People', 'action' => 'remove_relative', '?' => ['person' => $relative->id, 'relative' => $person->id, 'code' => $code]], true) ?>


<?= $this->element('Email/text/footer');
