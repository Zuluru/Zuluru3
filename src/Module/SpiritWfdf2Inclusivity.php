<?php
/**
 * Derived class for implementing functionality for spirit scoring by the updated WFDF questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;
use App\Model\Entity\SpiritEntry;

class SpiritWfdf2Inclusivity extends Spirit {
	public $ratios = [
		'perfect' => 0.75,
		'ok' => 0.5,
		'caution' => 0.25,
		'not_ok' => 0,
	];

	public function __construct() {
		$this->description = __('The 2014 WFDF standard spirit survey, tweaked with "inclusivity" question.');

		$this->questions = [
			'q1' => [
				'name' => __('Rules Knowledge'),
				'text' => __('Rules Knowledge and Use'),
				'desc' => __('Examples: They did not purposefully misinterpret the rules. They kept to time limits. When they didn\'t know the rules they showed a real willingness to learn.'),
				'type' => 'radio',
				'options' => [
					'PoorRules' => [
						'text' => __('Poor'),
						'value' => 0,
					],
					'NotGoodRules' => [
						'text' => __('Not good'),
						'value' => 1,
					],
					'GoodRules' => [
						'text' => __('Good'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodRules' => [
						'text' => __('Very good'),
						'value' => 3,
					],
					'ExcellentRules' => [
						'text' => __('Excellent'),
						'value' => 4,
					],
				],
			],
			'q2' => [
				'name' => __('Fouls'),
				'text' => __('Fouls and Body Contact'),
				'desc' => __('Examples: They avoided fouling, contact, and dangerous plays.'),
				'type' => 'radio',
				'options' => [
					'PoorFouls' => [
						'text' => __('Poor'),
						'value' => 0,
					],
					'NotGoodFouls' => [
						'text' => __('Not good'),
						'value' => 1,
					],
					'GoodFouls' => [
						'text' => __('Good'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodFouls' => [
						'text' => __('Very good'),
						'value' => 3,
					],
					'ExcellentFouls' => [
						'text' => __('Excellent'),
						'value' => 4,
					],
				],
			],
			'q3' => [
				'name' => __('Fair-Mindedness'),
				'text' => __('Fair-Mindedness'),
				'desc' => __('Examples: They apologized in situations where it was appropriate, informed teammates about wrong/unnecessary calls. Only called significant breaches.'),
				'type' => 'radio',
				'options' => [
					'PoorFairMindedness' => [
						'text' => __('Poor'),
						'value' => 0,
					],
					'NotGoodFairMindedness' => [
						'text' => __('Not good'),
						'value' => 1,
					],
					'GoodFairMindedness' => [
						'text' => __('Good'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodFairMindedness' => [
						'text' => __('Very good'),
						'value' => 3,
					],
					'ExcellentFairMindedness' => [
						'text' => __('Excellent'),
						'value' => 4,
					],
				],
			],
			'q4' => [
				'name' => __('Attitude'),
				'text' => __('Positive Attitude and Self-Control'),
				'desc' => __('Examples: They were polite. They played with appropriate intensity irrespective of the score. They left an overall positive impression during and after the game.'),
				'type' => 'radio',
				'options' => [
					'PoorAttitude' => [
						'text' => __('Poor'),
						'value' => 0,
					],
					'NotGoodAttitude' => [
						'text' => __('Not good'),
						'value' => 1,
					],
					'GoodAttitude' => [
						'text' => __('Good'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodAttitude' => [
						'text' => __('Very good'),
						'value' => 3,
					],
					'ExcellentAttitude' => [
						'text' => __('Excellent'),
						'value' => 4,
					],
				],
			],
			'q5' => [
				'name' => __('Communication'),
				'text' => __('Communication'),
				'desc' => __('Examples: They communicated respectfully. They listened. They kept to discussion time limits.'),
				'type' => 'radio',
				'options' => [
					'PoorCommunication' => [
						'text' => __('Poor'),
						'value' => 0,
					],
					'NotGoodCommunication' => [
						'text' => __('Not good'),
						'value' => 1,
					],
					'GoodCommunication' => [
						'text' => __('Good'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodCommunication' => [
						'text' => __('Very good'),
						'value' => 3,
					],
					'ExcellentCommunication' => [
						'text' => __('Excellent'),
						'value' => 4,
					],
				],
			],
			'q6' => [
				'name' => __('Inclusivity'),
				'text' => __('Inclusivity'),
				'desc' => __('Everyone had a genuine and equal opportunity to participate in varied roles, regardless of gender, e.g, getting the disc on open cuts, picking up the disc on turnovers, captaining, etc.'),
				'type' => 'radio',
				'options' => [
					'PoorInclusivity' => [
						'text' => __('Poor'),
						'value' => 0,
					],
					'NotGoodInclusivity' => [
						'text' => __('Not good'),
						'value' => 1,
					],
					'GoodInclusivity' => [
						'text' => __('Good'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodInclusivity' => [
						'text' => __('Very good'),
						'value' => 3,
					],
					'ExcellentInclusivity' => [
						'text' => __('Excellent'),
						'value' => 4,
					],
				],
			],
			'comments' => [
				'name' => __('Comments'),
				'text' => __('If you have selected Poor in any category, please explain in few words what happened. Negative feedback will be passed to the teams in the appropriate manner.'),
				'type' => 'textarea',
				'restricted' => true,
			],
			'highlights' => [
				'name' => __('Highlights'),
				'text' => __('If you have selected Excellent in any category, please explain in few words what happened. Compliments will be passed to the teams in the appropriate manner.'),
				'type' => 'textarea',
				'restricted' => true,
			],
		];

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
