<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

$this->Html->addCrumb(__('Divisions'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Add Coordinator'));
?>

<div class="divisions add_coordinator">
<h2><?= __('Add Coordinator') . ': ' . $division->full_league_name ?></h2>

<?php
if (!empty($division->people)) {
	echo $this->Html->tag('h3', __('Current Coordinators:'));
	$coordinators = [];
	foreach ($division->people as $person) {
		$coordinators[] = $this->element('People/block', compact('person'));
	}
	echo $this->Html->nestedList($coordinators);
}
?>
<p class="highlight-message"><?= __('Note that only people whose accounts are set as "volunteer" (or higher) can be made coordinators.') ?></p>

<?= $this->element('People/search_form', ['affiliate_id' => $division->league->affiliate_id]) ?>

	<div id="SearchResults" class="zuluru_pagination">

<?= $this->element('People/search_results', ['extra_url' => [__('Add as coordinator') => ['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id, 'return' => false]]]) ?>

	</div>
</div>
