<?php
use Cake\Core\Configure;

if (isset($id)) {
	if ($this->UserCache->read('Person.status', $id) == 'inactive') {
		$reactivate_link = $this->Html->link(__('click here'), ['controller' => 'People', 'action' => 'reactivate']);
	} else if ($this->UserCache->read('Person.status', $id) == 'locked') {
		$reactivate_link = $this->Html->link(__('contact {0}', Configure::read('email.admin_name')), 'mailto:' . Configure::read('email.admin_email'));
	}
	if (isset($reactivate_link)) {
		echo $this->Html->para('warning-message', __('Your profile is currently {0}, so you can continue to use the site, but may be limited in some areas. To reactivate, {1}.',
			__($this->UserCache->read('Person.status', $id)), $reactivate_link
		));
	} else {
		$teams = $this->UserCache->read('Teams', $id);

		if ($id == $this->Identity->getId()) {
			$divisions = $this->UserCache->read('Divisions', $id);
		} else {
			// The current user has this notice above the tab structure
			$unpaid = $this->UserCache->read('RegistrationsUnpaid', $id);
			$count = count($unpaid);
			if ($count) {
				echo $this->Html->para(null, __('You currently have {0} unpaid {1}. {2} to complete these registrations.',
					$count,
					__n('registration', 'registrations', $count),
					$this->Html->link(__('Click here'), ['controller' => 'Registrations', 'action' => 'checkout', 'act_as' => $id])
				));
			}
		}

		if (Configure::read('feature.tasks')) {
			$tasks = $this->UserCache->read('Tasks', $id);
		}

		echo $this->element('All/kickstart', [
			'id' => $id,
			'affiliates' => $affiliates,
			'empty' => (empty($teams) && empty($divisions) && empty($tasks)),
			'person' => $person ?? null,
		]);
		if (!empty($divisions)) {
			echo $this->element('Divisions/splash', ['divisions' => $divisions]);
		}

		if ($id) {
			$past_teams = count($this->UserCache->read('AllTeamIDs', $id)) - count($this->UserCache->read('TeamIDs', $id));
		} else {
			$past_teams = 0;
		}
		echo $this->element('Teams/splash', compact('id', 'name', 'teams', 'past_teams'));

		// Build a dummy list of items to be displayed as blank lines
		if (!empty($teams)) {
			$limit = max(4, ceil(count($teams) * 1.5));
			$items = array_fill(0, $limit, null);
			echo $this->element('Games/splash', compact('items'));
		}

		echo $this->element('People/ical_links', compact('id'));
	}
} else {
	echo $this->Html->tag('span', __('One moment...'), ['class' => 'schedule']);
}
