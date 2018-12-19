<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Badges'));
$this->Html->addCrumb(h($badge->name));
$this->Html->addCrumb(__('View'));
?>

<div class="badges view">
	<h2><?= $this->Html->iconImg($badge->icon . '_64.png') . ' ' . h($badge->name) ?></h2>
	<p><?= $this->Text->autoParagraph(h($badge->description)) ?></p>
<?php
if ($this->Authorize->can('edit', $badge)):
?>
	<dl class="dl-horizontal">
		<dt><?= __('Category') ?></dt>
		<dd><?= Configure::read("options.category.{$badge->category}") ?></dd>
		<dt><?= __('Active') ?></dt>
		<dd><?= $badge->active ? __('Yes') : __('No') ?></dd>
		<dt><?= __('Visibility') ?></dt>
		<dd><?= Configure::read("options.visibility.{$badge->visibility}") ?></dd>
	</dl>
<?php
endif;
?>
</div>

<?php
if ($this->Identity->isLoggedIn() && !empty($badge->people)):
?>
<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('This badge has been awarded to:') ?></h4>
		<p><?= $this->Paginator->counter([
			'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
		]) ?></p>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th><?= __('Name') ?></th>
						<th><?= __('For') ?></th>
					</tr>
				</thead>
				<tbody>
<?php
	foreach ($badge->people as $person):
?>
					<tr>
						<td><?= $this->element('People/block', compact('person')) ?></td>
						<td class="reasons"><?php
							$reasons = [];
							foreach ($person->badges as $record) {
								$record = $record->_joinData;
								if (in_array($badge->category, ['nominated', 'assigned'])) {
									$reason = $record->reason;
									if ($this->Authorize->can('edit', $badge)) {
										$reason = $this->Html->tag('span',
											$reason . ' (' . $this->Jquery->ajaxLink(__('Delete'), [
												'url' => ['controller' => 'People', 'action' => 'delete_badge', 'badge' => $record->id],
												'dialog' => 'badge_comment_div',
												'disposition' => 'remove_closest',
												'selector' => 'span',
												'remove-separator' => ', ',
												'remove-separator-selector' => 'tr',
											]) . ')'
										);
									}
									$reasons[] = $reason;
								} else if (!empty($record->game_id)) {
									$reasons[] = $this->element('Divisions/block', ['division' => $record->game->division, 'field' => 'full_league_name']);
								} else if (!empty($record->team_id)) {
									$reasons[] = $this->element('Teams/block', ['team' => $record->team, 'show_shirt' => false]);
								} else if (!empty($record->registration_id)) {
									$reasons[] = $this->Html->link($record->registration->event->name, ['controller' => 'Events', 'action' => 'view', 'event' => $record->registration->event->id]);
								}
							}
							echo implode(', ', $reasons);
						?></td>
					</tr>

<?php
	endforeach;
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<nav class="paginator"><ul class="pagination">
	<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
</ul></nav>
<?php
endif;
?>

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
if ($badge->category == 'nominated') {
	echo $this->Html->tag('li', $this->Html->link(__('Nominate'), ['controller' => 'People', 'action' => 'nominate_badge', 'badge' => $badge->id]));
}
if ($this->Authorize->can('edit', $badge)) {
	if ($badge->category == 'assigned') {
		echo $this->Html->tag('li', $this->Html->link(__('Assign'), ['controller' => 'People', 'action' => 'nominate_badge', 'badge' => $badge->id]));
	} else if (!in_array($badge->category, ['nominated', 'runtime', 'aggregate'])) {
		echo $this->Html->tag('li', $this->Html->iconLink('initialize_32.png',
			['action' => 'initialize_awards', 'badge' => $badge->id],
			['alt' => __('Initialize'), 'title' => __('Initialize')],
			['confirm' => __('Are you sure you want to initialize? This should only ever need to be done once when the badge system is introduced.')]));
	}
	echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
		['action' => 'edit', 'badge' => $badge->id],
		['alt' => __('Edit'), 'title' => __('Edit Badge')]));
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'badge' => $badge->id],
		['alt' => __('Delete'), 'title' => __('Delete Badge')],
		['confirm' => __('Are you sure you want to delete this badge?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Badge')]));
}
?>
	</ul>
</div>

<?= $this->element('People/badge_div', [
	'message' => __('If you want to add a comment to the badge holder about why the badge is being removed, do so here.'),
]);
