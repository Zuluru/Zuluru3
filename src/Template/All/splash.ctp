<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

$this->Html->addCrumb(__('Dashboard'));
$this->Html->addCrumb($this->UserCache->read('Person.full_name'));
?>

<div class="all splash">
<?php
echo $this->element('Layout/announcement');

if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
	if (isset($new_accounts)) {
		echo $this->Html->para(null, __('There are {0} new {1}.', $new_accounts,
			$this->Html->link(__('accounts to approve'), ['controller' => 'People', 'action' => 'list_new'])));
	}
	if (isset($new_photos)) {
		echo $this->Html->para(null, __('There are {0} new {1}.', $new_photos,
			$this->Html->link(__('profile photos to approve'), ['controller' => 'People', 'action' => 'approve_photos'])));
	}
	if (isset($new_documents)) {
		echo $this->Html->para(null, __('There are {0} new {1}.', $new_documents,
			$this->Html->link(__('uploaded documents to approve'), ['controller' => 'People', 'action' => 'approve_documents'])));
	}
	if (isset($new_nominations)) {
		echo $this->Html->para(null, __('There are {0} new {1}.', $new_nominations,
			$this->Html->link(__('badge nominations to approve'), ['controller' => 'People', 'action' => 'approve_badges'])));
	}
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
	$id = Configure::read('Perm.my_id');
	echo $this->Html->tag('div', $this->element('All/blank_splash', ['id' => $id, 'name' => __('My Teams')]), ['id' => "tab-$id"]);
	$this->Html->scriptBlock("
	jQuery.get('$url', function(result) {
		jQuery('#tab-$id').find('.schedule').replaceWith(result.content);
	});", ['buffer' => true]);
else:
?>
	<div id="tabs">
		<ul>
			<li><a href="<?= Router::url(['action' => 'schedule', 'person' => Configure::read('Perm.my_id')]) ?>"><?= $this->UserCache->read('Person.full_name') ?></a></li>
<?php
	$default_tab_index = 0;
	$default_tab_id = null;
	if ($this->request->session()->check('Zuluru.default_tab_id')) {
		$default_tab_id = $this->request->session()->read('Zuluru.default_tab_id');
		$this->request->session()->delete('Zuluru.default_tab_id');
	}

	foreach ($relatives as $i => $relative):
?>
			<li><a href="<?= Router::url(['action' => 'schedule', 'person' => $relative->id]) ?>"><?= $relative->full_name ?></a></li>
<?php
		if ($default_tab_id == $relative->id) {
			$default_tab_index = $i + 1;
		}
	endforeach;
?>
			<li><a href="<?= Router::url(['action' => 'consolidated_schedule']) ?>"><?= __('Consolidated Schedule') ?></a></li>
		</ul>
<?php
	echo $this->Html->tag('div', $this->element('All/blank_splash', ['id' => Configure::read('Perm.my_id'), 'name' => __('My Teams')]), ['id' => 'ui-tabs-1']);
	foreach ($relatives as $i => $relative) {
		echo $this->Html->tag('div', $this->element('All/blank_splash', ['id' => $relative->id, 'name' => __('{0}\'s Teams', $relative->first_name)]), ['id' => 'ui-tabs-' . ($i + 2)]);
	}
	echo $this->Html->tag('div', $this->element('All/blank_splash'), ['id' => 'ui-tabs-' . ($i + 3)]);
?>
	</div>
<?php
	$this->Html->scriptBlock("
	jQuery('#tabs').tabs({
		active: $default_tab_index,
		beforeLoad: function(event, ui) {
			if (ui.panel.attr('loaded')) {
				return false;
			}
			ui.panel.attr('loaded', true);
			// Technique from http://stackoverflow.com/questions/2785548/loading-json-encoded-ajax-content-into-jquery-ui-tabs
			var url = ui.ajaxSettings.url;
			jQuery.get(url, function(result) {
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
	if ($this->request->session()->check('Zuluru.CurrentAffiliate')) {
		echo $this->Html->para(null, __('You are currently browsing the {0} affiliate. You might want to {1} or {2}.',
			$affiliates[$this->request->session()->read('Zuluru.CurrentAffiliate')]['Affiliate']['name'],
			$this->Html->link(__('remove this restriction'), ['controller' => 'Affiliates', 'action' => 'view_all']),
			$this->Html->link(__('select a different affiliate to view'), ['controller' => 'Affiliates', 'action' => 'select'])));
	} else if (count($this->UserCache->read('AffiliateIDs')) != count($affiliates)) {
		if (Configure::read('Perm.is_admin')) {
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
