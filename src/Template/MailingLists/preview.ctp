<?php
use App\Controller\AppController;

$this->Html->addCrumb(__('Mailing Lists'));
$this->Html->addCrumb($mailing_list->name);
$this->Html->addCrumb(__('Preview'));
?>

<div class="mailingLists preview">
	<h2><?= $mailing_list->name ?></h2>
	<p><?= __('This mailing list currently matches the following people. Keep in mind that mailing lists are dynamic, so the list may change from day to day as people register, join teams, etc.') ?></p>
<?php
if (!empty($people)):
?>
	<p><?= $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]) ?></p>
	<p><?php
		$out = [];
		foreach ($people as $person) {
			$out[] = $this->element('People/block', compact('person'));
		}
		echo implode(', ', $out);
	?></p>

	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>
<?php
else:
?>
	<p class="error-message"><?= __('No matches found!') ?></p>
<?php
endif;
?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List Mailing Lists'), ['action' => 'index']));
echo $this->Html->tag('li', $this->Html->iconLink('view_32.png',
	['action' => 'View', 'mailing_list' => $mailing_list->id],
	['alt' => __('View'), 'title' => __('View')]));
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', 'mailing_list' => $mailing_list->id, 'return' => AppController::_return()],
	['alt' => __('Edit'), 'title' => __('Edit')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'mailing_list' => $mailing_list->id],
	['alt' => __('Delete'), 'title' => __('Delete Mailing List')],
	['confirm' => __('Are you sure you want to delete this mailingList?')]));
echo $this->Html->tag('li', $this->Html->iconLink('mailing_list_add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Mailing List')]));
?>
	</ul>
</div>
