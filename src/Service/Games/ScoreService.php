<?php
declare(strict_types=1);

namespace App\Service\Games;

use App\Model\Entity\ScoreEntry;
use Cake\ORM\TableRegistry;

class ScoreService
{
	/**
	 * @var ScoreEntry[]
	 */
	private array $entries;

	public function __construct(array $entries)
	{
		$this->entries = $entries;
	}

	public function hasScoreEntryFrom(?int $team_id): bool
	{
		$entry = $this->getScoreEntryFrom($team_id);
		return $entry && $entry->person_id;
	}

	/**
	 * Retrieve finalized score entry for given team.
	 *
	 * @param int $team_id ID of the team to find the score entry from
	 * @return ScoreEntry Entity with the requested score entry, or null if the team hasn't entered a final score yet.
	 */
	public function getScoreEntryFrom(?int $team_id): ?ScoreEntry
	{
		if (!$team_id) {
			return null;
		}

		if (!empty($this->entries)) {
			foreach ($this->entries as $entry) {
				if ($entry->team_id == $team_id) {
					return $entry;
				}
			}
		}

		return null;
	}

	public function getOrFindScoreEntryFrom(?int $team_id, int $game_id): ?ScoreEntry
	{
		$entry = $this->getScoreEntryFrom($team_id);
		if ($entry) {
			return $entry;
		}

		$score_entries_table = TableRegistry::getTableLocator()->get('ScoreEntries');
		/** @var ScoreEntry $entry */
		$entry = $score_entries_table->find()
			->where([
				'game_id' => $game_id,
				'team_id' => $team_id,
				'status !=' => 'in_progress',
			])
			->first();

		return $entry;
	}

	/**
	 * Retrieve the best score entry for a game.
	 *
	 * @return ScoreEntry|bool|null Entity with the best score entry, false if neither team has entered a score yet,
	 * or null if there is no clear "best" entry.
	 */
	public function getBestScoreEntry()
	{
		if (empty($this->entries)) {
			return false;
		}

		switch (count($this->entries)) {
			case 0:
				return false;

			case 1:
				return current($this->entries);

			case 2:
				$entries = array_values($this->entries);
				if ($this->scoreEntriesAgree($entries[0], $entries[1])) {
					return $entries[0];
				} else if ($entries[0]->status === 'in_progress' && $entries[1]->status !== 'in_progress') {
					return $entries[1];
				} else if ($entries[0]->status !== 'in_progress' && $entries[1]->status === 'in_progress') {
					return $entries[0];
				} else if ($entries[0]->status === 'in_progress' && $entries[1]->status === 'in_progress') {
					return ($entries[0]->modified > $entries[1]->modified ? $entries[0] : $entries[1]);
				}
		}

		return null;
	}

	/**
	 * Compare two score entries
	 */
	public static function scoreEntriesAgree(ScoreEntry $one, ScoreEntry $two): bool
	{
		if ($one->status == $two->status) {
			if (in_array($one->status, ['normal', 'in_progress'])) {
				// If carbon flips aren't enabled, both will have a score of 0 there, and they'll match anyway
				return (($one->score_for == $two->score_against) && ($one->score_against == $two->score_for) && ($one->home_carbon_flip == $two->home_carbon_flip));
			}
			return true;
		}

		return false;
	}
}
