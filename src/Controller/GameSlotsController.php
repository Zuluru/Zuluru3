<?php
namespace App\Controller;

use App\Model\Entity\GameSlot;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Database\Query;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * GameSlots Controller
 *
 * @property \App\Model\Table\GameSlotsTable $GameSlots
 */
class GameSlotsController extends AppController {

	// TODO: Eliminate this if we can find a way around black-holing caused by Ajax field adds
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);
		if (isset($this->FormProtection)) {
			$this->FormProtection->setConfig('unlockedActions', ['add']);
		}
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('slot');
		try {
			$game_slot = $this->GameSlots->get($id, [
				'contain' => [
					'Games' => [
						'HomeTeam',
						'HomePoolTeam' => ['DependencyPool'],
						'AwayTeam',
						'AwayPoolTeam' => ['DependencyPool'],
						'Divisions' => ['Leagues'],
					],
					'Fields' => ['Facilities' => ['Regions']],
					'Divisions' => ['Leagues'],
				],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($game_slot);
		$this->Configuration->loadAffiliate($game_slot->field->facility->region->affiliate_id);

		$this->set(compact('game_slot'));
	}

	/**
	 * Add method
	 * @todo: If the initial data fails validation, and there were multiple days selected, only one of the days stays
	 * selected when the form re-renders.
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$field = $this->getRequest()->getQuery('field');
		if (Configure::read('feature.affiliates')) {
			$affiliate = $this->getRequest()->getQuery('affiliate');
			if (!$affiliate && !$field) {
				$this->Flash->info(__('Invalid affiliate.'));
				return $this->redirect('/');
			}
		} else {
			$affiliate = 1;
		}

		// The entity should allow the extra fields that are used for bulk creation
		$game_slot = $this->GameSlots->newEmptyEntity();
		$game_slot->setAccess(['sport', 'length', 'buffer', 'weeks', 'fields', 'facilities', 'game_slots'], true);

		if ($field) {
			try {
				$field = $this->GameSlots->Fields->get($field, [
					'contain' => ['Facilities' => ['Regions']],
				]);
			} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid {0}.', Configure::read('UI.field')));
				return $this->redirect('/');
			}

			$this->Authorization->authorize($field, 'add_game_slots');
			$affiliate = $field->facility->region->affiliate_id;
			$this->set(compact('field'));
		} else {
			$regions = $this->GameSlots->Fields->Facilities->Regions->find()
				->contain([
					'Facilities' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Facilities.is_open' => true])->order('Facilities.name');
						},
						'Fields' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['Fields.is_open' => true])->order('Fields.num');
							},
						],
					],
				])
				->where(['Regions.affiliate_id' => $affiliate])
				->order('Regions.id')
				->toArray();

			if (empty($regions)) {
				if ($affiliate != 1) {
					$this->Flash->info(__('This affiliate has no regions set up.'));
				} else {
					$this->Flash->info(__('You have no regions set up.'));
				}
				return $this->redirect('/');
			}

			$this->Authorization->authorize(current($regions), 'add_game_slots');
			$this->set(compact('regions'));
		}
		$this->Configuration->loadAffiliate($affiliate);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if ($field && $this->getRequest()->getData('length') == 0 && $this->getRequest()->getData('weeks') == 1) {
				// Deal with a single game slot being added
				$game_slot = $this->GameSlots->newEntity(array_merge($this->getRequest()->getData(), [
					'field_id' => $field->id,
				]), ['associated' => 'Divisions', 'divisions' => true]);

				// Try to save
				if ($this->GameSlots->save($game_slot, ['single' => true])) {
					$this->Flash->success(__('The game slot has been saved.'));
					$this->GameSlots->Divisions->clearLocationsCache($game_slot->divisions);

					// We intentionally don't redirect here, leaving the user back on the
					// original "add" form, with the last game date/start/end/weeks options
					// already selected.
				} else {
					$this->Flash->warning(__('The game slot could not be saved. Please correct the errors below and try again.'));
				}
			} else {
				// Find the list of holidays to avoid
				$this->Holidays = $this->fetchTable('Holidays');
				$holidays = $this->Holidays->find()
					->where(['affiliate_id' => $affiliate])
					->all()
					->combine('date_string', 'name')
					->toArray();

				$game_slot = $this->GameSlots->patchEntity($game_slot, $this->getRequest()->getData(), [
					'validate' => 'bulk',
					'associated' => ['Divisions'],
					'accessibleFields' => ['length' => true, 'buffer' => true],
					'divisions' => true,
				]);
				// We have to do complete rule checking here, not just validation, so that we can
				// report any errors before proceeding to the confirmation page.
				$this->GameSlots->checkRules($game_slot);

				// Build the list of times to re-use
				$times = [];
				// Create games from start time through end time
				$start = $game_slot->start_time;
				if ($game_slot->length > 0) {
					$end = $game_slot->end_time;
					while ($start->addMinutes($game_slot->length) <= $end) {
						$times[] = $start;
						$start = $start->addMinutes($game_slot->length);
					}
				} else {
					$times[] = $start;
				}

				// Build the list of dates to re-use
				$weeks = $skipped = [];
				$date = $game_slot->game_date;
				while (count($weeks) < $game_slot->weeks) {
					$key = $date->toDateString();
					if (!array_key_exists($key, $holidays)) {
						$weeks[] = $date;
					} else {
						$skipped[$key] = $holidays[$key];
					}
					$date = $date->addWeeks(1);
				}

				$this->set(compact('times', 'weeks', 'skipped'));

				if ($game_slot->getErrors()) {
					$this->Flash->warning(__('The game slots could not be saved. Please correct any issues below and try again.'));

					// Some validation errors may need to be displayed as flash messages
					$errors = $game_slot->getError('fields');
					if (!empty($errors)) {
						$this->Flash->info(current($errors));
					}
				} else if (array_key_exists('confirm', $this->getRequest()->getData())) {
					if (empty($this->getRequest()->getData('game_slots'))) {
						$this->Flash->info(__('You must select at least one game slot!'));
						$this->viewBuilder()->setTemplate('confirm');
					} else {
						if ($this->GameSlots->getConnection()->transactional(function () use ($game_slot, $holidays, $times, $weeks) {
							$division_ids = collection($game_slot->divisions)->extract('id')->toArray();

							foreach ($this->getRequest()->getData('game_slots') as $field_id => $field_dates) {
								foreach ($field_dates as $date => $field_times) {
									$week = $weeks[$date];
									foreach (array_keys($field_times) as $time) {
										$game_start = $times[$time];
										if ($game_slot->length > 0) {
											$game_end = $game_start->addMinutes($game_slot->length - $game_slot->buffer);
										} else if (empty($game_slot->game_end)) {
											$game_end = null;
										} else {
											$game_end = $game_slot->game_end;
										}

										$slot = $this->GameSlots->newEntity(array_merge($this->getRequest()->getData(), [
											'field_id' => $field_id,
											'game_date' => $week,
											'game_start' => $game_start,
											'game_end' => $game_end,
											'divisions' => ['_ids' => $division_ids],
										]), ['associated' => 'Divisions']);

										// Try to save
										if (!$this->GameSlots->save($slot)) {
											$this->Flash->warning(__('The game slots could not be saved. Please correct any issues below and try again.'));
											$this->Flash->info(implode(' ', \Cake\Utility\Hash::flatten($slot->getErrors())));
											return false;
										}
									}
								}
							}

							return true;
						})
						) {
							$this->Flash->success(__('The game slots have been saved.'));
							$this->GameSlots->Divisions->clearLocationsCache($game_slot->divisions);

							// We intentionally don't redirect here, leaving the user back on the
							// original "add" form, with the last game date/start/end/weeks options
							// already selected.
						} else {
							$this->viewBuilder()->setTemplate('add');
						}
					}
				} else {
					$this->viewBuilder()->setTemplate('confirm');
				}
			}
		} else {
			// Not a post, set some defaults
			if (isset($field)) {
				$sport = $field->sport;
			} else {
				$sport = current(array_keys(Configure::read('options.sport')));
			}
			$game_slot = $this->GameSlots->patchEntity($game_slot, [
				'game_date' => FrozenDate::now(),
				'sport' => $sport,
			], ['validate' => 'common']);
		}

		$divisions_table = TableRegistry::getTableLocator()->get('Divisions');
		$divisions = $divisions_table->find('open')
			->find('day', ['date' => $game_slot->game_date])
			->contain(['Leagues'])
			->where(['Leagues.affiliate_id' => $affiliate])
			->where(['Leagues.sport' => ($field ? $field->sport : $game_slot->sport)])
			->order(['Divisions.id'])
			->all()
			->combine('id', 'full_league_name')
			->toArray();

		$this->set(compact('game_slot', 'affiliate', 'divisions'));
		$this->set('days', $divisions_table->Days->find('list'));
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('slot');
		try {
			$game_slot = $this->GameSlots->get($id, [
				'contain' => ['Divisions', 'Fields']
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($game_slot);
		$affiliate = $this->GameSlots->affiliate($id);
		$this->Configuration->loadAffiliate($affiliate);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$game_slot = $this->GameSlots->patchEntity($game_slot, $this->getRequest()->getData(), [
				'associated' => ['Divisions'],
				'divisions' => true,
			]);

			// Find the items that are in one or the other but not both. Has to be done before the save.
			$division_ids = collection($game_slot->divisions)->extract('id')->toArray();
			$old_division_ids = collection($game_slot->getOriginal('divisions'))->extract('id')->toArray();
			$intersect = array_intersect($division_ids, $old_division_ids);
			$diff = array_merge(array_diff($division_ids, $intersect), array_diff($old_division_ids, $intersect));

			if ($this->GameSlots->save($game_slot, ['single' => true])) {
				$this->Flash->success(__('The game slot has been saved.'));
				$this->GameSlots->Divisions->clearLocationsCache($diff);

				return $this->redirect(['action' => 'view', '?' => ['slot' => $id]]);
			} else {
				$this->Flash->warning(__('The game slot could not be saved. Please correct the errors below and try again.'));
			}
		}

		$divisions = $this->GameSlots->Games->Divisions->find('open')
			->find('day', ['date' => $game_slot->game_date])
			->contain(['Leagues'])
			->where(['Leagues.affiliate_id' => $affiliate])
			->where(['Leagues.sport' => $this->GameSlots->sport($id)])
			->order(['Divisions.id'])
			->all()
			->combine('id', 'full_league_name')
			->toArray();

		$this->set(compact('game_slot', 'affiliate', 'divisions'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		try {
			$game_slot = $this->GameSlots->get($this->getRequest()->getQuery('slot'), [
				'contain' => ['Divisions']
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($game_slot);

		if ($this->GameSlots->delete($game_slot)) {
			$this->Flash->success(__('The game slot has been deleted.'));
			$this->GameSlots->Divisions->clearLocationsCache($game_slot->divisions);
		} else if ($game_slot->getError('delete')) {
			$this->Flash->warning(current($game_slot->getError('delete')));
		} else {
			$this->Flash->warning(__('The game slot could not be deleted. Please, try again.'));
		}

		return $this->redirect('/');
	}

	/**
	 * Submit score method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful submission, renders view otherwise.
	 */
	public function submit_score() {
		$id = $this->getRequest()->getQuery('slot');
		try {
			/** @var GameSlot $game_slot */
			$game_slot = $this->GameSlots->get($id, [
				'contain' => [
					'Games' => [
						'HomeTeam',
						'HomePoolTeam' => ['DependencyPool'],
						'Divisions' => ['Leagues'],
						'Officials',
					],
					'Fields' => ['Facilities' => 'Regions'],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($game_slot);

		if (empty($game_slot->games)) {
			$this->Flash->info(__('This game slot has no games associated with it.'));
			return $this->redirect('/');
		}
		$game = $game_slot->games[0];
		if ($game->division->schedule_type !== 'competition') {
			$this->Flash->info(__('You can only enter scores for multiple games in "competition" divisions.'));
			return $this->redirect('/');
		}
		if ($game->isFinalized()) {
			$this->Flash->info(__('Games in this slot have already been finalized.'));
			return $this->redirect('/');
		}
		if ($game_slot->end_time->subHours(1)->isFuture()) {
			$this->Flash->info(__('That game has not yet occurred!'));
			return $this->redirect('/');
		}

		$this->Configuration->loadAffiliate($game_slot->field->facility->region->affiliate_id);
		$ratings_obj = $this->moduleRegistry->load("Ratings:{$game->division->rating_calculator}");

		$this->set(compact('game_slot'));

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$game_slot->setDirty('games');
			$data = $this->getRequest()->getData();
			$unplayed = in_array($data['game']['status'], Configure::read('unplayed_status'));

			// We could put these as hidden fields in the form, but we'd need to validate them against the values from the URL
			// anyway, so it's easier to just set them directly here.
			// We use the team_id as the array index, here and in the views, because order matters, and this is a good way to
			// ensure that the correct data gets into the correct form.
			foreach ($game_slot->games as $game) {
				if ($unplayed) {
					$this->GameSlots->Games->patchEntity($game, [
						'status' => $data['game']['status'],
						'approved_by_id' => $this->UserCache->currentId(),
					]);
				} elseif (!array_key_exists($game->home_team_id, $data['games'])) {
					$game->setError('home_score', __('Scores must be entered for all teams.'));
				} else {
					$details = $data['games'][$game->home_team_id];
					// TODO: Do something with a call to $game->adjustScoreAndRatings? It will need to be adjusted to handle
					// competition differences. For now, all that function does that this doesn't is stats, and we have no idea
					// how stats might play out in competition divisions, so this will suffice.
					$rating_points = $ratings_obj->calculateRatingsChange($details['home_score'], 0, 0);
					$details = array_merge($details, [
						'status' => $data['game']['status'],
						'away_score' => 0,
						'rating_points' => $rating_points,
						'approved_by_id' => $this->UserCache->currentId(),
						'home_team' => [
							'rating' => $game->home_team->rating + $rating_points,
							// Any time that this is called, the division seeding might change.
							// We just reset it here, and it will be recalculated as required elsewhere.
							'seed' => 0,
						],
					]);

					$this->GameSlots->Games->patchEntity($game, $details);
				}
			}

			// We don't actually want to update the "modified" column in the games table here
			if ($this->GameSlots->Games->hasBehavior('Timestamp')) {
				$this->GameSlots->Games->removeBehavior('Timestamp');
			}

			if ($this->GameSlots->save($game_slot)) {
				$this->Flash->success(__('Scores have been saved and game results posted.'));
				return $this->redirect('/');
			}

			$this->Flash->warning(__('The scores could not be saved. Please correct the errors below and try again.'));
		}
	}
}
