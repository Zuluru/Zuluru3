<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Franchise $franchise
 */

$this->Breadcrumbs->add(__('Franchise'));
$this->Breadcrumbs->add($franchise->name);
$this->Breadcrumbs->add(__('Add an Owner'));
?>

<div class="franchises add_owner">
	<h2><?= __('Add an Owner') . ': ' . $franchise->name ?></h2>

	<?= $this->element('People/search_form', ['affiliate_id' => $franchise->affiliate_id]) ?>

	<div id="SearchResults" class="zuluru_pagination">

		<?= $this->element('People/search_results', ['extra_url' => [__('Make owner') => ['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => $franchise->id]]]) ?>

	</div>
</div>
