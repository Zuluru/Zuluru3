<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[] $events
 */

if (!empty($events)):
?>
<div class="related">
	<h3><?= __('Register to play in this division:') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Registration') ?></th>
					<th><?= __('Type') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($events as $related):
?>
				<tr>
					<td><?= $this->Html->link($related->name, ['controller' => 'Events', 'action' => 'view', 'event' => $related->id]) ?></td>
					<td><?= __($related->event_type->name) ?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
	</div>
</div>
<?php
endif;
