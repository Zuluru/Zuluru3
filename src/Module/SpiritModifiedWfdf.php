<?php

/**
 * Derived class for implementing functionality for spirit scoring by the WODS questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;
use App\Model\Entity\SpiritEntry;

class SpiritModifiedWfdf extends Spirit {
	public $questions = [
		'q1' => [
			'name' => 'Respect',
			'text' => 'Respect',
			'desc' => 'They communicated objectively and without aggressive language. They were willing to believe calls were made in good faith. Were on time. Kept to time limits for discussions, time-outs, between points, etc.',
			'type' => 'radio',
			'options' => [
				'poorRespect' => [
					'text' => 'Below Average',
					'value' => 0,
				],
				'averageRespect' => [
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				],
				'exceptionalRespect' => [
					'text' => 'Above Average',
					'value' => 2,
				],
			],
		],
		'q2' => [
			'name' => 'Fair-Mindedness',
			'text' => 'Fair-Mindedness',
			'desc' => 'Players pointed out their own fouls. They corrected their own team player calls. In an important situation they admitted that the opponent was probably right. Avoided frequently calling non-obvious travels and picks.',
			'type' => 'radio',
			'options' => [
				'poorFairMindedness' => [
					'text' => 'Below Average',
					'value' => 0,
				],
				'averageFairMindedness' => [
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				],
				'exceptionalFairMindedness' => [
					'text' => 'Above Average',
					'value' => 2,
				],
			],
		],
		'q3' => [
			'name' => 'Attitude',
			'text' => 'Positive Attitude',
			'desc' => 'They introduced themselves to the opponent. They complimented the opponent for good plays.  Left a positive impression in an after-the-game Spirit Circle, etc.',
			'type' => 'radio',
			'options' => [
				'poorPositiveAttitude' => [
					'text' => 'Below Average',
					'value' => 0,
				],
				'averagePositiveAttitude' => [
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				],
				'exceptionalPositiveAttitude' => [
					'text' => 'Above Average',
					'value' => 2,
				],
			],
		],
		'q4' => [
			'name' => 'Emotional Management',
			'text' => 'Emotional Management',
			'desc' => 'Their reaction towards disagreements, successes, and mistakes was appropriately mature.',
			'type' => 'radio',
			'options' => [
				'poorEmotionalManagement' => [
					'text' => 'Below Average',
					'value' => 0,
				],
				'averageEmotionalManagement' => [
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				],
				'exceptionalEmotionalManagement' => [
					'text' => 'Above Average',
					'value' => 2,
				],
			],
		],
		'q5' => [
			'name' => 'Body Contact',
			'text' => 'Avoiding Body Contact',
			'desc' => 'They were aware of other players\' body location and movement and avoided dangerous plays.',
			'type' => 'radio',
			'options' => [
				'poorAvoidingBodyContact' => [
					'text' => 'Below Average',
					'value' => 0,
				],
				'averageAvoidingBodyContact' => [
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				],
				'exceptionalAvoidingBodyContact' => [
					'text' => 'Above Average',
					'value' => 2,
				],
			],
		],
		'q6' => [
			'name' => 'Fouls',
			'text' => 'Avoiding Violations and Fouls',
			'desc' => 'They tried to avoid fouls and violations. Their marks were legal. They did not commit off side violations, etc.',
			'type' => 'radio',
			'options' => [
				'poorAvoidingViolationsandFouls' => [
					'text' => 'Below Average',
					'value' => 0,
				],
				'averageAvoidingViolationsandFouls' => [
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				],
				'exceptionalAvoidingViolationsandFouls' => [
					'text' => 'Above Average',
					'value' => 2,
				],
			],
		],
		'q7' => [
			'name' => 'Rules Knowledge',
			'text' => 'Knowledge of the Rules',
			'desc' => 'They knew the rules and/or had the willingness to learn and teach them. They did not make unjustified calls.',
			'type' => 'radio',
			'options' => [
				'poorKnowledgeoftheRules' => [
					'text' => 'Below Average',
					'value' => 0,
				],
				'averageKnowledgeoftheRules' => [
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				],
				'exceptionalKnowledgeoftheRules' => [
					'text' => 'Above Average',
					'value' => 2,
				],
			],
		],
		'q8' => [
			'name' => 'Enjoyment',
			'text' => 'Encouraging Enjoyment of the Game',
			'desc' => 'They played the game in a way that made the game enjoyable for all those involved.',
			'type' => 'radio',
			'options' => [
				'poorEncouragingEnjoymentoftheGame' => [
					'text' => 'Below Average',
					'value' => 0,
				],
				'averageEncouragingEnjoymentoftheGame' => [
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				],
				'exceptionalEncouragingEnjoymentoftheGame' => [
					'text' => 'Above Average',
					'value' => 2,
				],
			],
		],
		'comments' => [
			'name' => 'Comments',
			'text' => 'Do you have any concerns from this game that you would like to bring to the coordinator\'s attention? These will be kept confidential.',
			'type' => 'textarea',
			'restricted' => true,
		],
		'highlights' => [
			'name' => 'Highlights',
			'text' => 'Do you have any spirit highlights from this game that you would like to bring to the coordinator\'s attention? These may be published.',
			'type' => 'textarea',
			'restricted' => true,
		],
	];

	public $ratios = [
		'perfect' => 0.75,
		'ok' => 0.5,
		'caution' => 0.25,
		'not_ok' => 0,
	];

	public function __construct() {
		$this->description = __('The Modified WFDF spirit survey was developed by the Waterloo Organization of Disc Sports to reflect league play rather than tournaments; some WFDF questions have been split, for example, and answers have been simplified from five to three. The survey answers are intended to reward good spirit rather than penalizing bad.');
		parent::__construct();
	}

	public function addValidation(Validator $validator, League $league) {
		return parent::addValidation($validator, $league)
			->range('q1', [0, 2], __('Select one of the given options.'))
			->requirePresence('q1', 'create')
			->notEmpty('q1')

			->range('q2', [0, 2], __('Select one of the given options.'))
			->requirePresence('q2', 'create')
			->notEmpty('q2')

			->range('q3', [0, 2], __('Select one of the given options.'))
			->requirePresence('q3', 'create')
			->notEmpty('q3')

			->range('q4', [0, 2], __('Select one of the given options.'))
			->requirePresence('q4', 'create')
			->notEmpty('q4')

			->range('q5', [0, 2], __('Select one of the given options.'))
			->requirePresence('q5', 'create')
			->notEmpty('q5')

			->range('q6', [0, 2], __('Select one of the given options.'))
			->requirePresence('q6', 'create')
			->notEmpty('q6')

			->range('q7', [0, 2], __('Select one of the given options.'))
			->requirePresence('q7', 'create')
			->notEmpty('q7')

			->range('q8', [0, 2], __('Select one of the given options.'))
			->requirePresence('q8', 'create')
			->notEmpty('q8')

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
