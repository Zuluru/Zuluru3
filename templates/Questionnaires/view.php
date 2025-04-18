<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Questionnaire $questionnaire
 * @var string[] $affiliates
 */

use App\Controller\AppController;

$this->Breadcrumbs->add(__('Questionnaire'));
$this->Breadcrumbs->add(h($questionnaire->name));
$this->Breadcrumbs->add(__('View'));
?>

<div class="questionnaires view">
	<h2><?= h($questionnaire->name) . ' ' . __('Questionnaire') ?></h2>
	<dl class="row">
<?php
if (count($affiliates) > 1):
?>
		<dt class="col-sm-3 text-end"><?= __('Affiliate') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Html->link($questionnaire->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $questionnaire->affiliate->id]]) ?></dd>
<?php
endif;
?>
		<dt class="col-sm-3 text-end"><?= __('Active') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $questionnaire->active ? __('Yes') : __('No') ?></dd>
	</dl>
<?= $this->Form->create(null) ?>
	<fieldset>
		<legend><?= __('Questionnaire Preview') ?></legend>
<?= $this->element('Questionnaires/input', ['questionnaire' => $questionnaire, 'responses' => []]) ?>
	</fieldset>
<?= $this->Form->end() ?>
</div>

<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Html->iconLink('view_32.png',
		['action' => 'index'],
		['alt' => __('List'), 'title' => __('List Questionnaires')]
	),
	$this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['questionnaire' => $questionnaire->id]],
		['alt' => __('Edit'), 'title' => __('Edit Questionnaire')]
	),
	$this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['questionnaire' => $questionnaire->id]],
		['alt' => __('Delete'), 'title' => __('Delete Questionnaire')],
		['confirm' => __('Are you sure you want to delete this questionnaire?')]
	),
	$this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Questionnaire')]
	),
]);
?>
</div>
<?php
if (!empty($questionnaire->events)):
?>

<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('Related Events') ?></h4>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th><?= __('Name') ?></th>
						<th><?= __('Open') ?></th>
						<th><?= __('Close') ?></th>
						<th class="actions"><?= __('Actions') ?></th>
					</tr>
				</thead>
				<tbody>
<?php
	foreach ($questionnaire->events as $event):
?>
					<tr>
						<td><?= $this->Html->link($event->name, ['controller' => 'Events', 'action' => 'view', '?' => ['event' => $event->id]]) ?></td>
						<td><?= $this->Time->fulldatetime($event->open) ?></td>
						<td><?= $this->Time->fulldatetime($event->close) ?></td>
						<td class="actions"><?php
							echo $this->Html->iconLink('view_24.png',
								['controller' => 'Events', 'action' => 'view', '?' => ['event' => $event->id]],
								['alt' => __('View'), 'title' => __('View')]);
							echo $this->Html->iconLink('edit_24.png',
								['controller' => 'Events', 'action' => 'edit', '?' => ['event' => $event->id, 'return' => AppController::_return()]],
								['alt' => __('Edit'), 'title' => __('Edit')]);
							echo $this->Form->iconPostLink('delete_24.png',
								['controller' => 'Events', 'action' => 'delete', '?' => ['event' => $event->id, 'return' => AppController::_return()]],
								['alt' => __('Delete'), 'title' => __('Delete')],
								['confirm' => __('Are you sure you want to delete this event?')]);
						?></td>
					</tr>

<?php
	endforeach;
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php
endif;
