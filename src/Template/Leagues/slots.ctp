<?php
use App\Model\Entity\Division;
use Cake\Core\Configure;

$tournaments = collection($league->divisions)->every(function (Division $division) {
	return $division->schedule_type == 'tournament';
});
$this->Html->addCrumb($tournaments ? __('Tournaments') : __('Leagues'));
$this->Html->addCrumb(__('League {0} Availability Report', __(Configure::read("sports.{$league->sport}.field_cap"))));
$this->Html->addCrumb($league->full_name);
?>

<div class="leagues slots">
	<h2><?= __('League {0} Availability Report', __(Configure::read("sports.{$league->sport}.field_cap"))) . ': ' . $league->full_name ?></h2>

	<p><?= __('Select a date below on which to view all available game slots:') ?></p>
<?php
echo $this->Form->create($league, ['align' => 'horizontal', 'id' => 'SlotForm']);
echo $this->Form->input('date', [
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
	echo $this->element('Leagues/slots_results');
}
?>
	</div>

</div>
