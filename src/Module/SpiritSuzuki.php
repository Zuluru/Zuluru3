<?php

/**
 * Derived class for implementing functionality for spirit scoring by Sushi Suzuki's Alternate questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;
use App\Model\Entity\SpiritEntry;

class SpiritSuzuki extends Spirit {
	public $questions = [
		'q1' => [
			'name' => 'Fair Play',
			'text' => 'Fair Play',
			'desc' => 'The team tried to win fair and square, no cheap calls or taking advantage of the rules.',
			'type' => 'radio',
			'options' => [
				'PoorFairPlay' => [
					'text' => '0: Full of cheaters',
					'value' => 0,
				],
				'NotGoodFairPlay' => [
					'text' => '1',
					'value' => 1,
				],
				'GoodFairPlay' => [
					'text' => '2',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodFairPlay' => [
					'text' => '3',
					'value' => 3,
				],
				'ExcellentFairPlay' => [
					'text' => '4: Full of angels',
					'value' => 4,
				],
			],
		],
		'q2' => [
			'name' => 'Intensity',
			'text' => 'Intensity',
			'desc' => 'Full sprints, hard cuts, layouts, etc. How intense was the team?',
			'type' => 'radio',
			'options' => [
				'PoorIntensity' => [
					'text' => '0: Sloth-like',
					'value' => 0,
				],
				'NotGoodIntensity' => [
					'text' => '1',
					'value' => 1,
				],
				'GoodIntensity' => [
					'text' => '2',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodIntensity' => [
					'text' => '3',
					'value' => 3,
				],
				'ExcellentIntensity' => [
					'text' => '4: Like the blinding sun',
					'value' => 4,
				],
			],
		],
		'q3' => [
			'name' => 'Daringness',
			'text' => 'Daringness',
			'desc' => 'Hucks, hammers, scoobers, "Wow! did he/she really do that?"',
			'type' => 'radio',
			'options' => [
				'PoorDaringness' => [
					'text' => '0: Snore bore',
					'value' => 0,
				],
				'NotGoodDaringness' => [
					'text' => '1',
					'value' => 1,
				],
				'GoodDaringness' => [
					'text' => '2',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodDaringness' => [
					'text' => '3',
					'value' => 3,
				],
				'ExcellentDaringness' => [
					'text' => '4: OMG WTF',
					'value' => 4,
				],
			],
		],
		'q4' => [
			'name' => 'Spirit Speech / Sense of Humor',
			'text' => ' Spirit Speech',
			'desc' => 'How much laughter did the other team induce during the match and in the spirit speech?',
			'type' => 'radio',
			'options' => [
				'PoorSpiritSpeech' => [
					'text' => '0: Somber as a funeral',
					'value' => 0,
				],
				'NotGoodSpiritSpeech' => [
					'text' => '1',
					'value' => 1,
				],
				'GoodSpiritSpeech' => [
					'text' => '2',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodSpiritSpeech' => [
					'text' => '3',
					'value' => 3,
				],
				'ExcellentSpiritSpeech' => [
					'text' => '4: Better than many comedians',
					'value' => 4,
				],
			],
		],
		'q5' => [
			'name' => 'Fun',
			'text' => 'Fun',
			'desc' => 'How entertaining was the match? Would you do this again?',
			'type' => 'radio',
			'options' => [
				'PoorFun' => [
					'text' => '0: Never ever again',
					'value' => 0,
				],
				'NotGoodFun' => [
					'text' => '1',
					'value' => 1,
				],
				'GoodFun' => [
					'text' => '2',
					'value' => 2,
					'default' => true,
				],
				'VeryGoodFun' => [
					'text' => '3',
					'value' => 3,
				],
				'ExcellentFun' => [
					'text' => '4: I wish every game was like this',
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
		$this->description = __('Sushi Suzuki\'s {0}, intended "for tournaments where the official WFDF SOTG score sheet may feel \"too serious.\"',
			'<a href="http://www.sushi-suzuki.com/sushilog/2014/12/the-alternate-spirit-of-the-game-score-sheet/">' . __('alternate spirit survey') . '</a>');
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
