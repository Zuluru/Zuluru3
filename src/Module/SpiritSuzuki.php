<?php
/**
 * Derived class for implementing functionality for spirit scoring by Sushi Suzuki's Alternate questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;
use App\Model\Entity\SpiritEntry;

class SpiritSuzuki extends Spirit {
	public $ratios = [
		'perfect' => 0.75,
		'ok' => 0.5,
		'caution' => 0.25,
		'not_ok' => 0,
	];

	public function __construct() {
		$this->description = __('Sushi Suzuki\'s {0}, intended "for tournaments where the official WFDF SOTG score sheet may feel \"too serious.\""',
			'<a href="http://www.sushi-suzuki.com/sushilog/2014/12/the-alternate-spirit-of-the-game-score-sheet/">' . __('alternate spirit survey') . '</a>');

		$this->questions = [
			'q1' => [
				'name' => __('Fair Play'),
				'text' => __('Fair Play'),
				'desc' => __('The team tried to win fair and square, no cheap calls or taking advantage of the rules.'),
				'type' => 'radio',
				'options' => [
					'PoorFairPlay' => [
						'text' => __('0: Full of cheaters'),
						'value' => 0,
					],
					'NotGoodFairPlay' => [
						'text' => __('1'),
						'value' => 1,
					],
					'GoodFairPlay' => [
						'text' => __('2'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodFairPlay' => [
						'text' => __('3'),
						'value' => 3,
					],
					'ExcellentFairPlay' => [
						'text' => __('4: Full of angels'),
						'value' => 4,
					],
				],
			],
			'q2' => [
				'name' => __('Intensity'),
				'text' => __('Intensity'),
				'desc' => __('Full sprints, hard cuts, layouts, etc. How intense was the team?'),
				'type' => 'radio',
				'options' => [
					'PoorIntensity' => [
						'text' => __('0: Sloth-like'),
						'value' => 0,
					],
					'NotGoodIntensity' => [
						'text' => __('1'),
						'value' => 1,
					],
					'GoodIntensity' => [
						'text' => __('2'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodIntensity' => [
						'text' => __('3'),
						'value' => 3,
					],
					'ExcellentIntensity' => [
						'text' => __('4: Like the blinding sun'),
						'value' => 4,
					],
				],
			],
			'q3' => [
				'name' => __('Daringness'),
				'text' => __('Daringness'),
				'desc' => __('Hucks, hammers, scoobers, "Wow! did he/she really do that?"'),
				'type' => 'radio',
				'options' => [
					'PoorDaringness' => [
						'text' => __('0: Snore bore'),
						'value' => 0,
					],
					'NotGoodDaringness' => [
						'text' => __('1'),
						'value' => 1,
					],
					'GoodDaringness' => [
						'text' => __('2'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodDaringness' => [
						'text' => __('3'),
						'value' => 3,
					],
					'ExcellentDaringness' => [
						'text' => __('4: OMG WTF'),
						'value' => 4,
					],
				],
			],
			'q4' => [
				'name' => __('Spirit Speech / Sense of Humor'),
				'text' => __(' Spirit Speech'),
				'desc' => __('How much laughter did the other team induce during the match and in the spirit speech?'),
				'type' => 'radio',
				'options' => [
					'PoorSpiritSpeech' => [
						'text' => __('0: Somber as a funeral'),
						'value' => 0,
					],
					'NotGoodSpiritSpeech' => [
						'text' => __('1'),
						'value' => 1,
					],
					'GoodSpiritSpeech' => [
						'text' => __('2'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodSpiritSpeech' => [
						'text' => __('3'),
						'value' => 3,
					],
					'ExcellentSpiritSpeech' => [
						'text' => __('4: Better than many comedians'),
						'value' => 4,
					],
				],
			],
			'q5' => [
				'name' => __('Fun'),
				'text' => __('Fun'),
				'desc' => __('How entertaining was the match? Would you do this again?'),
				'type' => 'radio',
				'options' => [
					'PoorFun' => [
						'text' => __('0: Never ever again'),
						'value' => 0,
					],
					'NotGoodFun' => [
						'text' => __('1'),
						'value' => 1,
					],
					'GoodFun' => [
						'text' => __('2'),
						'value' => 2,
						'default' => true,
					],
					'VeryGoodFun' => [
						'text' => __('3'),
						'value' => 3,
					],
					'ExcellentFun' => [
						'text' => __('4: I wish every game was like this'),
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
