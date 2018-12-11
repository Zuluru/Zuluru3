<?php
use Cake\Core\Configure;
?>

<tr>
	<th colspan="<?= 3 + $multi_day ?>"><a name="<?= $week[0]->toDateString() ?>"><?= $this->Time->dateRange($week[0], $week[1]) ?></a></th>
	<th colspan="<?= 2 + !$competition ?>" class="actions splash-action">
<?php
if (!isset($division) && count($league->divisions) == 1) {
	$division = reset($league->divisions);
}

if (!$finalized && (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator)):
	if (isset($division) && $has_dependent_games) {
		echo $this->Html->iconLink('initialize_24.png',
			['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $division->id, 'date' => $week[0]->toDateString()],
			['alt' => __('Initialize'), 'title' => __('Initialize schedule dependencies')]);
		echo $this->Html->iconLink('reset_24.png',
			['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $division->id, 'date' => $week[0]->toDateString(), 'reset' => true],
			['alt' => __('Reset'), 'title' => __('Reset schedule dependencies')]);
	}
?>
	<?= $this->Html->iconLink('field_24.png',
		['action' => 'slots', $id_field => $id, 'date' => $week[0]->toDateString()],
		['alt' => __(Configure::read("sports.{$league->sport}.fields_cap")), 'title' => __('Available {0}', __(Configure::read("sports.{$league->sport}.fields_cap")))]) ?>
	<?= $this->Html->iconLink('edit_24.png',
		['action' => 'schedule', $id_field => $id, 'edit_date' => $week[0]->toDateString(), '#' => $week[0]->toDateString()],
		['alt' => __('Edit Week'), 'title' => __('Edit Week')]) ?>
	<?= $this->Form->iconPostLink('delete_24.png',
		['controller' => 'Schedules', 'action' => 'delete', $id_field => $id, 'date' => $week[0]->toDateString()],
		['alt' => __('Delete Week'), 'title' => __('Delete Week')]) ?>
<?php
	if (isset($division)) {
		echo $this->Html->iconLink('reschedule_24.png',
			['controller' => 'Schedules', 'action' => 'reschedule', 'division' => $division->id, 'date' => $week[0]->toDateString()],
			['alt' => __('Reschedule'), 'title' => __('Reschedule')]);
	}

	if ($published) {
		echo $this->Html->iconLink('unpublish_24.png',
			['controller' => 'Schedules', 'action' => 'unpublish', $id_field => $id, 'date' => $week[0]->toDateString()],
			['alt' => __('Unpublish'), 'title' => __('Unpublish')]);
	} else {
		echo $this->Html->iconLink('publish_24.png',
			['controller' => 'Schedules', 'action' => 'publish', $id_field => $id, 'date' => $week[0]->toDateString()],
			['alt' => __('Publish'), 'title' => __('Publish')]);
	}

else:
	echo '&nbsp;';
endif;
?>
	</th>
</tr>
