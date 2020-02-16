<?php
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

$this->start('kickstart');
$identity = $this->Authorize->getIdentity();
if ($id == $identity->getIdentifier()) {
	$act_as = null;
} else {
	$act_as = $id;
}

if ($this->Authorize->can('index', \App\Controller\AffiliatesController::class)):
	if (empty($affiliates)):
		echo $this->Html->para('warning-message', __('You have enabled the affiliate option, but have not yet created any affiliates. ') .
			$this->Html->link(__('Create one now!'), ['controller' => 'Affiliates', 'action' => 'add', 'return' => AppController::_return()]));
	elseif (!empty($unmanaged)):
?>
	<p class="warning-message"><?= __('The following affiliates do not yet have managers assigned to them:') ?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Affiliate') ?></th>
					<th><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		foreach ($unmanaged as $affiliate):
?>
				<tr>
					<td class="splash_item"><?= $affiliate->name ?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('edit_24.png',
							['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => $affiliate->id, 'return' => AppController::_return()],
							['alt' => __('Edit'), 'title' => __('Edit')]);
						echo $this->Html->iconLink('coordinator_add_24.png',
							['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate->id, 'return' => AppController::_return()],
							['alt' => __('Add Manager'), 'title' => __('Add Manager')]);
						echo $this->Form->iconPostLink('delete_24.png',
							['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => $affiliate->id, 'return' => AppController::_return()],
							['alt' => __('Delete'), 'title' => __('Delete')],
							['confirm' => __('Are you sure you want to delete this affiliate?')]);
					?></td>
				</tr>
<?php
		endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
	endif;
endif;

if ($identity->isManager()):
	$my_affiliates = $identity->managedAffiliateIds();
	if (!empty($my_affiliates)):
		$facilities = TableRegistry::get('Facilities')->find('open', ['affiliates' => $my_affiliates]);
		if ($facilities->count() == 0):
			echo $this->Html->para('warning-message', __('You have no open facilities.') . ' ' .
				$this->Html->link(__('Create one now!'), ['controller' => 'Facilities', 'action' => 'add', 'return' => AppController::_return()])
			);
		else:
			// Eliminate any open facilities that have fields, and check if there's anything left that we need to warn about
			$facilities = $facilities
				->contain(['Fields' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['Fields.is_open' => true]);
					},
				]])
				->filter(function ($facility) { return empty($facility->fields); })
				->toList();
			if (!empty($facilities)):
?>
	<p class="warning-message"><?= __('The following facilities are open but do not have any open {0}:', Configure::read('UI.fields')) ?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Facility') ?></th>
					<th><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
				foreach ($facilities as $facility):
?>
				<tr>
					<td class="splash_item"><?= $facility->name ?></td>
					<td class="actions"><?php
						echo $this->Html->iconLink('view_24.png',
							['controller' => 'Facilities', 'action' => 'view', 'facility' => $facility->id],
							['alt' => __('View'), 'title' => __('View Facility')]);
						echo $this->Html->iconLink('edit_24.png',
							['controller' => 'Facilities', 'action' => 'edit', 'facility' => $facility->id, 'return' => AppController::_return()],
							['alt' => __('Edit'), 'title' => __('Edit Facility')]);
						echo $this->Form->iconPostLink('delete_24.png',
							['controller' => 'Facilities', 'action' => 'delete', 'facility' => $facility->id, 'return' => AppController::_return()],
							['alt' => __('Delete'), 'title' => __('Delete Facility')],
							['confirm' => __('Are you sure you want to delete this facility?')]);
					?></td>
				</tr>
<?php
				endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
			endif;
		endif;

		$leagues = TableRegistry::get('Leagues')->find('open', ['affiliates' => $my_affiliates]);
		if ($leagues->count() == 0) {
			echo $this->Html->para('warning-message', __('You have no current or upcoming leagues. ') .
				$this->Html->link(__('Create one now!'), ['controller' => 'Leagues', 'action' => 'add', 'return' => AppController::_return()]));
		}

		if (Configure::read('feature.registration')) {
			if (TableRegistry::get('Events')->find('open', ['extended' => true])->count() == 0) {
				echo $this->Html->para('warning-message', __('You have no current or upcoming registration events. ') .
					$this->Html->link(__('Create one now!'), ['controller' => 'Events', 'action' => 'add', 'return' => AppController::_return()]));
			}
		}
	endif;
elseif ($empty && $identity->isPlayer()):
	// If the user has nothing going on, pull some more details to allow us to help them get started
	$waivers = TableRegistry::get('Waivers')->find('active', ['affiliates' => $applicable_affiliates])->toArray();
	$signed_waivers = $this->UserCache->read('Waivers', $act_as);
	$membership_events = TableRegistry::get('Events')->find('open', ['affiliates' => $applicable_affiliates])->find('membership')->count();
	$non_membership_events = TableRegistry::get('Events')->find('open', ['affiliates' => $applicable_affiliates])->find('notMembership')->count();
	$open_teams = TableRegistry::get('Teams')->find('openRoster', ['affiliates' => $applicable_affiliates])->count();
	$leagues = TableRegistry::get('Leagues')->find('open', ['affiliates' => $applicable_affiliates])->count();
?>
	<h3><?= __('You are not yet on any teams.') ?></h3>
<?php
	$actions = [];

	if (empty($signed_waivers) && count($waivers) == 1 && in_array($waivers[0]->expiry_type, ['elapsed_time', 'never'])) {
		$actions[] = $this->Html->link(__('Sign the waiver'), ['controller' => 'Waivers', 'action' => 'sign', 'waiver' => $waivers[0]->id, 'date' => FrozenDate::now()->toDateString(), 'act_as' => $act_as]);
	} else {
		$options = [];
		if ($membership_events) {
			$options[] = __('membership');
		}
		if ($non_membership_events) {
			$options[] = __('an event');
		}

		if (!empty($options)) {
			$actions[] = $this->Html->link(__('Register for') . ' ' . implode(' ' . __('or') . ' ', $options), ['controller' => 'Events', 'action' => 'wizard', 'act_as' => $act_as]);
		}
	}

	if ($open_teams) {
		$actions[] = $this->Html->link(__('Join an existing team'), ['controller' => 'Teams', 'action' => 'join', 'act_as' => $act_as]);
	}

	if ($leagues) {
		$actions[] = $this->Html->link(__('Check out the leagues we are currently offering'), ['controller' => 'Leagues']);
	}

	if (!empty($actions)) {
		echo $this->Html->tag('div', $this->Html->nestedList($actions, ['class' => 'nav nav-pills']), ['class' => 'actions columns']);
	}
endif;

$this->end();

// There may be nothing to output, in which case we don't even want the wrapper div
$kickstart = $this->fetch('kickstart');
if ($kickstart) {
	echo $this->Html->tag('div', $kickstart, ['div id' => 'kick_start']);
}
