<?php

/**
 * Derived class for implementing functionality for spirit scoring by the original team questionnaire.
 */
namespace App\Module;

use Cake\Validation\Validator;
use App\Model\Entity\League;

class SpiritTeam extends Spirit {
	public $questions = [
		'q1' => [
			'name' => 'Timeliness',
			'text' => 'Our opponents had a full line and were ready to play:',
			'type' => 'radio',
			'options' => [
				'OnTime' => [
					'text' => 'early, or at the official start time',
					'value' => 3,
					'default' => true,
				],
				'FiveOrLess' => [
					'text' => 'less than five minutes late',
					'value' => 2,
				],
				'LessThanTen' => [
					'text' => 'less than ten minutes late',
					'value' => 1,
				],
				'MoreThanTen' => [
					'text' => 'more than ten minutes late',
					'value' => 0,
				],
			],
		],
		'q2' => [
			'name' => 'Rules Knowledge',
			'text' => 'Our opponents\' rules knowledge was:',
			'type' => 'radio',
			'options' => [
				'ExcellentRules' => [
					'text' => 'excellent',
					'value' => 3,
					'default' => true,
				],
				'AcceptableRules' => [
					'text' => 'acceptable',
					'value' => 2,
				],
				'PoorRules' => [
					'text' => 'poor',
					'value' => 1,
				],
				'NonexistantRules' => [
					'text' => 'nonexistant',
					'value' => 0,
				],
			],
		],
		'q3' => [
			'name' => 'Sportsmanship',
			'text' => 'Our opponents\' sportsmanship was:',
			'type' => 'radio',
			'options' => [
				'ExcellentSportsmanship' => [
					'text' => 'excellent',
					'value' => 3,
					'default' => true,
				],
				'AcceptableSportsmanship' => [
					'text' => 'acceptable',
					'value' => 2,
				],
				'PoorSportsmanship' => [
					'text' => 'poor',
					'value' => 1,
				],
				'NonexistantSportsmanship' => [
					'text' => 'nonexistant',
					'value' => 0,
				],
			],
		],
		'q4' => [
			'name' => 'Enjoyment',
			'text' => 'Ignoring the score and based on the opponents\' spirit of the game, did your team enjoy this game?',
			'type' => 'radio',
			'options' => [
				'AllEnjoyed' => [
					'text' => 'all or most of my players did',
					'value' => 1,
					'default' => true,
				],
				'NoneEnjoyed' => [
					'text' => 'some or none of my players did',
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

	public function __construct() {
		$this->description = __('The original Leaguerunner spirit survey, developed by the Ottawa Carleton Ultimate Association.');
		parent::__construct();
	}

	public function addValidation(Validator $validator, League $league) {
		return parent::addValidation($validator, $league)
			->range('q1', [0, 3], __('Select one of the given options.'))
			->requirePresence('q1', 'create')
			->notEmpty('q1')

			->range('q2', [0, 3], __('Select one of the given options.'))
			->requirePresence('q2', 'create')
			->notEmpty('q2')

			->range('q3', [0, 3], __('Select one of the given options.'))
			->requirePresence('q3', 'create')
			->notEmpty('q3')

			->range('q4', [0, 1], __('Select one of the given options.'))
			->requirePresence('q4', 'create')
			->notEmpty('q4')

			;
	}

}
