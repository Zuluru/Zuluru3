<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Divisions'));
$this->Breadcrumbs->add(__('Division {0} Availability Report', __(Configure::read("sports.{$division->league->sport}.field_cap"))));
$this->Breadcrumbs->add($division->full_league_name);
?>

<div class="divisions slots">
<h2><?= __('Division {0} Availability Report', __(Configure::read("sports.{$division->league->sport}.field_cap"))) . ': ' . $division->full_league_name ?></h2>

<p><?= __('Select a date below on which to view all available game slots:') ?></p>
<?php
echo $this->Form->create(null, ['align' => 'horizontal']);
echo $this->Form->control('date', [
	'label' => false,
	'options' => array_combine(
		array_map(function ($date) { return $date->toDateString(); }, $dates),
		array_map([$this->Time, 'date'], $dates)
	),
]);
echo $this->Jquery->ajaxButton(__('View'), ['selector' => '#SlotResults']);
echo $this->Form->end();
?>

<div id="SlotResults">
<?php
if (!empty($slots)) {
	echo $this->element('Divisions/slots_results');
}
?>
</div>

</div>
