<?php
/**
 * Rule helper for returning a user's membership type.
 */
namespace App\Module;

use App\Model\Entity\Registration;
use App\Model\Entity\Team;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class RuleMemberType extends Rule {

	public string $desc = 'have a membership type';

	/**
	 * Date range to look at
	 *
	 * @var FrozenDate[]
	 */
	protected array $range;

	public function parse($config) {
		$config = trim($config, '"\'');
		if ($config[0] == '<') {
			$to = substr($config, 1);
			try {
				$to = (new FrozenDate($to))->subDays(1);
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}', $to);
				return false;
			}
			$this->range = [new FrozenDate('0001-01-01'), $to];
			$this->desc = 'have a past membership type';
		} else if ($config[0] == '>') {
			$from = substr($config, 1);
			try {
				$from = (new FrozenDate($from))->addDays(1);
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}', $from);
				return false;
			}
			$this->range = [$from, new FrozenDate('9999-12-31')];
			$this->desc = 'have an upcoming membership type';
		} else if (strpos($config, ',') !== false) {
			list($from, $to) = explode(',', $config);
			try {
				$from = new FrozenDate($from);
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}', $from);
				return false;
			}
			try {
				$to = new FrozenDate($to);
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}', $to);
				return false;
			}
			$this->range = [$from, $to];
		} else {
			try {
				$date = new FrozenDate($config);
			} catch (\Exception $ex) {
				$this->parse_error = __('Invalid date: {0}', $config);
				return false;
			}
			$this->range = [$date, $date];
		}

		return true;
	}

	// Check if the user was a member on the configured date
	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		if (!$params->has('registrations')) {
			return 'none';
		}

		$memberships = collection($params->registrations ?? [])
			->filter(function (Registration $reg) use ($affiliate) {
				return (!empty($reg->event->membership_begins) &&
					$reg->event->affiliate_id == $affiliate &&
					$reg->event->membership_begins <= $this->range[1] &&
					$this->range[0] <= $reg->event->membership_ends);
			})
			->sortBy(function (Registration $reg) {
				return Configure::read("membership_types.priority.{$reg->event->membership_type}");
			}, SORT_ASC);

		if ($memberships->isEmpty()) {
			return 'none';
		}

		// The first membership in the resulting list is the highest priority one that matches the criteria.
		$reg = $memberships->first();
		if ($reg->event->membership_ends->isPast()) {
			$this->desc = 'have a past membership type';
		} else if ($reg->event->membership_begins->isFuture()) {
			$this->desc = 'have an upcoming membership type';
		}

		return Configure::read("membership_types.map.{$reg->event->membership_type}");
	}

	protected function buildQuery(Query $query, $affiliate) {
		if (!isset($this->events)) {
			$model = TableRegistry::getTableLocator()->get('Events');
			$types = $model->EventTypes->find('list', [
				'conditions' => ['type' => 'membership'],
			])->toArray();
			$events = $model->find()
				->where([
					'Events.event_type_id IN' => array_keys($types),
				]);
			if ($affiliate) {
				$events = $events->where([
					'Events.affiliate_id IN' => $affiliate,
				]);
			}
			$events = $events->toArray();

			foreach ($events as $key => $event) {
				if ($event->membership_begins > $this->range[1] || $event->membership_ends < $this->range[0]) {
					unset($events[$key]);
				}
			}
			$this->events = collection($events ?? [])->extract('id')->toArray();
		}

		$query
			->where([
				'Events.id IN' => $this->events,
				'Registrations.payment' => 'Paid',
			])
			->leftJoin(['Registrations' => 'registrations'], 'Registrations.person_id = People.id')
			->leftJoin(['Events' => 'events'], 'Events.id = Registrations.event_id');

		// TODO: This is almost certainly MySQL-specific
		$type_str = '"membership_type";s:';
		$type_len_pos = "POSITION('$type_str' IN custom) + " . strlen($type_str);
		$type_len_len = "POSITION('\"' IN SUBSTR(custom, $type_len_pos))";
		$type_len = "SUBSTR(custom, $type_len_pos, $type_len_len)";
		$type = "SUBSTR(custom, $type_len_pos + $type_len_len, $type_len)";
		return $type;
	}

	public function desc() {
		return __($this->desc);
	}

}
