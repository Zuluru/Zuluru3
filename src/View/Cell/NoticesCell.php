<?php
namespace App\View\Cell;

use App\Core\UserCache;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\Routing\Router;
use Cake\View\Cell;

/**
 * Notices Cell
 *
 * @property \App\Model\Table\NoticesTable $Notices
 * @property \App\Model\Table\NoticesPeopleTable $NoticesPeople
 */
class NoticesCell extends Cell {

	public function next() {
		$identity = Router::getRequest()->getAttribute('identity');

		// Guests get no notices
		if (!$identity || mt_rand(0, 100) > Configure::read('notice_frequency')) {
			$this->set(['notice' => null]);
			return;
		}

		$this->loadModel('Notices');
		$this->loadModel('NoticesPeople');

		// Delete any old reminder requests
		$this->NoticesPeople->deleteAll([
			'remind' => true,
			'created <' => FrozenDate::now()->subMonth(),
		]);

		// Delete any annual recurring notices that are too old
		$annual = $this->Notices->find()
			->where(['Notices.repeat_on' => 'annual'])
			->combine('id', 'id')
			->toArray();
		$this->NoticesPeople->deleteAll([
			'created <' => FrozenDate::now()->subYear(),
			'notice_id IN' => $annual,
		]);

		// Find the list of all notices the user has seen
		$notices = $this->NoticesPeople->find()
			->where(['person_id' => $identity->getIdentifier()])
			->combine('notice_id', 'created')
			->toArray();

		// Check if this user has seen a notice recently; we don't want to overwhelm them
		if (!empty($notices)) {
			// Was the most recent response in the past 7 days?
			if (max($notices)->wasWithinLast('7 days')) {
				$this->set(['notice' => null]);
				return;
			}
		}

		// Figure out which notices to include based on this user's current details
		$display_to = ['all'];

		if ($identity->isAdmin()) {
			$display_to[] = 'admin';
		}
		if ($identity->isManager()) {
			$display_to[] = 'manager';
		}
		if ($identity->isOfficial()) {
			$display_to[] = 'official';
		}
		if ($identity->isVolunteer()) {
			$display_to[] = 'volunteer';
		}
		if ($identity->isCoach()) {
			$display_to[] = 'coach';
		}
		if ($identity->isPlayer()) {
			$display_to[] = 'player';
		}
		if ($identity->isParent()) {
			$display_to[] = 'parent';
		}
		if ($identity->isChild()) {
			$display_to[] = 'child';
		}

		if (!empty(UserCache::getInstance()->read('OwnedTeamIDs'))) {
			$display_to[] = 'captain';
		}
		if (!empty(UserCache::getInstance()->read('DivisionIDs'))) {
			$display_to[] = 'coordinator';
		}

		// Find a notice that the user hasn't seen, if any
		$query = $this->Notices->find()
			->where([
				'active' => true,
				'effective_date <= NOW()',
				'display_to IN' => $display_to,
			]);

		if (!empty($notices)) {
			$query->andWhere(['NOT' => ['id IN' => array_keys($notices)]]);
		}

		// Don't show the notices that repeat annually to anyone created in the past year
		$created = UserCache::getInstance()->read('Person.created');
		if ($created && $created->diffInDays(FrozenDate::now(), false) < 365) {
			$query->andWhere(['OR' => [
				'repeat_on IS' => null,
				'NOT' => ['repeat_on' => 'annual']
			]]);
		}

		$notice = $query
			->order('sort')
			->first();

		$this->set(compact('notice'));
	}

}
