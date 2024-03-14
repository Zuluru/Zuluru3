<?php
/**
 * Derived class for implementing functionality for spirit scoring by the WODS questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;
use App\Model\Entity\SpiritEntry;

class SpiritModifiedWfdf extends Spirit {
	public $ratios = [
		'perfect' => 0.75,
		'ok' => 0.5,
		'caution' => 0.25,
		'not_ok' => 0,
	];

	public function __construct() {
		$this->description = __('The Modified WFDF spirit survey was developed by the Waterloo Organization of Disc Sports to reflect league play rather than tournaments; some WFDF questions have been split, for example, and answers have been simplified from five to three. The survey answers are intended to reward good spirit rather than penalizing bad.');

		$this->questions = [
			'q1' => [
				'name' => __('Respect'),
				'text' => __('Respect'),
				'desc' => __('They communicated objectively and without aggressive language. They were willing to believe calls were made in good faith. Were on time. Kept to time limits for discussions, time-outs, between points, etc.'),
				'type' => 'radio',
				'options' => [
					'poorRespect' => [
						'text' => __('Below average'),
						'value' => 0,
					],
					'averageRespect' => [
						'text' => __('Average'),
						'value' => 1,
						'default' => true,
					],
					'exceptionalRespect' => [
						'text' => __('Above average'),
						'value' => 2,
					],
				],
			],
			'q2' => [
				'name' => __('Fair-Mindedness'),
				'text' => __('Fair-Mindedness'),
				'desc' => __('Players pointed out their own fouls. They corrected their own team player calls. In an important situation they admitted that the opponent was probably right. Avoided frequently calling non-obvious travels and picks.'),
				'type' => 'radio',
				'options' => [
					'poorFairMindedness' => [
						'text' => __('Below average'),
						'value' => 0,
					],
					'averageFairMindedness' => [
						'text' => __('Average'),
						'value' => 1,
						'default' => true,
					],
					'exceptionalFairMindedness' => [
						'text' => __('Above average'),
						'value' => 2,
					],
				],
			],
			'q3' => [
				'name' => __('Attitude'),
				'text' => __('Positive Attitude'),
				'desc' => __('They introduced themselves to the opponent. They complimented the opponent for good plays.  Left a positive impression in an after-the-game Spirit Circle, etc.'),
				'type' => 'radio',
				'options' => [
					'poorPositiveAttitude' => [
						'text' => __('Below average'),
						'value' => 0,
					],
					'averagePositiveAttitude' => [
						'text' => __('Average'),
						'value' => 1,
						'default' => true,
					],
					'exceptionalPositiveAttitude' => [
						'text' => __('Above average'),
						'value' => 2,
					],
				],
			],
			'q4' => [
				'name' => __('Emotional Management'),
				'text' => __('Emotional Management'),
				'desc' => __('Their reaction towards disagreements, successes, and mistakes was appropriately mature.'),
				'type' => 'radio',
				'options' => [
					'poorEmotionalManagement' => [
						'text' => __('Below average'),
						'value' => 0,
					],
					'averageEmotionalManagement' => [
						'text' => __('Average'),
						'value' => 1,
						'default' => true,
					],
					'exceptionalEmotionalManagement' => [
						'text' => __('Above average'),
						'value' => 2,
					],
				],
			],
			'q5' => [
				'name' => __('Body Contact'),
				'text' => __('Avoiding Body Contact'),
				'desc' => __('They were aware of other players\' body location and movement and avoided dangerous plays.'),
				'type' => 'radio',
				'options' => [
					'poorAvoidingBodyContact' => [
						'text' => __('Below average'),
						'value' => 0,
					],
					'averageAvoidingBodyContact' => [
						'text' => __('Average'),
						'value' => 1,
						'default' => true,
					],
					'exceptionalAvoidingBodyContact' => [
						'text' => __('Above average'),
						'value' => 2,
					],
				],
			],
			'q6' => [
				'name' => __('Fouls'),
				'text' => __('Avoiding Violations and Fouls'),
				'desc' => __('They tried to avoid fouls and violations. Their marks were legal. They did not commit off side violations, etc.'),
				'type' => 'radio',
				'options' => [
					'poorAvoidingViolationsandFouls' => [
						'text' => __('Below average'),
						'value' => 0,
					],
					'averageAvoidingViolationsandFouls' => [
						'text' => __('Average'),
						'value' => 1,
						'default' => true,
					],
					'exceptionalAvoidingViolationsandFouls' => [
						'text' => __('Above average'),
						'value' => 2,
					],
				],
			],
			'q7' => [
				'name' => __('Rules Knowledge'),
				'text' => __('Knowledge of the Rules'),
				'desc' => __('They knew the rules and/or had the willingness to learn and teach them. They did not make unjustified calls.'),
				'type' => 'radio',
				'options' => [
					'poorKnowledgeoftheRules' => [
						'text' => __('Below average'),
						'value' => 0,
					],
					'averageKnowledgeoftheRules' => [
						'text' => __('Average'),
						'value' => 1,
						'default' => true,
					],
					'exceptionalKnowledgeoftheRules' => [
						'text' => __('Above average'),
						'value' => 2,
					],
				],
			],
			'q8' => [
				'name' => __('Enjoyment'),
				'text' => __('Encouraging Enjoyment of the Game'),
				'desc' => __('They played the game in a way that made the game enjoyable for all those involved.'),
				'type' => 'radio',
				'options' => [
					'poorEncouragingEnjoymentoftheGame' => [
						'text' => __('Below average'),
						'value' => 0,
					],
					'averageEncouragingEnjoymentoftheGame' => [
						'text' => __('Average'),
						'value' => 1,
						'default' => true,
					],
					'exceptionalEncouragingEnjoymentoftheGame' => [
						'text' => __('Above average'),
						'value' => 2,
					],
				],
			],
			'comments' => [
				'name' => __('Comments'),
				'text' => __('Do you have any concerns from this game that you would like to bring to the coordinator\'s attention? These will be kept confidential.'),
				'type' => 'textarea',
				'restricted' => true,
			],
			'highlights' => [
				'name' => __('Highlights'),
				'text' => __('Do you have any spirit highlights from this game that you would like to bring to the coordinator\'s attention? These may be published.'),
				'type' => 'textarea',
				'restricted' => true,
			],
		];

		parent::__construct();
	}

	public function addValidation(Validator $validator, League $league) {
		return parent::addValidation($validator, $league)
			->range('q1', [0, 2], __('Select one of the given options.'))
			->requirePresence('q1', 'create')
			->notEmptyString('q1')

			->range('q2', [0, 2], __('Select one of the given options.'))
			->requirePresence('q2', 'create')
			->notEmptyString('q2')

			->range('q3', [0, 2], __('Select one of the given options.'))
			->requirePresence('q3', 'create')
			->notEmptyString('q3')

			->range('q4', [0, 2], __('Select one of the given options.'))
			->requirePresence('q4', 'create')
			->notEmptyString('q4')

			->range('q5', [0, 2], __('Select one of the given options.'))
			->requirePresence('q5', 'create')
			->notEmptyString('q5')

			->range('q6', [0, 2], __('Select one of the given options.'))
			->requirePresence('q6', 'create')
			->notEmptyString('q6')

			->range('q7', [0, 2], __('Select one of the given options.'))
			->requirePresence('q7', 'create')
			->notEmptyString('q7')

			->range('q8', [0, 2], __('Select one of the given options.'))
			->requirePresence('q8', 'create')
			->notEmptyString('q8')

			;
	}

	public function expected($entity = true) {
		$expected = [
			'entered_sotg' => 8,
			'score_entry_penalty' => 0,
			'q1' => 1,
			'q2' => 1,
			'q3' => 1,
			'q4' => 1,
			'q5' => 1,
			'q6' => 1,
			'q7' => 1,
			'q8' => 1,
		];
		if ($entity) {
			return new SpiritEntry($expected);
		} else {
			return $expected;
		}
	}
}
