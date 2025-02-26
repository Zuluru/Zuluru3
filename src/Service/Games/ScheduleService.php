<?php
declare(strict_types=1);

namespace App\Service\Games;

use App\Controller\Component\LockComponent;
use App\Exception\ScheduleException;
use App\Model\Entity\Game;
use App\Model\Entity\League;
use App\Model\Table\GamesTable;
use Cake\Controller\Component\FlashComponent;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;

class ScheduleService
{
	use EventDispatcherTrait;

	private GamesTable $Games;
	private FlashComponent $Flash;
	private LockComponent $Lock;

	public function __construct(GamesTable $gamesTable, FlashComponent $flash, LockComponent $lock)
	{
		$this->Games = $gamesTable;
		$this->Flash = $flash;
		$this->Lock = $lock;
	}

	public function update(League $league, array $games, array $game_slots, array $data): bool
	{
		if ($this->Lock->lock('scheduling', $league->affiliate_id, 'schedule creation or edit')) {
			try {
				if ($this->Games->getConnection()->transactional(function () use ($data, $games, $game_slots) {
					$edit_games = $this->Games->patchEntities($games, $data['games'],
						array_merge($data['options'], ['validate' => 'scheduleEdit'])
					);

					// Skip anything that wasn't actually updated
					$updated_games = collection($edit_games)
						->filter(function (Game $game) { return $game->isDirty(); })
						->toList();
					if (empty($updated_games)) {
						return true;
					}

					$edit_ids = collection($edit_games)->extract('id')->toArray();
					$other_games = collection($games)->reject(function ($game) use ($edit_ids) {
						return in_array($game->id, $edit_ids);
					})->toArray();
					$games = array_merge($edit_games, $other_games);
					usort($games, [GamesTable::class, 'compareDateAndField']);

					// Find all game slots that *were* assigned to one of *these* games but not assigned to any *other* game.
					// Mark them all as unassigned. This will simplify the checking of whether a slot is already assigned,
					// and also handle correct release of slots that are no longer in use. Any slots that are actually
					// assigned to these games will be marked as such by the
					$slot_ids = array_unique(
						collection($updated_games)
							->filter(function (Game $game) { return $game->isDirty('game_slot_id'); })
							->extract(function (Game $game) { return $game->getOriginal('game_slot_id'); })
							->toList()
					);
					$updated_ids = collection($updated_games)->extract('id')->toArray();
					if (!empty($slot_ids)) {
						$used_elsewhere = $this->Games->find()
							->distinct('Games.game_slot_id')
							->where([
								'Games.game_slot_id IN' => $slot_ids,
								'Games.id NOT IN' => $updated_ids,
							])
							->extract('game_slot_id')
							->toArray();
						$unused = array_diff($slot_ids, $used_elsewhere);
						if (!empty($unused)) {
							$this->Games->GameSlots->updateAll(['assigned' => false], ['GameSlots.id IN' => $unused]);
						}
					}

					$success = true;
					$options = array_merge($data['options'], [
						'games' => $edit_games,
						'game_slots' => $game_slots,
						'validate' => 'scheduleEdit',
					]);
					// We intentionally do not use saveMany here; it returns immediately when one save fails,
					// whereas this method will generate error messages for everything applicable.
					foreach ($edit_games as $game) {
						if (!$this->Games->save($game, $options)) {
							$success = false;
						}
					}
					return $success;
				})) {
					$this->Flash->success(__('Schedule changes saved!'));

					// With the saves being inside a transaction, afterSaveCommit is not called.
					$event = new Event('Model.afterSaveCommit', $this, [null]);
					$this->getEventManager()->dispatch($event);

					return true;
				}

				$this->Flash->warning(__('The games could not be saved. Please correct the errors below and try again.'));

				$event = new Event('Model.afterSaveRollback', $this, [null]);
				$this->getEventManager()->dispatch($event);
			} catch (ScheduleException $ex) {
				$this->Flash->html($ex->getMessages(), ['params' => $ex->getAttributes()]);

				$event = new Event('Model.afterSaveRollback', $this, [null]);
				$this->getEventManager()->dispatch($event);
			}
		}

		return false;
	}
}
