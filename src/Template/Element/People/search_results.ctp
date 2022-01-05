<?php
use App\Controller\AppController;
?>
<div class="index">
<?php
if (isset($search_error)):
	echo $this->Html->para(null, $search_error);
elseif (isset($people)):
?>
	<p><?php
	$this->Paginator->options([
		'url' => $url,
	]);
	echo $this->Paginator->counter([
		'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	]);
	?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr class="paginator">
					<th><?= $this->Paginator->sort('first_name') ?></th>
<?php
	if ($this->Authorize->can('display_legal_names', \App\Controller\PeopleController::class)):
?>
					<th><?= $this->Paginator->sort('legal_name') ?></th>
<?php
	endif;
?>
					<th><?= $this->Paginator->sort('last_name') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($people as $person):
		$affiliates = collection($person->affiliates)->extract('id')->toArray();
		$mine = array_intersect($affiliates, $this->UserCache->read('ManagedAffiliateIDs'));
		$is_person_manager = !empty($mine);
?>
				<tr>
					<td><?= $this->element('People/block', ['person' => $person, 'display_field' => 'first_name']) ?></td>
<?php
	if ($this->Authorize->can('display_legal_names', \App\Controller\PeopleController::class)):
?>
					<td><?= $this->element('People/block', ['person' => $person, 'display_field' => 'legal_name']) ?></td>
<?php
	endif;
?>
					<td><?= $this->element('People/block', ['person' => $person, 'display_field' => 'last_name']) ?></td>
					<td class="actions"><?php
					echo $this->Html->iconLink('view_24.png', ['controller' => 'People', 'action' => 'view', 'person' => $person->id, 'return' => AppController::_return()], ['alt' => __('View Profile'), 'title' => __('View Profile')]);
					if ($this->Authorize->can('vcf', $person)) {
						echo $this->Html->link(__('VCF'), ['controller' => 'People', 'action' => 'vcf', 'person' => $person->id]);
					}
					if ($this->Authorize->can('note', $person)) {
						echo $this->Html->link(__('Add Note'), ['controller' => 'People', 'action' => 'note', 'person' => $person->id, 'return' => AppController::_return()]);
					}
					if ($this->Authorize->can('edit', $person)) {
						echo $this->Html->iconLink('edit_24.png', ['controller' => 'People', 'action' => 'edit', 'person' => $person->id, 'return' => AppController::_return()], ['alt' => __('Edit Profile'), 'title' => __('Edit Profile')]);
						if (!$person->user_id) {
							echo $this->Html->iconLink('add_24.png', ['controller' => 'People', 'action' => 'add_account', 'person' => $person->id, 'return' => AppController::_return()], ['alt' => __('Create Login'), 'title' => __('Create Login')]);
						}
						echo $this->Form->iconPostLink('delete_24.png', ['controller' => 'People', 'action' => 'delete', 'person' => $person->id], ['alt' => __('Delete Player'), 'title' => __('Delete Player')], ['confirm' => __('Are you sure you want to delete this person?')]);
					}
					if (!empty($extra_url)) {
						foreach ($extra_url as $title => $url_params) {
							if (empty($url_params['url_parameter'])) {
								$extra_url_parameter = 'person';
							} else {
								$extra_url_parameter = $url_params['url_parameter'];
								unset($url_params['url_parameter']);
							}
							if (empty($url_params['url_field'])) {
								$extra_url_field = 'id';
							} else {
								$extra_url_field = $url_params['url_field'];
								unset($url_params['url_field']);
							}
							if (empty($url_params['link_opts'])) {
								$link_opts = [];
							} else {
								$link_opts = $url_params['link_opts'];
								unset($url_params['link_opts']);
							}

							if (!empty($person[$extra_url_field])) {
								$url_params = array_merge([$extra_url_parameter => $person[$extra_url_field], 'return' => AppController::_return()], $url_params);
								echo $this->Html->link($title, $url_params, $link_opts);
							}
						}
					}
					?></td>
				</tr>
<?php
	endforeach;
?>

			</tbody>
		</table>
	</div>
</div>
<nav class="paginator"><ul class="pagination">
	<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
</ul></nav>

<?php
	if (in_array($this->getRequest()->action, ['rule_search', 'league_search', 'inactive_search'])):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('Download'), array_merge($url, ['_ext' => 'csv'])));
?>
	</ul>
<?php
	endif;
endif;
?>
</div>
