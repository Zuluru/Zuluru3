<?php
/**
 * Rule helper for checking whether the user has registered for something.
 */
namespace App\Module;

use App\Controller\AppController;
use App\Model\Entity\Team;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class RuleRegistered extends Rule {

	public $reason = 'have previously registered for the prerequisite';

	/**
	 * IDs of events to look for
	 *
	 * @var int[]
	 */
	protected $event_ids;

	/**
	 * Events to look for
	 *
	 * @var string[]
	 */
	protected $events;

	public function parse($config) {
		$config = trim($config, '"\'');
		$this->event_ids = array_map('trim', explode(',', $config));
		if (count(array_unique($this->event_ids)) != count($this->event_ids)) {
			$this->parse_error = __('At least one event has been included more than once in the list.');
			return false;
		}
		$model = TableRegistry::getTableLocator()->get('Events');
		$this->events = $model->find()
			->enableHydration(false)
			->where(['Events.id IN' => $this->event_ids])
			->combine('id', 'name')
			->toArray();
		if (count($this->events) != count($this->event_ids)) {
			$this->parse_error = __('Cannot locate {0} of the specified events.', count($this->event_ids) - count($this->events));
			return false;
		}

		return true;
	}

	// Check if the user has registered for one of the specified events
	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		$events = [];
		if ($text_reason) {
			$events = array_values($this->events);
		} else {
			foreach ($this->events as $key => $event) {
				$url = ['controller' => 'Events', 'action' => 'view', 'event' => $key];
				if ($absolute_url) {
					$url = Router::url($url, true);
				} else {
					$url['return'] = AppController::_return();
				}
				$events[] = [
					'type' => 'link',
					'link' => $event,
					'target' => $url,
				];
			}
		}
		$this->reason = [
			'format' => __('have previously registered for the {0}'),
			'replacements' => [['OR' => $events]],
		];

		if ($params->has('registrations')) {
			return collection($params->registrations ?? [])->some(function ($registration) {
				return in_array($registration->event_id, $this->event_ids);
			});
		}

		return false;
	}

	protected function buildQuery(Query $query, $affiliate) {
		$query->leftJoin(['Registrations' => 'registrations'], 'Registrations.person_id = People.id')
			->where([
				'Registrations.event_id IN' => $this->event_ids,
				'Registrations.payment IN' => Configure::read('registration_reserved'),
			]);

		return true;
	}

	public function desc() {
		return __('Registered');
	}

}
