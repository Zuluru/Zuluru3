<?php
/**
 * Derived class for implementing functionality for spirit scoring by the WFDF questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;
use App\Model\Entity\SpiritEntry;

class SpiritWfdf extends Spirit {
	public $ratios = [
		'perfect' => 0.75,
		'ok' => 0.5,
		'caution' => 0.25,
		'not_ok' => 0,
	];

	public function __construct() {
		$this->description = __('The WFDF standard spirit survey.');

		$this->questions = [
			'q1' => [
				'name' => __('Rules Knowledge'),
				'text' => __('Rules Knowledge and Use'),
				'desc' => __('For example: They did not make unjustified calls. They did not purposefully misinterpret the rules. They kept to time limits. They were willing to teach and/or learn the rules.'),
				'type' => 'radio',
				'options' => [
					'PoorRules' => [
						'text' => __('Poor'),
						'value' => 0,
					],
					'NotGoodRules' => [
						'text' => __('Not so good'),
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
				'desc' => __('For example: They avoided fouling, contact, and dangerous plays.'),
				'type' => 'radio',
				'options' => [
					'PoorFouls' => [
						'text' => __('Poor'),
						'value' => 0,
					],
					'NotGoodFouls' => [
						'text' => __('Not so good'),
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
				'desc' => __('For example: They apologized for their own fouls. They informed teammates when they made wrong or unnecessary calls. They were willing to admit that we were right and retracted their call.'),
				'type' => 'radio',
				'options' => [
					'PoorFairMindedness' => [
						'text' => __('Poor'),
						'value' => 0,
					],
					'NotGoodFairMindedness' => [
						'text' => __('Not so good'),
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
				'desc' => __('For example: They introduced themselves. They communicated without derogatory or aggressive language. They complimented us on our good plays. They left an overall positive impression during and after the game, e.g. during the Spirit circle.'),
				'type' => 'radio',
				'options' => [
					'PoorAttitude' => [
						'text' => __('Poor'),
						'value' => 0,
					],
					'NotGoodAttitude' => [
						'text' => __('Not so good'),
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
				'name' => __('Comparison'),
				'text' => __('Our Spirit compared to theirs'),
				'desc' => __('How did our team compare to theirs with regards to rules knowledge, body contact, fair-mindedness, positive attitude and self-control?'),
				'type' => 'radio',
				'options' => [
					'PoorSpirit' => [
						'text' => __('Our spirit was much better'),
						'value' => 0,
					],
					'NotGoodSpirit' => [
						'text' => __('Our spirit was slightly better'),
						'value' => 1,
					],
					'GoodSpirit' => [
						'text' => __('Our spirit was the same'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodSpirit' => [
						'text' => __('Our spirit was slightly worse'),
						'value' => 3,
					],
					'ExcellentSpirit' => [
						'text' => __('Our spirit was much worse'),
						'value' => 4,
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
			->range('q1', [0, 4], __('Select one of the given options.'))
			->requirePresence('q1', 'create')
			->notEmptyString('q1')

			->range('q2', [0, 4], __('Select one of the given options.'))
			->requirePresence('q2', 'create')
			->notEmptyString('q2')

			->range('q3', [0, 4], __('Select one of the given options.'))
			->requirePresence('q3', 'create')
			->notEmptyString('q3')

			->range('q4', [0, 4], __('Select one of the given options.'))
			->requirePresence('q4', 'create')
			->notEmptyString('q4')

			->range('q5', [0, 4], __('Select one of the given options.'))
			->requirePresence('q5', 'create')
			->notEmptyString('q5')

			;
	}

	public function expected($entity = true) {
		$expected = [
			'entered_sotg' => 15,
			'score_entry_penalty' => 0,
			'q1' => 3,
			'q2' => 3,
			'q3' => 3,
			'q4' => 3,
			'q5' => 3,
		];
		if ($entity) {
			return new SpiritEntry($expected);
		} else {
			return $expected;
		}
	}
}
