<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 */

$this->Breadcrumbs->add(__('Badges'));
if ($badge->category == 'assigned') {
	$this->Breadcrumbs->add(__('Assign'));
} else {
	$this->Breadcrumbs->add(__('Nominate'));
}
if (count($affiliates) > 1) {
	$this->Breadcrumbs->add($badge->affiliate->name);
}
$this->Breadcrumbs->add($badge->name);
?>

<div class="badges form">
	<fieldset>
		<legend><?php
		if ($badge->category == 'assigned') {
			echo __('Assign a Badge');
		} else {
			echo __('Nominate for a Badge');
		}
		echo ': ';
		if (count($affiliates) > 1) {
			echo "{$badge->affiliate->name} ";
		}
		echo $badge->name;
		?></legend>
		<p><?= $this->Html->iconImg($badge->icon . '_64.png') . ' ' . $badge->description ?></p>

<?= $this->element('People/search_form', ['affiliate_id' => $badge->affiliate_id]) ?>

		<div id="SearchResults" class="zuluru_pagination">

<?php
$extra_url = ['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => $badge->id];
if ($badge->category == 'assigned') {
	$extra_url = [__('Assign badge') => $extra_url];
} else {
	$extra_url = [__('Nominate for badge') => $extra_url];
}
echo $this->element('People/search_results', compact('extra_url'));
?>

		</div>
	</fieldset>
</div>
