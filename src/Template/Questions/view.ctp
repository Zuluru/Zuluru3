<?php
use App\Controller\AppController;

$this->Html->addCrumb(__('Question'));
$this->Html->addCrumb(__('Preview'));
?>

<div class="questions view">
	<h2><?= __('Question') ?></h2>
<?php
if (count($affiliates) > 1):
?>
	<dl class="dl-horizontal">
		<dt><?= __('Affiliate') ?></dt>
		<dd><?= $this->Html->link($question->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $question->affiliate->id]) ?></dd>
	</dl>
<?php
endif;

// Key is required, but unimportant here
$key = 0;
?>
<?= $this->element('Questions/input', compact('key', 'question')) ?>
</div>

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('view_32.png',
	['action' => 'index'],
	['alt' => __('List'), 'title' => __('List Questions')]));
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', 'question' => $question->id, 'return' => AppController::_return()],
	['alt' => __('Edit'), 'title' => __('Edit Question')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'question' => $question->id, 'return' => AppController::_return()],
	['alt' => __('Delete'), 'title' => __('Delete Question')],
	['confirm' => __('Are you sure you want to delete this question?')]));
?>
	</ul>
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
								['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' => $questionnaire->id],
								['alt' => __('Preview'), 'title' => __('Preview')]);
							echo $this->Html->iconLink('edit_24.png',
								['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' => $questionnaire->id, 'return' => AppController::_return()],
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
		<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['controller' => 'Questionnaires', 'action' => 'add'],
	['alt' => __('New'), 'title' => __('New Questionnaire')]));
?>
		</ul>
	</div>
</div>
