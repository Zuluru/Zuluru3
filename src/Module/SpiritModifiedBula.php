<?php
/**
 * Derived class for implementing functionality for spirit scoring by the modified BULA questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;

class SpiritModifiedBula extends Spirit {
	public $ratios = [
		'perfect' => 0.75,
		'ok' => 0.5,
		'caution' => 0.25,
		'not_ok' => 0,
	];

	public function __construct() {
		$this->description = __('The Modified BULA spirit survey was developed by Mile Zero Ultimate, based on the original BULA spirit survey. (BULA now uses the WFDF standard spirit survey, which was also based on BULA\'s original.)');

		$this->questions = [
			'q1' => [
				'name' => __('Respect'),
				'text' => __('They communicated objectively and without aggressive language. They were willing to believe calls were made in good faith.'),
				'type' => 'radio',
				'options' => [
					'2' => [
						'text' => __('They did more than expected in this category'),
						'value' => 2,
					],
					'1' => [
						'text' => __('They did average in this category'),
						'value' => 1,
					],
					'0' => [
						'text' => __('They did poorly in this category'),
						'value' => 0,
					],
				],
			],
			'q2' => [
				'name' => __('Fair-Mindedness'),
				'text' => __('Players pointed out their own fouls. They corrected their own player calls. In an important situation they admitted that the opponent was probably right.'),
				'type' => 'radio',
				'options' => [
					'2' => [
						'text' => __('They did more than expected in this category'),
						'value' => 2,
					],
					'1' => [
						'text' => __('They did average in this category'),
						'value' => 1,
					],
					'0' => [
						'text' => __('They did poorly in this category'),
						'value' => 0,
					],
				],
			],
			'q3' => [
				'name' => __('Positive Attitude'),
				'text' => __('They introduced themselves to the opponent. They complimented the opponent for good plays. Left a positive impression in an after-the-game Spirit Circle.'),
				'type' => 'radio',
				'options' => [
					'2' => [
						'text' => __('They did more than expected in this category'),
						'value' => 2,
					],
					'1' => [
						'text' => __('They did average in this category'),
						'value' => 1,
					],
					'0' => [
						'text' => __('They did poorly in this category'),
						'value' => 0,
					],
				],
			],
			'q4' => [
				'name' => __('Emotional Management'),
				'text' => __('Their reaction towards disagreements, successes, and mistakes was appropriately mature.'),
				'type' => 'radio',
				'options' => [
					'2' => [
						'text' => __('They did more than expected in this category'),
						'value' => 2,
					],
					'1' => [
						'text' => __('They did average in this category'),
						'value' => 1,
					],
					'0' => [
						'text' => __('They did poorly in this category'),
						'value' => 0,
					],
				],
			],
			'q5' => [
				'name' => __('Avoiding Body Contact'),
				'text' => __('They were aware of other player\'s body location and movement and avoided dangerous plays.'),
				'type' => 'radio',
				'options' => [
					'2' => [
						'text' => __('They did more than expected in this category'),
						'value' => 2,
					],
					'1' => [
						'text' => __('They did average in this category'),
						'value' => 1,
					],
					'0' => [
						'text' => __('They did poorly in this category'),
						'value' => 0,
					],
				],
			],
			'q6' => [
				'name' => __('Avoid Violations and Fouls'),
				'text' => __('They tried to avoid fouls and violations. Their marks were legal.'),
				'type' => 'radio',
				'options' => [
					'2' => [
						'text' => __('They did more than expected in this category'),
						'value' => 2,
					],
					'1' => [
						'text' => __('They did average in this category'),
						'value' => 1,
					],
					'0' => [
						'text' => __('They did poorly in this category'),
						'value' => 0,
					],
				],
			],
			'q7' => [
				'name' => __('Knowledge of the Rules'),
				'text' => __('They knew the rules and/or had the willingness to learn and teach them. They did not make unjustified calls.'),
				'type' => 'radio',
				'options' => [
					'2' => [
						'text' => __('They did more than expected in this category'),
						'value' => 2,
					],
					'1' => [
						'text' => __('They did average in this category'),
						'value' => 1,
					],
					'0' => [
						'text' => __('They did poorly in this category'),
						'value' => 0,
					],
				],
			],
			'q8' => [
				'name' => __('Their Spirit compared to ours'),
				'text' => __('How was their spirit compared to our own spirit?'),
				'type' => 'radio',
				'options' => [
					'2' => [
						'text' => __('They did more than expected in this category'),
						'value' => 2,
					],
					'1' => [
						'text' => __('They did average in this category'),
						'value' => 1,
					],
					'0' => [
						'text' => __('They did poorly in this category'),
						'value' => 0,
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

}
