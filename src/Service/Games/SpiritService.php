<?php
declare(strict_types=1);

namespace App\Service\Games;

use App\Model\Entity\Game;
use App\Model\Entity\League;
use App\Model\Entity\SpiritEntry;
use App\Module\Spirit;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class SpiritService
{
	/**
	 * @var SpiritEntry[]
	 */
	private array $entries;

	private ?Spirit $module;

	public function __construct(array $entries, ?Spirit $module)
	{
		$this->entries = $entries;
		$this->module = $module;
	}

	public function getEntries(): array
	{
		return $this->entries;
	}

	/**
	 * Return a (possibly empty) array of spirit entries for the specified team.
	 */
	public function getEntriesFor(?int $team_id): array
	{
        if (!$team_id) {
            return [];
        }

        return collection($this->entries)->match(compact('team_id'))->toList();
	}

	public function hasOfficialSpiritEntry(): bool
	{
		return collection($this->entries)->some(function (SpiritEntry $entry) {
			return $entry->created_team_id === 0;
		});
	}

	/**
	 * Return the array index of the desired spirit entry, for getting input forms to line up correctly.
	 */
	public function getEntryIndexFor(?int $team_id, bool $from_official): ?int
	{
		if (!$team_id) {
			return null;
		}

		foreach ($this->entries as $key => $entry) {
			$entry_from_official = $entry->created_team_id === 0;
			if ($team_id === $entry->team_id && $from_official === $entry_from_official) {
				return $key;
			}
		}

		return null;
	}

	/**
	 * Return an entity representing the average of all extant spirit entries for the specified team.
	 */
	public function getAverageEntryFor(?int $team_id, array $questions): ?SpiritEntry
	{
        $entries = $this->getEntriesFor($team_id);
        if (empty($entries)) {
            return null;
        }

        if (count($entries) === 1) {
            return $entries[0];
        }

        /** @var SpiritEntry $ret */
        $ret = TableRegistry::getTableLocator()->get('SpiritEntries')->newEmptyEntity();
        foreach ($questions as $question) {
            $sum = 0;
            foreach ($entries as $entry) {
                $sum += $entry->$question;
            }
            $ret->$question = $sum / count($entries);
        }

        return $ret;
	}

	/**
	 * Returns the numeric spirit score for a particular team, incorporating all applicable entries (whether from the
	 * opposition or an official). If there are no such entries, null is returned, as this is quite a different
	 * scenario from a zero score.
	 */
	public function getScoreFor(?int $team_id, League $league): ?float
	{
		$entries = $this->getEntriesFor($team_id);
		if (empty($entries)) {
			return null;
		}

		if ($league->numeric_sotg) {
			return collection($entries)->avg('entered_sotg');
		}

		return collection($entries)->avg(function (SpiritEntry $entry) { return $this->module->calculate($entry); });
	}

	public function addHomePenaltyEntry(Game $game): void
	{
		$this->addPenaltyEntry($game->home_team_id, $game->away_team_id);
	}

	public function addAwayPenaltyEntry(Game $game): void
	{
		$this->addPenaltyEntry($game->away_team_id, $game->home_team_id);
	}

	private function addPenaltyEntry(int $penalized_team_id, int $default_team_id): void
	{
		// Add the penalty to all applicable spirit entries (could be both opponent and official)
		$penalty = Configure::read('scoring.missing_score_spirit_penalty');
		if ($penalty) {
			foreach ($this->entries as $entry) {
				if ($entry->team_id === $penalized_team_id) {
					$entry->score_entry_penalty = -$penalty;
				}
			}
		}

		// If spirit scores do not only come from officials, create the default entry for the team that didn't get one
		// from their opponent.
		if (Configure::read('scoring.spirit_entry_by') !== SPIRIT_BY_OFFICIAL) {
			$this->entries[] = $this->getDefaultEntryFor($default_team_id, $penalized_team_id);
		}
	}

	public function getDefaultEntryFor(int $for_team_id, int $from_team_id): SpiritEntry
	{
		/** @var SpiritEntry $default */
		$default = TableRegistry::getTableLocator()->get('SpiritEntries')->newEntity(array_merge(
			$this->module->expected(false),
			[
				'team_id' => $for_team_id,
				'created_team_id' => $from_team_id,
			]
		));

		return $default;
	}
}
