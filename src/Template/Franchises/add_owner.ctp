<?php
$this->Html->addCrumb(__('Franchise'));
$this->Html->addCrumb($franchise->name);
$this->Html->addCrumb(__('Add an Owner'));
?>

<div class="franchises add_owner">
	<h2><?= __('Add Owner') . ': ' . $franchise->name ?></h2>

	<?= $this->element('People/search_form', ['affiliate_id' => $franchise->affiliate_id]) ?>

	<div id="SearchResults" class="zuluru_pagination">

		<?= $this->element('People/search_results', ['extra_url' => [__('Make owner') => ['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => $franchise->id]]]) ?>

	</div>
</div>
