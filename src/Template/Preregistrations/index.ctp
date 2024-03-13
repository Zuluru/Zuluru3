<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Preregistrations'));
$this->Html->addCrumb(__('List'));
if (isset($event)) {
	if (count($affiliates) > 1) {
		$this->Html->addCrumb($event->affiliate->name);
	}
	$this->Html->addCrumb($event->name);
}
?>

<div class="preregistrations index">
	<h2><?php
		echo __('Preregistrations');
		if (isset($event)) {
			echo ': ';
			if (count($affiliates) > 1) {
				echo "{$event->affiliate->name} ";
			}
			echo $event->name;
		}
	?></h2>
	<p><?= $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]) ?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('person_id') ?></th>
<?php
if (!isset($event)):
?>
					<th><?= $this->Paginator->sort('event_id') ?></th>
<?php
endif;
?>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$affiliate_id = null;
foreach ($preregistrations as $preregistration):
	if (!isset($event) && count($affiliates) > 1 && $preregistration->event->affiliate_id != $affiliate_id):
		$affiliate_id = $preregistration->event->affiliate_id;
?>
				<tr>
					<th colspan="<?= 2 + (!isset($event)) ?>">
						<h3 class="affiliate"><?= h($preregistration->event->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
	endif;
?>
				<tr>
					<td><?= $this->element('People/block', ['person' => $preregistration->person]) ?></td>
<?php
	if (!isset($event)):
?>
					<td><?= $this->Html->link($preregistration->event->name, ['controller' => 'Events', 'action' => 'view', 'event' => $preregistration->event->id]) ?></td>
<?php
	endif;
?>
					<td class="actions"><?php
					echo $this->Form->iconPostLink('delete_24.png',
						['action' => 'delete', 'preregistration' => $preregistration->id],
						['alt' => __('Delete'), 'title' => __('Delete')],
						['confirm' => __('Are you sure you want to delete this preregistration?')]);
					?></td>
				</tr>

<?php
endforeach;
?>
			</tbody>
		</table>
	</div>
	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>
</div>
<div class="actions columns">
<?php
if (isset($event)):
?>
	<?= $this->element('Events/actions', ['event' => $event, 'format' => 'list']) ?>
<?php
else:
?>
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Preregistration')]));
?>
	</ul>
<?php
endif;
?>
</div>
