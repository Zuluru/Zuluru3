<?php
/**
 * @type $this \App\View\AppView
 * @type $events \App\Model\Entity\Event[]
 * @type $category \App\Model\Entity\Category
 * @type $affiliates int[]
 */

$this->Html->addCrumb(__('Registration Events'));
$this->Html->addCrumb(__('List'));
?>

<div class="events index">
<?php
if (isset($category)):
?>
	<h2><?= $category->image_url ? $this->Html->image($category->image_url) : '' ?><?= $category->name ?></h2>
<?php
else:
?>
	<h2><?= __('Registration Events List') ?></h2>
<?php
endif;

if ($this->Identity->isLoggedIn()) {
	echo $this->element('Registrations/relative_notice');
}

echo $this->element('Registrations/notice');
if (!$this->Identity->isLoggedIn()) {
	echo $this->element('Events/not_logged_in');
}

echo $this->element('Events/selectors', compact('events'));

$events = collection($events)->groupBy('affiliate_id')->toArray();
foreach ($events as $affiliate_id => $affiliate_events):
	if (count($affiliates) > 1):
?>
	<h3 class="affiliate"><?= h($affiliate_events[0]->affiliate->name) ?></h3>
<?php
	endif;

	echo $this->element('Events/list', ['events' => $affiliate_events, 'category' => $category ?? null]);
endforeach;
?>
</div>
<?php
echo $this->element('People/confirmation', ['fields' => ['height', 'shirt_size', 'year_started', 'skill_level']]);
echo $this->element('Events/category_scaffolding');
