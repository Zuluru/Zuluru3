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

<p><?= __('Dear {0},', $relative->first_name) ?></p>
<p><?= __('{0} has indicated on the {1} web site that you are related to them. You have the opportunity to accept or decline this.',
	$person->full_name,
	Configure::read('organization.name')
) ?></p>
<p><?= __('If you accept, {0} will be granted access to see your schedule and perform various tasks in the system on your behalf. You can always remove this later on if you change your mind.',
	$person->first_name
) ?></p>
<p><?= $this->Html->link(__('Accept the request'),
	Router::url(['controller' => 'People', 'action' => 'approve_relative', 'person' => $relative->id, 'relative' => $person->id, 'code' => $code], true)
) ?></p>
<p><?= __('If you decline, {0} will not have any additional access to your account.', $person->first_name) ?></p>
<p><?= $this->Html->link(__('Decline the request'),
	Router::url(['controller' => 'People', 'action' => 'remove_relative', 'person' => $relative->id, 'relative' => $person->id, 'code' => $code], true)
) ?></p>
<?= $this->element('Email/html/footer');
