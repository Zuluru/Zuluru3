<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Badges'));
$this->Breadcrumbs->add(h($badge->name));
$this->Breadcrumbs->add(__('View'));
?>

<div class="badges view">
	<h2><?= $this->Html->iconImg($badge->icon . '_64.png') . ' ' . h($badge->name) ?></h2>
	<p><?= $this->Text->autoParagraph(h($badge->description)) ?></p>
<?php
if ($this->Authorize->can('edit', $badge)):
?>
	<dl class="row">
		<dt class="col-sm-3 text-end"><?= __('Category') ?></dt>
		<dd class="col-sm-9 mb-0"><?= Configure::read("options.category.{$badge->category}") ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Active') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $badge->active ? __('Yes') : __('No') ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Visibility') ?></dt>
		<dd class="col-sm-9 mb-0"><?= Configure::read("options.visibility.{$badge->visibility}") ?></dd>
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
		<p><?= $this->Paginator->counter(
			__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
		) ?></p>
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
											$reason . __(' ({0})', $this->Jquery->ajaxLink(__('Delete'), [
												'url' => ['controller' => 'People', 'action' => 'delete_badge', '?' => ['badge' => $record->id]],
												'dialog' => 'badge_comment_div',
												'disposition' => 'remove_closest',
												'selector' => 'span',
												'remove-separator' => ', ',
												'remove-separator-selector' => 'tr',
											]))
										);
									}
									$reasons[] = $reason;
								} else if (!empty($record->game_id)) {
									$reasons[] = $this->element('Divisions/block', ['division' => $record->game->division, 'field' => 'full_league_name']);
								} else if (!empty($record->team_id)) {
									$reasons[] = $this->element('Teams/block', ['team' => $record->team, 'show_shirt' => false]);
								} else if (!empty($record->registration_id)) {
									$reasons[] = $this->Html->link($record->registration->event->name, ['controller' => 'Events', 'action' => 'view', '?' => ['event' => $record->registration->event->id]]);
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
<?php
$links = [
	$this->Html->iconLink('view_32.png',
		['action' => 'index'],
		['alt' => __('List'), 'title' => __('List Badges')]
	),
];
if ($badge->category === 'nominated') {
	$links[] = $this->Html->link(
		__('Nominate'),
		['controller' => 'People', 'action' => 'nominate_badge', '?' => ['badge' => $badge->id]],
		['class' => $this->Bootstrap::navPillLinkClasses()]
	);
}
if ($this->Authorize->can('edit', $badge)) {
	if ($badge->category === 'assigned') {
		$links[] = $this->Html->link(
			__('Assign'),
			['controller' => 'People', 'action' => 'nominate_badge', '?' => ['badge' => $badge->id]],
			['class' => $this->Bootstrap::navPillLinkClasses()]
		);
	} else if (!in_array($badge->category, ['nominated', 'runtime', 'aggregate'])) {
		$links[] = $this->Html->iconLink('initialize_32.png',
			['action' => 'initialize_awards', '?' => ['badge' => $badge->id]],
			['alt' => __('Initialize'), 'title' => __('Initialize')],
			['confirm' => __('Are you sure you want to initialize? This should only ever need to be done once when the badge system is introduced.')]
		);
	}
	$links[] = $this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['badge' => $badge->id]],
		['alt' => __('Edit'), 'title' => __('Edit Badge')]
	);
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['badge' => $badge->id]],
		['alt' => __('Delete'), 'title' => __('Delete Badge')],
		['confirm' => __('Are you sure you want to delete this badge?')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Badge')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>

<?= $this->element('People/badge_div', [
	'message' => __('If you want to add a comment to the badge holder about why the badge is being removed, do so here.'),
]);
