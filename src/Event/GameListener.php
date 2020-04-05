<?php
/**
 * Implementation of game event listeners.
 */

namespace App\Event;

use App\Core\UserCache;
use App\Model\Entity\Game;
use App\Model\Entity\Team;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventListenerInterface;
use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class GameListener implements EventListenerInterface {

	use FlashTrait;

	public function implementedEvents() {
		return [
			'Model.Game.incidentReport' => 'incidentReport',
			'Model.Game.scoreSubmission' => 'scoreSubmission',
			'Model.Game.scoreMismatch' => 'scoreMismatch',
			'Model.Game.scoreApproval' => 'scoreApproval',
			'Model.Game.remindTeam' => 'remindTeam',
		];
	}

	public function incidentReport(CakeEvent $cakeEvent, Game $game) {
		$addr = Configure::read('email.incident_report_email');
		if (AppController::_sendMail([
			'to' => [$addr => __('Incident Manager')],
			'replyTo' => UserCache::getInstance()->read('Person'),
			'subject' => function() use ($game) { return __('Incident report: {0}', $game->incidents[0]->type); },
			'template' => 'incident_report',
			'sendAs' => 'both',
			'viewVars' => [
				'incident' => $game->incidents[0],
				'game' => $game,
				'division' => $game->division,
				'slot' => $game->game_slot,
				'field' => $game->game_slot->field,
				'home_team' => $game->home_team,
				'away_team' => $game->away_team,
			],
		]))
		{
			// TODO: Maybe send the incident report before saving data, and add in a column for
			// whether it was sent, thus allowing the cron to attempt to re-send it?
			$this->Flash('success', __('Your incident report details have been sent for handling.'));
		} else {
			$this->Flash('warning', __('There was an error sending your incident report details. Please send them to {0} to ensure proper handling.', [
				'params' => [
					'link' => $addr,
					'target' => "mailto:$addr",
				],
			]));
		}
	}

	public function scoreSubmission(CakeEvent $cakeEvent, Game $game) {
		if ($game->isFinalized()) {
			// The afterSave function must have finalized it
			$this->Flash('success', __('This score agrees with the score submitted by your opponent. It will now be posted as an official game result.'));
			return;
		}

		// Just mention that it's been saved and move on
		$status = $game->score_entries[0]->status;
		if (in_array($status, Configure::read('unplayed_status'))) {
			$team_status = $opponent_status = __($status);
		} else {
			$score_for = $game->score_entries[0]->score_for;
			$score_against = $game->score_entries[0]->score_against;
			$default = (strpos($status, 'default') !== false);
			if ($score_for > $score_against) {
				$team_status = __('a win for your team');
				if ($default) {
					$opponent_status = __('a default loss for your team');
				} else {
					$opponent_status = __('a {0}-{1} loss for your team', $score_for, $score_against);
				}
			} else {
				if ($score_for < $score_against) {
					$team_status = __('a loss for your team');
					if ($default) {
						$opponent_status = __('a default win for your team');
					} else {
						$opponent_status = __('a {0}-{1} win for your team', $score_against, $score_for);
					}
				} else {
					$team_status = __('a tie');
					$opponent_status = __('a {0}-{1} tie', $score_for, $score_against);
				}
			}

			// We need to swap the for and against scores to reflect the opponent's view in the email below
			list($score_against, $score_for) = [$score_for, $score_against];
		}

		$this->Flash('html', __('This score has been saved. Once your opponent has entered their score, it will be officially posted. The score you have submitted indicates that this game was {0}. If this is incorrect, you can {1} to correct it.'), [
			'params' => [
				'class' => 'success',
				'replacements' => [
					[
						'text' => $team_status,
					],
					[
						'type' => 'link',
						'link' => __('edit the score'),
						'target' => ['controller' => 'Games', 'action' => 'submit_score', 'game' => $game->id, 'team' => $game->score_entries[0]->team_id],
					],
				],
			],
		]);

		if ($game->score_entries[0]->team_id == $game->home_team_id) {
			$team = $game->home_team;
			$opponent = $game->away_team;
		} else {
			$team = $game->away_team;
			$opponent = $game->home_team;
		}

		// Email opposing captains with this score and an easy link
		$captains = collection($opponent->people)->filter(function ($player) {
			return in_array($player->_joinData->role, Configure::read('privileged_roster_roles')) && $player->_joinData->status == ROSTER_APPROVED;
		})->toArray();
		if (!empty($captains)) {
			AppController::_sendMail([
				'to' => $captains,
				'replyTo' => UserCache::getInstance()->read('Person'),
				'subject' => function() { return __('Opponent score submission'); },
				'template' => 'score_submission',
				'sendAs' => 'both',
				'viewVars' => array_merge([
					'captains' => implode(', ', collection($captains)->extract('first_name')->toArray()),
					'division' => $game->division,
				], compact('game', 'status', 'opponent_status', 'score_for', 'score_against', 'team', 'opponent')),
			]);
		}
	}

	public function scoreMismatch(CakeEvent $cakeEvent, Game $game) {
		// TODO: Do this on a recurring basis, every few days, instead of just once.
		if (empty($game->score_mismatch_emails)) {
			if (AppController::_sendMail([
				'to' => $game->division->people,
				'subject' => function() { return __('Score entry mismatch'); },
				'template' => 'score_entry_mismatch',
				'sendAs' => 'both',
				'viewVars' => compact('game'),
			]))
			{
				$logs_table = TableRegistry::get('ActivityLogs');
				$logs_table->save($logs_table->newEntity(['type' => 'email_score_mismatch', 'game_id' => $game->id]));
			}
		}

		$this->Flash('html', __('This score doesn\'t agree with the one your opponent submitted. Because of this, the score will not be posted until your coordinator approves it. Alternately, whichever coach or captain made an error can {0}.'), [
			'params' => [
				'class' => 'warning',
				'replacements' => [
					[
						'type' => 'link',
						'link' => __('edit their submission'),
						'target' => ['controller' => 'Games', 'action' => 'submit_score', 'game' => $game->id, 'team' => $game->score_entries[0]->team_id],
					],
				],
			],
		]);
	}

	public function scoreApproval(CakeEvent $cakeEvent, Game $game, Team $team, Team $opponent) {
		if ($game->getScoreEntry($team->id)->person_id) {
			return;
		}

		$captains = collection($team->people)->filter(function ($player) {
			return in_array($player->_joinData->role, Configure::read('privileged_roster_roles')) && $player->_joinData->status == ROSTER_APPROVED;
		});
		AppController::_sendMail([
			'to' => $captains->toArray(),
			'replyTo' => $game->division->people,
			'subject' => function() use ($team) { return __('{0} notification of score approval', $team->name); },
			'template' => 'score_approval',
			'sendAs' => 'both',
			'viewVars' => [
				'team' => $team,
				'opponent' => $opponent,
				'division' => $game->division,
				'game' => $game,
				'captains' => implode(', ', $captains->extract('first_name')->toArray()),
			],
			'header' => [
				'Auto-Submitted' => 'auto-generated',
				'X-Auto-Response-Suppress' => 'OOF',
			],
		]);
	}

	public function remindTeam(CakeEvent $cakeEvent, Game $game, Team $team = null, Team $opponent = null) {
		// TODO: Do this on a recurring basis, every few days, instead of just once.
		if (!$team || $game->getScoreEntry($team->id)->person_id || $game->getScoreReminderEmail($team->id)) {
			return;
		}

		$captains = collection($team->people)->filter(function ($player) {
			return in_array($player->_joinData->role, Configure::read('privileged_roster_roles')) && $player->_joinData->status == ROSTER_APPROVED;
		});
		if (AppController::_sendMail([
			'to' => $captains->toArray(),
			'replyTo' => $game->division->people,
			'subject' => function() use ($team) { return __('{0} reminder to submit score', $team->name); },
			'template' => 'score_reminder',
			'sendAs' => 'both',
			'viewVars' => [
				'team' => $team,
				'opponent' => $opponent,
				'division' => $game->division,
				'game' => $game,
				'captains' => implode(', ', $captains->extract('first_name')->toArray()),
			],
			'header' => [
				'Auto-Submitted' => 'auto-generated',
				'X-Auto-Response-Suppress' => 'OOF',
			],
		]))
		{
			$logs_table = TableRegistry::get('ActivityLogs');
			$logs_table->save($logs_table->newEntity(['type' => 'email_score_reminder', 'game_id' => $game->id, 'team_id' => $team->id]));
		}
	}

}
