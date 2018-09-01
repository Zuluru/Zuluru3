<?php

/**
 * Derived class for implementing functionality for spirit scoring by the modified OCUA team questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;

class SpiritOcuaTeam extends Spirit {
	public $questions = [
		'q1' => [
			'name' => 'Timeliness',
			'text' => 'Our opponents\' timeliness:',
			'type' => 'radio',
			'options' => [
				'MetExpectations' => [
					'text' => 'met expectations',
					'value' => 1,
					'default' => true,
				],
				'DidNotMeet' => [
					'text' => 'did not meet expectations',
					'value' => 0,
				],
			],
		],
		'q2' => [
			'name' => 'Rules Knowledge',
			'text' => 'Our opponents\' rules knowledge was:',
			'type' => 'radio',
			'options' => [
				'ExceptionalRules' => [
					'text' => 'exceptional',
					'value' => 3,
				],
				'GoodRules' => [
					'text' => 'good',
					'value' => 2,
					'default' => true,
				],
				'BelowAvgRules' => [
					'text' => 'below average',
					'value' => 1,
				],
				'BadRules' => [
					'text' => 'bad',
					'value' => 0,
				],
			],
		],
		'q3' => [
			'name' => 'Sportsmanship',
			'text' => 'Our opponents\' sportsmanship was:',
			'type' => 'radio',
			'options' => [
				'ExceptionalSportsmanship' => [
					'text' => 'exceptional',
					'value' => 3,
				],
				'GoodSportsmanship' => [
					'text' => 'good',
					'value' => 2,
					'default' => true,
				],
				'BelowAvgSportsmanship' => [
					'text' => 'below average',
					'value' => 1,
				],
				'PoorSportsmanship' => [
					'text' => 'poor',
					'value' => 0,
				],
			],
		],
		'q4' => [
			'name' => 'Overall',
			'text' => 'Ignoring the score and based on the opponents\' spirit of the game, what was your overall assessment of the game?',
			'type' => 'radio',
			'options' => [
				'Exceptional' => [
					'text' => 'This was an exceptionally great game',
					'value' => 3,
				],
				'Enjoyable' => [
					'text' => 'This was an enjoyable game',
					'value' => 2,
					'default' => true,
				],
				'Mediocre' => [
					'text' => 'This was a mediocre game',
					'value' => 1,
				],
				'VeryBad' => [
					'text' => 'This was a very bad game',
					'value' => 0,
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
		'perfect' => 0.9,
		'ok' => 0.6,
		'caution' => 0.4,
		'not_ok' => 0,
	];

	public function __construct() {
		$this->description = __('The modified Leaguerunner spirit survey, developed by the Ottawa Carleton Ultimate Association. Compared to the original Leaguerunner spirit survey, this one emphasizes enjoyment by adding more options there, while decreasing the number of timeliness options.');
		parent::__construct();
	}

	public function addValidation(Validator $validator, League $league) {
		return parent::addValidation($validator, $league)
			->range('q1', [0, 1], __('Select one of the given options.'))
			->requirePresence('q1', 'create')
			->notEmpty('q1')

			->range('q2', [0, 3], __('Select one of the given options.'))
			->requirePresence('q2', 'create')
			->notEmpty('q2')

			->range('q3', [0, 3], __('Select one of the given options.'))
			->requirePresence('q3', 'create')
			->notEmpty('q3')

			->range('q4', [0, 3], __('Select one of the given options.'))
			->requirePresence('q4', 'create')
			->notEmpty('q4')

			;
	}

}
