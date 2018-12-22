<?php
/**
 * Base class for spirit scoring functionality.  This class defines default
 * no-op functions for all operations that any spirit system needs, as well
 * as providing some common utility functions that derived classes need.
 */
namespace App\Module;

use Cake\Core\Configure;
use Cake\Validation\Validator;
use App\Model\Entity\League;
use App\Model\Entity\SpiritEntry;

abstract class Spirit {
	/**
	 * Define the element to use for rendering various views
	 */
	public $render_element = 'basic';

	public $questions = [];

	/**
	 * Default mappings from ratios to symbol file names. These may
	 * be overridden by specific implementations.
	 */
	public $ratios = [
		'perfect' => 0.9,
		'ok' => 0.7,
		'caution' => 0.4,
		'not_ok' => 0,
	];

	public function __construct() {
		// Add the subs question, if enabled.
		// TODO: Find a better way to deal with this.
		if (Configure::read('scoring.subs')) {
			$this->questions['subs'] = array(
				'name' => 'Subs',
				'text' => __('Please list any players who subbed for your team in this game.'),
				'type' => 'text',
				'restricted' => true,
			);
		}
	}

	public function addValidation(Validator $validator, League $league) {
		if ($league->numeric_sotg) {
			$validator = $validator
				->range('entered_sotg', [0, $this->max()], __('Spirit scores must be in the range 0-{0}.', $this->max()))
				->requirePresence('entered_sotg', 'create')
				->notEmpty('entered_sotg');
		}

		return $validator;
	}

	/**
	 * Calculate the assigned spirit based on answers to the questions.
	 * This default implementation just adds up the scores for each question.
	 *
	 * @param mixed $entry The record with answers
	 * @return mixed The assigned spirit calculated
	 *
	 */
	public function calculate($entry) {
		$score = 0;
		foreach ($this->questions as $key => $question) {
			if (!empty($question['options'])) {
				$score += $entry->$key;
			}
		}
		$score += $entry->score_entry_penalty;
		return $score;
	}

	/**
	 * Return an array with expected scores
	 */
	public function expected($entity = true) {
		$expected = [
			'entered_sotg' => $this->maxs(),
			'score_entry_penalty' => 0,
		];
		foreach ($this->questions as $key => $question) {
			if ($question['type'] == 'radio') {
				$expected[$key] = $this->maxq($key);
			}
		}
		if ($entity) {
			return new SpiritEntry($expected);
		} else {
			return $expected;
		}
	}

	/**
	 * Return the max value for a question, or the entire survey
	 *
	 * @param mixed $q Question to check, or null for the entire survey
	 * @return mixed Maximum value
	 */
	public function max($q = null) {
		if ($q == 'score_entry_penalty') {
			return - Configure::read('scoring.missing_score_spirit_penalty');
		} else if (array_key_exists($q, $this->questions))
			return $this->maxq($q);
		else
			return $this->maxs();
	}

	/**
	 * Return the maximum possible spirit score for the entire survey.
	 * This default implementation just adds up the max scores for each question.
	 *
	 * @return mixed The maximum possible spirit score for the entire survey
	 */
	public function maxs() {
		static $max = null;
		if ($max === null) {
			$max = 0;
			foreach ($this->questions as $key => $question) {
				if (!empty($question['options'])) {
					$max += $this->maxq($key);
				}
			}
		}
		return $max;
	}

	/**
	 * Return the maximum possible spirit score for a question.
	 *
	 * @param mixed $q Question to check
	 * @return mixed Maximum value
	 */
	public function maxq($q) {
		static $max = [];
		if (!array_key_exists($q, $max)) {
			$question = $this->questions[$q];
			$max[$q] = 0;
			if (!empty($question['options'])) {
				foreach ($question['options'] as $option) {
					$max[$q] = max($max[$q], $option['value']);
				}
			}
		}
		return $max[$q];
	}

	/**
	 * Return an array with scores for a defaulted game
	 */
	public function defaulted($entity = true) {
		$default = [
			'entered_sotg' => $this->mins(),
			'score_entry_penalty' => 0,
		];
		foreach ($this->questions as $key => $question) {
			if ($question['type'] == 'radio') {
				$default[$key] = $this->minq($key);
			}
		}
		if ($entity) {
			return new SpiritEntry($default);
		} else {
			return $default;
		}
	}

	/**
	 * Return the min value for a question, or the entire survey
	 *
	 * @param mixed $q Question to check, or null for the entire survey
	 * @return mixed Minimum value
	 */
	public function min($q = null) {
		if (array_key_exists($q, $this->questions)) {
			return $this->minq($q);
		} else {
			return $this->mins();
		}
	}

	/**
	 * Return the minimum possible spirit score for the entire survey.
	 * This default implementation just adds up the min scores for each question.
	 *
	 * @return mixed The minimum possible spirit score for the entire survey
	 */
	public function mins() {
		static $min = null;
		if ($min === null) {
			$min = 0;
			foreach ($this->questions as $key => $question) {
				$min += $this->minq($key);
			}
		}
		return $min;
	}

	/**
	 * Return the minimum possible spirit score for a question.
	 *
	 * @param mixed $q Question to check
	 * @return mixed Minimum value
	 */
	public function minq($q) {
		static $min = [];
		if (!array_key_exists($q, $min)) {
			$question = $this->questions[$q];
			$min[$q] = 0;
			if (!empty($question['options'])) {
				foreach ($question['options'] as $option) {
					$min[$q] = min($min[$q], $option['value']);
				}
			}
		}
		return $min[$q];
	}

	public function symbol($value) {
		foreach ($this->ratios as $file => $ratio) {
			if ($value >= $ratio) {
				return $file;
			}
		}
		return $file;
	}
}
