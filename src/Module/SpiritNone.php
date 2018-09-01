<?php

/**
 * Derived class for implementing functionality for spirit scoring without any questionnaire.
 */
namespace App\Module;

use Cake\Core\Configure;

class SpiritNone extends Spirit {
	public $render_element = 'none';

	public function __construct() {
		$this->description = __('This selection will result in no spirit questions being asked.');
		parent::__construct();
	}

	public function maxs() {
		return Configure::read('scoring.spirit_max');
	}
}
