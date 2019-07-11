<?php
/**
 * Derived class for implementing functionality for spirit scoring by the modified OCUA team questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;

class SpiritOcuaTeam extends Spirit {
	public $ratios = [
		'perfect' => 0.9,
		'ok' => 0.6,
		'caution' => 0.4,
		'not_ok' => 0,
	];

	public function __construct() {
		$this->description = __('The modified Leaguerunner spirit survey, developed by the Ottawa Carleton Ultimate Association. Compared to the original Leaguerunner spirit survey, this one emphasizes enjoyment by adding more options there, while decreasing the number of timeliness options.');

		$this->questions = [
			'q1' => [
				'name' => __('Timeliness'),
				'text' => __('Our opponents\' timeliness:'),
				'type' => 'radio',
				'options' => [
					'MetExpectations' => [
						'text' => __('Met expectations'),
						'value' => 1,
						'default' => true,
					],
					'DidNotMeet' => [
						'text' => __('Did not meet expectations'),
						'value' => 0,
					],
				],
			],
			'q2' => [
				'name' => __('Rules Knowledge'),
				'text' => __('Our opponents\' rules knowledge was:'),
				'type' => 'radio',
				'options' => [
					'ExceptionalRules' => [
						'text' => __('Exceptional'),
						'value' => 3,
					],
					'GoodRules' => [
						'text' => __('Good'),
						'value' => 2,
						'default' => true,
					],
					'BelowAvgRules' => [
						'text' => __('Below average'),
						'value' => 1,
					],
					'BadRules' => [
						'text' => __('Bad'),
						'value' => 0,
					],
				],
			],
			'q3' => [
				'name' => __('Sportsmanship'),
				'text' => __('Our opponents\' sportsmanship was:'),
				'type' => 'radio',
				'options' => [
					'ExceptionalSportsmanship' => [
						'text' => __('Exceptional'),
						'value' => 3,
					],
					'GoodSportsmanship' => [
						'text' => __('Good'),
						'value' => 2,
						'default' => true,
					],
					'BelowAvgSportsmanship' => [
						'text' => __('Below average'),
						'value' => 1,
					],
					'PoorSportsmanship' => [
						'text' => __('Poor'),
						'value' => 0,
					],
				],
			],
			'q4' => [
				'name' => __('Overall'),
				'text' => __('Ignoring the score and based on the opponents\' spirit of the game, what was your overall assessment of the game?'),
				'type' => 'radio',
				'options' => [
					'Exceptional' => [
						'text' => __('This was an exceptionally great game'),
						'value' => 3,
					],
					'Enjoyable' => [
						'text' => __('This was an enjoyable game'),
						'value' => 2,
						'default' => true,
					],
					'Mediocre' => [
						'text' => __('This was a mediocre game'),
						'value' => 1,
					],
					'VeryBad' => [
						'text' => __('This was a very bad game'),
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
