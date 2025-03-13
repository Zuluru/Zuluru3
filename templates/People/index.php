<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person[] $people
 * @var string[] $affiliates
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add(__('List'));
if (isset($group)) {
	$this->Breadcrumbs->add(__(Inflector::pluralize($group)));
}
?>

<div class="people index">
	<h2><?php
		echo __('People');
		if (isset($group)) {
			echo ': ' . __(Inflector::pluralize($group));
		}

		$user_names = array_unique(collection($people)->extract('user_name')->toArray());
		$hide_user_name = (empty($user_names) || (count($user_names) == 1 && empty($user_names[0])));
		$emails = array_unique(collection($people)->extract('email')->toArray());
		$hide_email = (empty($emails) || (count($emails) == 1 && empty($emails[0])));
		$column = Configure::read('gender.column');
		$genders = array_unique(collection($people)->extract($column)->toArray());
		$hide_gender = (empty($genders) || (count($genders) == 1 && empty($genders[0])));
		$show_badges = Configure::read('feature.badges');
	?></h2>
	<p><?= $this->Paginator->counter(
		__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	) ?></p>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('first_name') ?></th>
				<th><?= $this->Paginator->sort('last_name') ?></th>
<?php
if (!$hide_user_name):
?>
				<th><?= __('Username') ?></th>
<?php
endif;

if (!$hide_email):
?>
				<th><?= __('Email') ?></th>
<?php
endif;

if (!$hide_gender):
?>
				<th><?= $this->Paginator->sort($column) ?></th>
<?php
endif;
?>
				<th><?= $this->Paginator->sort('status') ?></th>
<?php
if ($show_badges):
?>
				<th><?= __('Badges') ?></th>
<?php
endif;
?>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
$affiliate_id = null;
foreach ($people as $person):
	if (count($affiliates) > 1 && $person->_matchingData['Affiliates']->id != $affiliate_id):
		$affiliate_id = $person->_matchingData['Affiliates']->id;
?>
			<tr>
				<th colspan="<?= 4 + !$hide_user_name + !$hide_email + !$hide_gender + $show_badges ?>">
					<h3 class="affiliate"><?= h($person->_matchingData['Affiliates']->name) ?></h3>
				</th>
			</tr>
<?php
	endif;
?>
			<tr>
				<td><?= $this->element('People/block', ['person' => $person, 'display_field' => 'first_name']) ?></td>
				<td><?= $this->element('People/block', ['person' => $person, 'display_field' => 'last_name']) ?></td>
<?php
	if (!$hide_user_name):
?>
				<td><?= empty($person->user_name) ? '' : $person->user_name ?>&nbsp;</td>
<?php
endif;

if (!$hide_email):
?>
				<td><?= $person->email ?>&nbsp;</td>
<?php
endif;

if (!$hide_gender):
?>
				<td><?= $person->$column ?>&nbsp;</td>
<?php
endif;
?>
				<td><?= $person->status ?></td>
<?php
if ($show_badges):
?>
				<td><?php
	foreach ($person->badges as $badge) {
		if ($this->Authorize->can('view', $badge)) {
			echo $this->Html->iconLink("{$badge->icon}_32.png", ['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badge->id]],
				['alt' => $badge->name, 'title' => $badge->description]);
		}
	}
				?></td>
<?php
endif;
?>
				<td class="actions"><?php
				echo $this->Html->iconLink('view_24.png',
					['action' => 'view', '?' => ['person' => $person->id]],
					['alt' => __('View'), 'title' => __('View')]);
				echo $this->Html->iconLink('edit_24.png',
					['action' => 'edit', '?' => ['person' => $person->id]],
					['alt' => __('Edit'), 'title' => __('Edit')]);
				echo $this->Html->link(__('Act As'), ['action' => 'act_as', '?' => ['person' => $person->id]]);
				if (in_array(GROUP_OFFICIAL, $this->UserCache->read('UserGroupIDs', $person->id))) {
					echo $this->Html->iconLink('schedule_24.png',
						['controller' => 'People', 'action' => 'officiating_schedule', '?' => ['official' => $person->id]],
						['alt' => __('Officiating Schedule'), 'title' => __('Officiating Schedule')]);
				}
				echo $this->Form->iconPostLink('delete_24.png',
					['action' => 'delete', '?' => ['person' => $person->id]],
					['alt' => __('Delete'), 'title' => __('Delete')],
					['confirm' => __('Are you sure you want to delete this person?')]);
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
$params = $this->getRequest()->getQueryParams();
unset($params['page']);
echo $this->Bootstrap->navPills([
	$this->Html->link(__('Download'), ['?' => $params, '_ext' => 'csv'], ['class' => $this->Bootstrap->navPillLinkClasses()]),
]);
?>
</div>
