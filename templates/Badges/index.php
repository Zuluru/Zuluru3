<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge[] $badges
 * @var string[] $affiliates
 * @var bool $active
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Badges'));
$this->Breadcrumbs->add(__('List'));
?>

<div class="badges index">
	<h2><?= $active ? __('Badges') : __('Deactivated Badges') ?></h2>
	<p><?= $this->Paginator->counter(
		__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	) ?></p>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('name') ?></th>
				<th><?= $this->Paginator->sort('category') ?></th>
				<th><?= $this->Paginator->sort('visibility') ?></th>
				<th><?= __('Icon')  ?></th>
<?php
if ($active):
?>
				<th><?= __('Awarded') ?></th>
<?php
endif;
?>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
$affiliate_id = null;
foreach ($badges as $badge):
	if (count($affiliates) > 1 && $badge->affiliate_id != $affiliate_id):
		$affiliate_id = $badge->affiliate_id;
?>
			<tr>
				<th colspan="<?= 5 + $active ?>">
					<h3 class="affiliate"><?= h($badge->_matchingData['Affiliates']->name) ?></h3>
				</th>
			</tr>
<?php
	endif;
?>
			<tr>
				<td><?= h($badge->name) ?></td>
				<td><?= __(Configure::read("options.category.{$badge->category}")) ?></td>
				<td><?= __(Configure::read("options.visibility.{$badge->visibility}")) ?></td>
				<td><?= $this->Html->iconImg($badge->icon . '_64.png') ?></td>
<?php
if ($active):
?>
				<td><?php
				if (in_array($badge->category, ['runtime', 'aggregate'])) {
					echo $this->Html->tag('abbr', __('N/A'),
							['title' => __('This badge is determined on-the-fly so there is no count of how many people have it')]);
				} else {
					echo $badge->count;
				}
				?></td>
<?php
endif;
?>
				<td class="actions"><?php
				echo $this->Html->iconLink('view_24.png',
					['action' => 'view', '?' => ['badge' => $badge->id]],
					['alt' => __('View'), 'title' => __('View')]);
				if ($this->Authorize->can('edit', $badge)) {
					echo $this->Html->iconLink('edit_24.png',
						['action' => 'edit', '?' => ['badge' => $badge->id]],
						['alt' => __('Edit'), 'title' => __('Edit')]);
				}
				if ($this->Authorize->can('delete', $badge)) {
					echo $this->Form->iconPostLink('delete_24.png',
						['action' => 'delete', '?' => ['badge' => $badge->id]],
						['alt' => __('Delete'), 'title' => __('Delete')],
						['confirm' => __('Are you sure you want to delete this badge?')]);
				}

				if ($this->Authorize->can('edit', $badge)) {
					if ($badge->active) {
						echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', '?' => ['badge' => $badge->id]]]);
					} else {
						echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', '?' => ['badge' => $badge->id]]]);
					}
				}

				if ($this->Authorize->can('nominate_badge', $badge)) {
					if ($badge->category == 'assigned') {
						$action = __('Assign');
					} else if ($badge->category == 'nominated') {
						$action = __('Nominate');
					}
					if (isset($action)) {
						echo $this->Html->link($action, ['controller' => 'People', 'action' => 'nominate_badge', '?' => ['badge' => $badge->id]]);
					}
				}

				if (!in_array($badge->category, ['assigned', 'nominated', 'runtime', 'aggregate']) && $this->Authorize->can('edit', $badge)) {
					echo $this->Html->iconLink('initialize_24.png',
						['action' => 'initialize_awards', '?' => ['badge' => $badge->id]],
						['alt' => __('Initialize'), 'title' => __('Initialize')],
						['confirm' => __('Are you sure you want to initialize? This should only ever need to be done once when the badge system is introduced.')]);
				}
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
<?php
if ($this->Authorize->can('add', \App\Controller\BadgesController::class)):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Badge')]));
?>
	</ul>
</div>
<?php
endif;
