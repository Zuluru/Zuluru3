<?php
/**
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 * @var bool $competition
 * @var bool $multi_day
 * @var string $id_field
 * @var int $id
 * @var \Cake\I18n\FrozenDate[] $week
 */

use Cake\Core\Configure;
?>

<tr>
	<th colspan="<?= 3 + $multi_day ?>"><a name="<?= $week[0]->toDateString() ?>"><?= $this->Time->dateRange($week[0], $week[1]) ?></a></th>
	<th colspan="<?= 2 + !$competition ?>" class="actions splash-action">
<?php
if (!isset($division) && count($league->divisions) == 1) {
	$division = reset($league->divisions);
}
if (isset($division)) {
	$resource = $division;
} else {
	$resource = $league;
}

if (!$finalized) {
	if (isset($division) && $has_dependent_games && $this->Authorize->can('initialize_dependencies', $division)) {
		echo $this->Html->iconLink('initialize_24.png',
			['controller' => 'Divisions', 'action' => 'initialize_dependencies', '?' => ['division' => $division->id, 'date' => $week[0]->toDateString()]],
			['alt' => __('Initialize'), 'title' => __('Initialize Schedule Dependencies')]);
		echo $this->Html->iconLink('reset_24.png',
			['controller' => 'Divisions', 'action' => 'initialize_dependencies', '?' => ['division' => $division->id, 'date' => $week[0]->toDateString(), 'reset' => true]],
			['alt' => __('Reset'), 'title' => __('Reset Schedule Dependencies')]);
	}

	if ($this->Authorize->can('edit_schedule', $resource)) {
		echo $this->Html->iconLink('field_24.png',
			['action' => 'slots', '?' => [$id_field => $id, 'date' => $week[0]->toDateString()]],
			['alt' => __(Configure::read("sports.{$league->sport}.fields_cap")), 'title' => __('Available {0}', __(Configure::read("sports.{$league->sport}.fields_cap")))]);
		echo $this->Html->iconLink('edit_24.png',
			['action' => 'schedule', '?' => [$id_field => $id, 'edit_date' => $week[0]->toDateString(), '#' => $week[0]->toDateString()]],
			['alt' => __('Edit Week'), 'title' => __('Edit Week')]);
		echo $this->Form->iconPostLink('delete_24.png',
			['controller' => 'Schedules', 'action' => 'delete', '?' => [$id_field => $id, 'date' => $week[0]->toDateString()]],
			['alt' => __('Delete Week'), 'title' => __('Delete Week')]);

		if ($published) {
			echo $this->Html->iconLink('unpublish_24.png',
				['controller' => 'Schedules', 'action' => 'unpublish', '?' => [$id_field => $id, 'date' => $week[0]->toDateString()]],
				['alt' => __('Unpublish'), 'title' => __('Unpublish')]);
		} else {
			echo $this->Html->iconLink('publish_24.png',
				['controller' => 'Schedules', 'action' => 'publish', '?' => [$id_field => $id, 'date' => $week[0]->toDateString()]],
				['alt' => __('Publish'), 'title' => __('Publish')]);
		}
	}

	if (isset($division) && $this->Authorize->can('edit_schedule', $division)) {
		echo $this->Html->iconLink('reschedule_24.png',
			['controller' => 'Schedules', 'action' => 'reschedule', '?' => ['division' => $division->id, 'date' => $week[0]->toDateString()]],
			['alt' => __('Reschedule'), 'title' => __('Reschedule')]);
	}
} else {
	echo '&nbsp;';
}
?>
	</th>
</tr>
