<?php
namespace App\Shell\Task;

use App\Middleware\ConfigurationLoader;
use App\Model\Entity\Game;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * FinalizeGames Task
 *
 * @property \App\Model\Table\GamesTable $games_table
 */
class FinalizeGamesTask extends Shell {

	public function main() {
		ConfigurationLoader::loadConfiguration();
		$this->games_table = TableRegistry::getTableLocator()->get('Games');

		$captain_contain = [
			'People' => [
				'queryBuilder' => function (Query $q) {
					return $q->where([
						'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
						'TeamsPeople.status' => ROSTER_APPROVED,
					]);
				},
				Configure::read('Security.authModel'),
			],
		];

		$contain = [
			'GameSlots',
			'Divisions' => [
				'People' => [
					Configure::read('Security.authModel'),
				],
				'Leagues',
			],
			// Get the list of captains for each team, we may need to email them
			'HomeTeam' => $captain_contain,
			'AwayTeam' => $captain_contain,
			'ScoreEntries',
			'SpiritEntries',
			'ScoreReminderEmails',
			'ScoreMismatchEmails',
		];
		$games = $this->games_table->find('played')
			->contain($contain)
			->where([
				'Divisions.is_open' => true,
				'Games.published' => true,
				'GameSlots.game_date <=' => FrozenDate::now(),
				['OR' => [
					'Games.home_score IS' => null,
					'Games.away_score IS' => null,
				]],
				['OR' => [
					'Divisions.email_after >' => 0,
					'Divisions.finalize_after >' => 0,
				]],
			])
			->order(['Divisions.id', 'GameSlots.game_date', 'GameSlots.game_start', 'Games.id']);

		foreach ($games as $game) {
			$this->handleGame($game);
		}
	}

	private function handleGame(Game $game) {
		if ($game->division->finalize_after > 0 && $game->game_slot->start_time->addHours($game->division->finalize_after)->isPast()) {
			if ($game->finalize() === true) {
				$this->games_table->save($game);
				return;
			}
		}

		if ($game->division->email_after > 0 && $game->game_slot->start_time->addHours($game->division->email_after)->isPast()) {
			$event = new CakeEvent('Model.Game.remindTeam', $this, [$game, $game->home_team, $game->away_team]);
			EventManager::instance()->dispatch($event);
			$event = new CakeEvent('Model.Game.remindTeam', $this, [$game, $game->away_team, $game->home_team]);
			EventManager::instance()->dispatch($event);
		}
	}

}
