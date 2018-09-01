<?php

/**
 * Derived class for implementing functionality for spirit scoring by the WFDF questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;
use App\Model\Entity\SpiritEntry;

class SpiritWfdf extends Spirit {
	public $questions = [
		'q1' => [
			'name' => 'Rules Knowledge',
			'text' => 'Rules Knowledge and Use',
			'desc' => 'For example: They did not make unjustified calls. They did not purposefully misinterpret the rules. They kept to time limits. They were willing to teach and/or learn the rules.',
			'type' => 'radio',
			'options' => [
				'PoorRules' => [
					'text' => 'poor',
					'value' => 0,
				],
				'NotGoodRules' => [
					'text' => 'not so good',
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
			'desc' => 'For example: They avoided fouling, contact, and dangerous plays.',
			'type' => 'radio',
			'options' => [
				'PoorFouls' => [
					'text' => 'poor',
					'value' => 0,
				],
				'NotGoodFouls' => [
					'text' => 'not so good',
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
			'desc' => 'For example: They apologized for their own fouls. They informed teammates when they made wrong or unnecessary calls. They were willing to admit that we were right and retracted their call.',
			'type' => 'radio',
			'options' => [
				'PoorFairMindedness' => [
					'text' => 'poor',
					'value' => 0,
				],
				'NotGoodFairMindedness' => [
					'text' => 'not so good',
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
			'desc' => 'For example: They introduced themselves. They communicated without derogatory or aggressive language. They complimented us on our good plays. They left an overall positive impression during and after the game, e.g. during the Spirit circle.',
			'type' => 'radio',
			'options' => [
				'PoorAttitude' => [
					'text' => 'poor',
					'value' => 0,
				],
				'NotGoodAttitude' => [
					'text' => 'not so good',
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
			'name' => 'Comparison',
			'text' => 'Our Spirit compared to theirs',
			'desc' => 'How did our team compare to theirs with regards to rules knowledge, body contact, fair-mindedness, positive attitude and self-control?',
			'type' => 'radio',
			'options' => [
				'PoorSpirit' => [
					'text' => 'Our spirit was much better',
					'value' => 0,
				],
				'NotGoodSpirit' => [
					'text' => 'Our spirit was slightly better',
					'value' => 1,
				],
				'GoodSpirit' => [
					'text' => 'Our spirit was the same',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodSpirit' => [
					'text' => 'Our spirit was slightly worse',
					'value' => 3,
				],
				'ExcellentSpirit' => [
					'text' => 'Our spirit was much worse',
					'value' => 4,
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
		$this->description = __('The WFDF standard spirit survey.');
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
