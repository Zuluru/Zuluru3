<?php

/**
 * Derived class for implementing functionality for spirit scoring by the updated WFDF questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;
use App\Model\Entity\SpiritEntry;

class SpiritWfdf2Inclusivity extends Spirit {
	public $questions = [
		'q1' => [
			'name' => 'Rules Knowledge',
			'text' => 'Rules Knowledge and Use',
			'desc' => 'Examples: They did not purposefully misinterpret the rules. They kept to time limits. When they didn\'t know the rules they showed a real willingness to learn.',
			'type' => 'radio',
			'options' => [
				'PoorRules' => [
					'text' => 'poor',
					'value' => 0,
				],
				'NotGoodRules' => [
					'text' => 'not good',
					'value' => 1,
				],
				'GoodRules' => [
					'text' => 'good',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodRules' => [
					'text' => 'very good',
					'value' => 3,
				],
				'ExcellentRules' => [
					'text' => 'excellent',
					'value' => 4,
				],
			],
		],
		'q2' => [
			'name' => 'Fouls',
			'text' => 'Fouls and Body Contact',
			'desc' => 'Examples: They avoided fouling, contact, and dangerous plays.',
			'type' => 'radio',
			'options' => [
				'PoorFouls' => [
					'text' => 'poor',
					'value' => 0,
				],
				'NotGoodFouls' => [
					'text' => 'not good',
					'value' => 1,
				],
				'GoodFouls' => [
					'text' => 'good',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodFouls' => [
					'text' => 'very good',
					'value' => 3,
				],
				'ExcellentFouls' => [
					'text' => 'excellent',
					'value' => 4,
				],
			],
		],
		'q3' => [
			'name' => 'Fair-Mindedness',
			'text' => 'Fair-Mindedness',
			'desc' => 'Examples: They apologized in situations where it was appropriate, informed teammates about wrong/unnecessary calls. Only called significant breaches.',
			'type' => 'radio',
			'options' => [
				'PoorFairMindedness' => [
					'text' => 'poor',
					'value' => 0,
				],
				'NotGoodFairMindedness' => [
					'text' => 'not good',
					'value' => 1,
				],
				'GoodFairMindedness' => [
					'text' => 'good',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodFairMindedness' => [
					'text' => 'very good',
					'value' => 3,
				],
				'ExcellentFairMindedness' => [
					'text' => 'excellent',
					'value' => 4,
				],
			],
		],
		'q4' => [
			'name' => 'Attitude',
			'text' => 'Positive Attitude and Self-Control',
			'desc' => 'Examples: They were polite. They played with appropriate intensity irrespective of the score. They left an overall positive impression during and after the game.',
			'type' => 'radio',
			'options' => [
				'PoorAttitude' => [
					'text' => 'poor',
					'value' => 0,
				],
				'NotGoodAttitude' => [
					'text' => 'not good',
					'value' => 1,
				],
				'GoodAttitude' => [
					'text' => 'good',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodAttitude' => [
					'text' => 'very good',
					'value' => 3,
				],
				'ExcellentAttitude' => [
					'text' => 'excellent',
					'value' => 4,
				],
			],
		],
		'q5' => [
			'name' => 'Communication',
			'text' => 'Communication',
			'desc' => 'Examples: They communicated respectfully. They listened. They kept to discussion time limits.',
			'type' => 'radio',
			'options' => [
				'PoorCommunication' => [
					'text' => 'poor',
					'value' => 0,
				],
				'NotGoodCommunication' => [
					'text' => 'not good',
					'value' => 1,
				],
				'GoodCommunication' => [
					'text' => 'good',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodCommunication' => [
					'text' => 'very good',
					'value' => 3,
				],
				'ExcellentCommunication' => [
					'text' => 'excellent',
					'value' => 4,
				],
			],
		],
		'q6' => [
			'name' => 'Inclusivity',
			'text' => 'Inclusivity',
			'desc' => 'Everyone had a genuine and equal opportunity to participate in varied roles, regardless of gender, e.g, getting the disc on open cuts, picking up the disc on turnovers, captaining, etc.',
			'type' => 'radio',
			'options' => [
				'PoorInclusivity' => [
					'text' => 'poor',
					'value' => 0,
				],
				'NotGoodInclusivity' => [
					'text' => 'not good',
					'value' => 1,
				],
				'GoodInclusivity' => [
					'text' => 'good',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodInclusivity' => [
					'text' => 'very good',
					'value' => 3,
				],
				'ExcellentInclusivity' => [
					'text' => 'excellent',
					'value' => 4,
				],
			],
		],
		'comments' => [
			'name' => 'Comments',
			'text' => 'If you have selected Poor in any category, please explain in few words what happened. Negative feedback will be passed to the teams in the appropriate manner.',
			'type' => 'textarea',
			'restricted' => true,
		],
		'highlights' => [
			'name' => 'Highlights',
			'text' => 'If you have selected Excellent in any category, please explain in few words what happened. Compliments will be passed to the teams in the appropriate manner.',
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
		$this->description = __('The 2014 WFDF standard spirit survey, tweaked with "inclusivity" question.');
		parent::__construct();
	}

	public function addValidation(Validator $validator, League $league) {
		return parent::addValidation($validator, $league)
			->range('q1', [0, 4], __('Select one of the given options.'))
			->requirePresence('q1', 'create')
			->notEmpty('q1')

			->range('q2', [0, 4], __('Select one of the given options.'))
			->requirePresence('q2', 'create')
			->notEmpty('q2')

			->range('q3', [0, 4], __('Select one of the given options.'))
			->requirePresence('q3', 'create')
			->notEmpty('q3')

			->range('q4', [0, 4], __('Select one of the given options.'))
			->requirePresence('q4', 'create')
			->notEmpty('q4')

			->range('q5', [0, 4], __('Select one of the given options.'))
			->requirePresence('q5', 'create')
			->notEmpty('q5')

			->range('q6', [0, 4], __('Select one of the given options.'))
			->requirePresence('q6', 'create')
			->notEmpty('q6')

			;
	}

	public function expected($entity = true) {
		$expected = [
			'entered_sotg' => 18,
			'score_entry_penalty' => 0,
			'q1' => 3,
			'q2' => 3,
			'q3' => 3,
			'q4' => 3,
			'q5' => 3,
			'q6' => 3,
		];
		if ($entity) {
			return new SpiritEntry($expected);
		} else {
			return $expected;
		}
	}
}
