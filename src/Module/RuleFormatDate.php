<?php
/**
 * Rule for converting a date into a standard format for comparison purposes.
 */
namespace App\Module;

use App\Model\Entity\Team;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\I18n\FrozenDate;

class RuleFormatDate extends Rule {

	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		if ($this->rule == null)
			return null;
		$date = $this->rule->evaluate($affiliate, $params);

		// If the thing we're formatting can't change, then neither can we
		$this->invariant = $this->rule->invariant;

		return (new FrozenDate($date))->toDateString();
	}

	protected function buildQuery(Query $query, $affiliate) {
		if ($this->rule == null)
			return null;
		$date = $this->rule->buildQuery($query, $affiliate);
		return (new FrozenDate($date))->toDateString();
	}

	// Just a formatter, so we simply return our rule's description
	public function desc() {
		if ($this->rule == null)
			return null;

		$date_format = Configure::read('personal.date_format');
		if (empty($date_format)) {
			$date_format = current(Configure::read('options.date_formats'));
		}

		return (new FrozenDate($this->rule->desc()))->i18nFormat($date_format);
	}

}
