<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Question $question
 * @var string[] $affiliates
 */

use App\Controller\AppController;

$this->Breadcrumbs->add(__('Question'));
$this->Breadcrumbs->add(__('Preview'));
?>

<div class="questions view">
	<h2><?= __('Question') ?></h2>
<?php
if (count($affiliates) > 1):
?>
	<dl class="row">
		<dt class="col-sm-3 text-end"><?= __('Affiliate') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->Html->link($question->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $question->affiliate->id]]) ?></dd>
	</dl>
<?php
endif;

// Key is required, but unimportant here
$key = 0;
?>
<?= $this->element('Questions/input', compact('key', 'question')) ?>
</div>

<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Html->iconLink('view_32.png',
		['action' => 'index'],
		['alt' => __('List'), 'title' => __('List Questions')]
	),
	$this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['question' => $question->id, 'return' => AppController::_return()]],
		['alt' => __('Edit'), 'title' => __('Edit Question')]
	),
	$this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['question' => $question->id, 'return' => AppController::_return()]],
		['alt' => __('Delete'), 'title' => __('Delete Question')],
		['confirm' => __('Are you sure you want to delete this question?')]
	),
]);
?>
</div>
<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('Related Questionnaires') ?></h4>
<?php
if (!empty($question->questionnaires)):
?>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th><?= __('Id') ?></th>
						<th><?= __('Name') ?></th>
						<th class="actions"><?= __('Actions') ?></th>
					</tr>
				</thead>
				<tbody>
<?php
	foreach ($question->questionnaires as $questionnaire):
?>
					<tr>
						<td><?= $questionnaire->id ?></td>
						<td><?= $questionnaire->name ?></td>
						<td class="actions"><?php
							echo $this->Html->iconLink('view_24.png',
								['controller' => 'Questionnaires', 'action' => 'view', '?' => ['questionnaire' => $questionnaire->id]],
								['alt' => __('Preview'), 'title' => __('Preview')]);
							echo $this->Html->iconLink('edit_24.png',
								['controller' => 'Questionnaires', 'action' => 'edit', '?' => ['questionnaire' => $questionnaire->id, 'return' => AppController::_return()]],
								['alt' => __('Edit'), 'title' => __('Edit')]);
						?></td>
					</tr>

<?php
	endforeach;
?>
				</tbody>
			</table>
		</div>
<?php
endif;
?>
	</div>

	<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Html->iconLink('add_32.png',
		['controller' => 'Questionnaires', 'action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Questionnaire')]
	)
]);
?>
	</div>
</div>
