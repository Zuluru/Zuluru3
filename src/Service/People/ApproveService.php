<?php
declare(strict_types=1);

namespace App\Service\People;

use App\Controller\AppController;
use App\Exception\ApproveException;
use App\Exception\EmailException;
use App\Model\Entity\Person;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Inflector;

class ApproveService {

	use LocatorAwareTrait;

	private \Cake\ORM\Table $People;

	public function __construct() {
		$this->People = $this->fetchTable('People');
	}

	public function approve(Person $person): void {
		$person->status = 'active';

		if (!$this->People->save($person)) {
			throw new ApproveException(__('Couldn\'t save new member activation'));
		}

		$this->clearCache($person);

		if (!AppController::_sendMail([
			'subject' => function() use ($person) {
				return __('{0} {1} Activation for {2}',
					Configure::read('organization.name'),
					empty($person->user_id) ? __('Profile') : __('Account'),
					empty($person->user_id) ? $person->full_name : $person->user_name
				);
			},
			'template' => 'account_approved',
			'to' => $person,
			'sendAs' => 'both',
			'viewVars' => compact('person'),
		])) {
			throw new EmailException(__('Error sending email to {0}.', $person->full_name));
		}
	}

	public function delete(Person $person): void {
		if (!$this->People->delete($person)) {
			throw new ApproveException(__('Failed to delete {0}.', $person->full_name));
		}

		$this->clearCache($person);
	}

	public function delete_duplicate(Person $person, Person $duplicate): void {
		if (!$this->People->delete($person)) {
			throw new ApproveException(__('Failed to delete {0}.', $person->full_name));
		}

		$this->clearCache($person);
		$this->clearCache($duplicate);

		if (!AppController::_sendMail([
			'subject' => function() { return __('{0} Account Update', Configure::read('organization.name')); },
			'template' => 'account_delete_duplicate',
			'to' => [$person, $duplicate],
			'sendAs' => 'both',
			'viewVars' => compact('person', 'duplicate'),
		])) {
			throw new EmailException(__('Error sending email to {0}.', $person->full_name));
		}
	}

	/**
	 * This is basically the same as delete duplicate, except that some old information (e.g. user ID) is preserved
	 */
	public function merge_duplicate(Person $person, Person $duplicate): void {
		$this->People->getConnection()->transactional(function () use ($duplicate, $person) {
			$duplicate->merge($person);

			// If we are merging, we want to migrate all records that aren't part of the in-memory record.

			// For anything that we have in memory, we must skip doing a direct query
			$ignore = ['Affiliates'];
			$duplicate->setHidden([]);
			foreach ($duplicate->getVisible() as $prop) {
				if ($duplicate->isAccessible($prop) && (is_array($person->$prop))) {
					$ignore[] = Inflector::camelize($prop);
				}
			}

			$associations = $this->People->associations();

			foreach ($associations->getByType('BelongsToMany') as $association) {
				if (!in_array($association->getName(), $ignore)) {
					$foreign_key = $association->getForeignKey();
					$conditions = [$foreign_key => $person->id];
					$association_conditions = $association->getConditions();
					if (!empty($association_conditions)) {
						$conditions += $association_conditions;
					}
					$association->junction()->updateAll([$foreign_key => $duplicate->id], $conditions);
				}

				// BelongsToMany associations also create HasMany associations for the join tables.
				// Ignore them when we get there.
				$ignore[] = $association->junction()->getAlias();
			}

			foreach ($associations->getByType('HasMany') as $association) {
				if (!in_array($association->getName(), $ignore)) {
					$foreign_key = $association->getForeignKey();
					$conditions = [$foreign_key => $person->id];
					$association_conditions = $association->getConditions();
					if (!empty($association_conditions)) {
						$conditions += $association_conditions;
					}
					$association->getTarget()->updateAll([$foreign_key => $duplicate->id], $conditions);
				}
			}

			if (!$this->People->delete($person)) {
				throw new ApproveException(__('Failed to delete {0}.', $person->full_name));
			}

			if (!$this->People->save($duplicate)) {
				throw new ApproveException(__('Couldn\'t save new member information'));
			}
		});

		$this->clearCache($person);
		$this->clearCache($duplicate);

		if (!AppController::_sendMail([
			'subject' => function() { return __('{0} Account Update', Configure::read('organization.name')); },
			'template' => 'account_merge_duplicate',
			'to' => [$person, $duplicate],
			'sendAs' => 'both',
			'viewVars' => compact('person', 'duplicate'),
		])) {
			throw new EmailException(__('Error sending email to {0}.', $person->full_name));
		}
	}

	/**
	 * Clear any related cached information
	 */
	public function clearCache(Person $person): void {
		// TODO: It's conceivable that there could also be stored teams, division, stats, etc. with the deleted person_id in them.
		// For now, we'll just clear everything whenever this happens...
		Cache::clear('long_term');
		/*
		Cache::delete("person_{$person->id}", 'long_term');
		foreach ($person->related as $relative) {
			$this->UserCache->clear('Relatives', $relative->id);
			$this->UserCache->clear('RelativeIDs', $relative->id);
		}
		*/
	}
}
