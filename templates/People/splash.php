<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person[] $relatives
 * @var \App\Model\Entity\Affiliate[] $affiliates
 * @var \App\Model\Entity\Affiliate[] $unmanaged
 * @var int[] $applicable_affiliates
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

$this->Breadcrumbs->add(__('Dashboard'));
$this->Breadcrumbs->add($this->UserCache->read('Person.full_name'));
?>

<div class="all splash">
<?php
echo $this->element('layout/announcement');

if (isset($new_accounts) && $this->Authorize->can('list_new', \App\Controller\PeopleController::class)) {
	echo $this->Html->para(null, __('There are {0} new {1}.', $new_accounts,
		$this->Html->link(__('accounts to approve'), ['controller' => 'People', 'action' => 'list_new'])));
}
if (isset($new_photos) && $this->Authorize->can('approve_photos', \App\Controller\PeopleController::class)) {
	echo $this->Html->para(null, __('There are {0} new {1}.', $new_photos,
		$this->Html->link(__('profile photos to approve'), ['controller' => 'People', 'action' => 'approve_photos'])));
}
if (isset($new_documents) && $this->Authorize->can('approve_documents', \App\Controller\PeopleController::class)) {
	echo $this->Html->para(null, __('There are {0} new {1}.', $new_documents,
		$this->Html->link(__('uploaded documents to approve'), ['controller' => 'People', 'action' => 'approve_documents'])));
}
if (isset($new_nominations) && $this->Authorize->can('approve_badges', \App\Controller\PeopleController::class)) {
	echo $this->Html->para(null, __('There are {0} new {1}.', $new_nominations,
		$this->Html->link(__('badge nominations to approve'), ['controller' => 'People', 'action' => 'approve_badges'])));
}
if ($this->Authorize->getIdentity()->isManager()) {
	echo $this->Html->para(null, __('Check out {0}.',
		$this->Html->link(__('today\'s schedule'), ['controller' => 'Schedules', 'action' => 'day'])));
}

$unpaid = $this->UserCache->read('RegistrationsUnpaid');
$relative_unpaid = [];
foreach ($relatives as $relative) {
	$relative_unpaid[$relative->id] = $this->UserCache->read('RegistrationsUnpaid', $relative->id);
}

// TODO
$count = count($unpaid); // + array_sum(array_map('count', $relative_unpaid));
if ($count) {
	echo $this->Html->para(null, __('You currently have {0} unpaid {1}. {2} to complete these registrations.',
		$count,
		__n('registration', 'registrations', $count),
		$this->Html->link(__('Click here'), ['controller' => 'Registrations', 'action' => 'checkout'])
	));
}

if (empty($relatives)):
	echo $this->Html->tag('h2', $this->UserCache->read('Person.full_name'));
	$url = Router::url(['action' => 'schedule']);
	$id = $this->UserCache->currentId();
	echo $this->Html->tag('div', $this->element('All/blank_splash', ['id' => $id, 'name' => __('My Teams')]), ['id' => "tab-$id"]);
	$this->Html->scriptBlock("
	zjQuery.get('$url', function(result) {
		zjQuery('#tab-$id').find('.schedule').replaceWith(result.content);
	});", ['buffer' => true]);
else:
?>
	<div id="tabs">
		<ul>
			<li><a href="<?= Router::url(['action' => 'schedule', '?' => ['person' => $this->UserCache->currentId()]]) ?>"><?= $this->UserCache->read('Person.full_name') ?></a></li>
<?php
	$default_tab_index = 0;
	$default_tab_id = null;
	if ($this->getRequest()->getSession()->check('Zuluru.default_tab_id')) {
		$default_tab_id = $this->getRequest()->getSession()->read('Zuluru.default_tab_id');
		$this->getRequest()->getSession()->delete('Zuluru.default_tab_id');
	}

	foreach ($relatives as $i => $relative):
?>
			<li><a href="<?= Router::url(['action' => 'schedule', '?' => ['person' => $relative->id]]) ?>"><?= $relative->full_name ?></a></li>
<?php
		if ($default_tab_id == $relative->id) {
			$default_tab_index = $i + 1;
		}
	endforeach;
?>
			<li><a href="<?= Router::url(['action' => 'consolidated_schedule']) ?>"><?= __('Consolidated Schedule') ?></a></li>
		</ul>
<?php
	echo $this->Html->tag('div', $this->element('All/blank_splash', ['id' => $this->UserCache->currentId(), 'name' => __('My Teams'), 'person' => null]), ['id' => 'ui-tabs-1']);
	foreach ($relatives as $i => $relative) {
		echo $this->Html->tag('div', $this->element('All/blank_splash', ['id' => $relative->id, 'name' => __('{0}\'s Teams', $relative->first_name), 'person' => $relative]), ['id' => 'ui-tabs-' . ($i + 2)]);
	}
	echo $this->Html->tag('div', $this->element('All/blank_splash'), ['id' => 'ui-tabs-' . ($i + 3), 'person' => null]);
?>
	</div>
<?php
	$this->Html->scriptBlock("
	zjQuery('#tabs').tabs({
		active: $default_tab_index,
		beforeLoad: function(event, ui) {
			if (ui.panel.attr('loaded')) {
				return false;
			}
			ui.panel.attr('loaded', true);
			// Technique from https://stackoverflow.com/questions/2785548/loading-json-encoded-ajax-content-into-jquery-ui-tabs
			var url = ui.ajaxSettings.url;
			zjQuery.get(url, function(result) {
				ui.panel.find('.schedule').replaceWith(result.content);
			});
			return false;
		}
	});", ['buffer' => true]);
endif;

if (Configure::read('feature.affiliates') && count($affiliates) > 1):
?>
	<div id="affiliate_select">
<?php
	if ($this->getRequest()->getSession()->check('Zuluru.CurrentAffiliate')) {
		echo $this->Html->para(null, __('You are currently browsing the {0} affiliate. You might want to {1} or {2}.',
			$affiliates[$this->getRequest()->getSession()->read('Zuluru.CurrentAffiliate')]->name,
			$this->Html->link(__('remove this restriction'), ['controller' => 'Affiliates', 'action' => 'view_all']),
			$this->Html->link(__('select a different affiliate to view'), ['controller' => 'Affiliates', 'action' => 'select'])));
	} else if (count($this->UserCache->read('AffiliateIDs')) != count($affiliates)) {
		if ($this->Authorize->can('index', \App\Controller\AffiliatesController::class)) {
			echo $this->Html->para(null, __('This site has multiple affiliates. You might want to {0}.',
				$this->Html->link(__('select a specific affiliate to view'), ['controller' => 'Affiliates', 'action' => 'select'])));
		} else if (Configure::read('feature.multiple_affiliates')) {
			echo $this->Html->para(null, __('This site has affiliates that you are not a member of. You might want to {0} or {1}.',
				$this->Html->link(__('join other affiliates'), ['controller' => 'People', 'action' => 'edit']),
				$this->Html->link(__('select a specific affiliate to view'), ['controller' => 'Affiliates', 'action' => 'select'])));
		} else {
			echo $this->Html->para(null, __('This site has affiliates that you are not a member of. You might want to {0} or {1}.',
				$this->Html->link(__('change which affiliate you are a member of'), ['controller' => 'People', 'action' => 'edit']),
				$this->Html->link(__('select a specific affiliate to view'), ['controller' => 'Affiliates', 'action' => 'select'])));
		}
	} else {
		echo $this->Html->para(null, __('You are a member of all affiliates on this site. You might want to {0} or {1}.',
			$this->Html->link(__('reduce your affiliations'), ['controller' => 'People', 'action' => 'edit']),
			$this->Html->link(__('select a specific affiliate to view'), ['controller' => 'Affiliates', 'action' => 'select'])));
	}
?>
	</div>
<?php
endif;
?>

</div>
<?php
echo $this->element('Games/attendance_div');
echo $this->element('People/roster_div');
