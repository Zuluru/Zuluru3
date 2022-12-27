<?php
/**
 * @type $this \App\View\AppView
 * @type $events \App\Model\Entity\Event[]
 * @type $affiliates int[]
 */

echo $this->element('Events/selectors', compact('events'));
?>
<div class="table-responsive clear-float">
	<table class="table table-condensed">
		<thead>
			<tr>
				<th><?= __('Registration') ?></th>
				<th><?= __('Cost') ?></th>
				<th><?= __('Opens on') ?></th>
				<th><?= __('Closes on') ?></th>
				<th><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
echo $this->element('Events/list_type', ['event_type' => $events[0]->event_type, 'events' => $events]);
?>

		</tbody>
	</table>
</div>
