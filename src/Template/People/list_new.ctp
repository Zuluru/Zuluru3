<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person[] $new
 */

use App\Controller\AppController;
use Cake\Core\Configure;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb(__('New Accounts'));
?>

<div class="people list_new">
<h2><?= __('New Accounts') ?></h2>

<?php
$rows = [];
foreach ($new as $person) {
	$links = [
		$this->Html->iconLink('view_24.png',
			['action' => 'view', 'person' => $person->id],
			['alt' => __('View'), 'title' => __('View')]),
		$this->Html->iconLink('edit_24.png',
			['action' => 'edit', 'person' => $person->id, 'return' => AppController::_return()],
			['alt' => __('Edit'), 'title' => __('Edit')]),
		$this->Html->link(__('Approve'), ['action' => 'approve', 'person' => $person->id]),
		$this->Form->iconPostLink('delete_24.png',
			['action' => 'delete', 'person' => $person->id, 'return' => AppController::_return()],
			['alt' => __('Delete'), 'title' => __('Delete')],
			['confirm' => __('Are you sure you want to delete this person?')])
	];

	$class = ($person->duplicate ? 'warning-message' : '');
	$rows[] = [
		[$person->full_name, ['class' => $class]],
		[implode('', $links), ['class' => 'actions']]
	];
}
if (empty($rows)) {
	echo $this->Html->para(null, __('No accounts to approve.'));
} else {
	echo $this->Html->tag('table', $this->Html->tableCells($rows), ['class' => 'table table-striped table-hover table-condensed']);
}
?>

</div>
