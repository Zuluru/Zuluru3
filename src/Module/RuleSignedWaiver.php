<?php
/**
 * Rule helper for checking whether the user has signed a required waiver.
 */
namespace App\Module;

use App\Controller\AppController;
use App\Model\Entity\Team;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class RuleSignedWaiver extends Rule {

	public $reason = 'have signed the required waiver';

	/**
	 * Waiver ids to look for
	 *
	 * @var int[]
	 */
	protected $waiver_ids;

	/**
	 * Waiver name
	 *
	 * @var string
	 */
	protected $waiver;

	/**
	 * Date to look at
	 *
	 * @var FrozenDate
	 */
	protected $date;

	public function parse($config) {
		$config = array_map('trim', explode(',', $config));
		foreach ($config as $key => $val) {
			$config[$key] = trim($val, '"\'');
		}
		if (count($config) >= 2) {
			$date = array_pop($config);
			try {
				$this->date = (new FrozenDate($date));
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}.', $date);
				return false;
			}
			$model = TableRegistry::getTableLocator()->get('Waivers');
			try {
				$this->waiver_ids = $config;
				$this->waiver = $model->get($this->waiver_ids[0]);
			} catch (RecordNotFoundException $ex) {
				$this->parse_error = __('Invalid waiver.');
				return false;
			}
			return true;
		} else {
			$this->parse_error = __('Invalid number of parameters to SIGNED_WAIVER rule.');
			return false;
		}
	}

	// Check if the user has signed the required waiver
	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		$url = ['controller' => 'Waivers', 'action' => 'sign', 'waiver' => $this->waiver_ids[0], 'date' => $this->date->toDateString()];
		if ($this->waiver->expiry_type == 'never' && $this->date->isFuture()) {
			$url['date'] = FrozenDate::now()->toDateString();
		}

		if ($text_reason) {
			$this->reason = __('have signed the {0} waiver', $this->waiver->name);
		} else {
			if ($absolute_url) {
				$target = Router::url($url, true);
			} else {
				$url['return'] = AppController::_return();
				$target = $url;
			}
			$this->reason = [
				'format' => 'have {0}',
				'replacements' => [
					[
						'type' => 'link',
						'link' => __('signed the {0} waiver', $this->waiver->name),
						'target' => $target,
					],
				],
			];
		}
		$this->redirect = $url;

		if (!$strict) {
			// Possible for anyone to sign the waiver. Make it invariant so it doesn't generate output on these preliminary screens.
			$this->invariant = true;
			return true;
		}

		if ($params->has('waivers')) {
			if (collection($params->waivers ?? [])->some(function ($waiver) {
				if ($waiver->has('_joinData')) {
					$match = $waiver->_joinData;
				} else if ($waiver->has('_matchingData')) {
					$match = $waiver->_matchingData['WaiversPeople'];
				}
				return $this->date->between($match->valid_from, $match->valid_until) &&
					in_array($waiver->id, $this->waiver_ids);
			})) {
				// The waiver has been signed for this date. Can't be unsigned, so this condition is invariant.
				$this->invariant = true;
				return true;
			}
		}
		$this->invariant = false;
		return false;
	}

	protected function buildQuery(Query $query, $affiliate) {
		$query->leftJoin(['WaiversPeople' => 'waivers_people'], 'WaiversPeople.person_id = People.id')
			->where([
				'WaiversPeople.waiver_id IN' => $this->waiver_ids,
				'WaiversPeople.valid_from <=' => $this->date,
				'WaiversPeople.valid_until >=' => $this->date,
			]);

		return true;
	}

	public function desc() {
		return __('have signed the waiver');
	}

}
