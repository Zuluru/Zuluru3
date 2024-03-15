<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Affiliate $affiliate
 */

$this->Breadcrumbs->add(__('Affiliates'));
$this->Breadcrumbs->add($affiliate->name);
$this->Breadcrumbs->add(__('Add Manager'));
?>

<div class="affiliates add_manager">
<h2><?= __('Add Manager') . ': ' . $affiliate->name ?></h2>

<?php
if (!empty($affiliate->people)) {
	echo $this->Html->tag('h3', __('Current Managers:'));
	$managers = [];
	foreach ($affiliate->people as $person) {
		$managers[] = $this->element('People/block', compact('person'));
	}
	echo $this->Html->nestedList($managers);
}
?>
<p class="highlight-message"><?= __('Note that only people whose accounts are set as "manager" (or higher) can be made managers.') ?></p>

<?= $this->element('People/search_form', ['affiliate_id' => $affiliate->id]) ?>

	<div id="SearchResults" class="zuluru_pagination">

<?= $this->element('People/search_results', ['extra_url' => [__('Add as manager') => ['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate->id]]]) ?>

	</div>
</div>
