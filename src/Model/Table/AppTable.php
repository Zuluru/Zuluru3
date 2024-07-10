<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Log\LogTrait;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\Utility\Inflector;

class AppTable extends Table {

	use LogTrait;

	/**
	 * Convenience function for getting the value of a single field from a single record.
	 *
	 * @param mixed $field The field to retrieve
	 * @param array $conditions Conditions for selecting the record
	 * @param mixed $order The order to sort results in
	 * @return mixed The field, if found, null otherwise
	 */
	public function field($field, array $conditions, $order = null) {
		$query = $this->find()
			->enableHydration(false)
			->select([$field])
			->where($conditions);
		if ($order) {
			$query = $query->order($order);
		}
		$record = $query->first();
		if (!$record) {
			throw new RecordNotFoundException(sprintf(
				'Record not found in table "%s"',
				$this->getTable()
			));
		}

		return $record[$field];
	}

	/**
	 * Use model associations to determine whether a record can be deleted.
	 *
	 * @param mixed $id The id of the record to delete
	 * @param array $ignore Optional list of models to ignore
	 * @param array $ignoreDeep Optional list of models to ignore IF they themselves have no dependencies
	 * @return mixed Text list of dependencies found, or false if none
	 */
	public function dependencies($id, array $ignore = [], array $ignoreDeep = []) {
		if ($id === null) {
			return false;
		}

		// We always want to ignore I18n dependencies
		$ignore[] = 'I18n';

		$dependencies = [];
		$associations = $this->associations();

		/** @var BelongsToMany $association */
		foreach ($associations->getByType('BelongsToMany') as $association) {
			$class = $association->getName();
			$foreign_key = $association->getForeignKey();
			$through = $association->junction()->getAlias();
			$dependent = $association->junction()->find()->where(["$through.$foreign_key" => $id]);

			$association_conditions = $association->getConditions();
			if (!empty($association_conditions)) {
				$dependent->andWhere($association_conditions);
			}

			if (in_array($class, $ignoreDeep) || array_key_exists($class, $ignoreDeep)) {
				foreach ($dependent->extract($association->getTargetForeignKey())->toArray() as $deepId) {
					if (array_key_exists($class, $ignoreDeep)) {
						$deep = $association->dependencies($deepId, $ignoreDeep[$class]);
					} else {
						$deep = $association->dependencies($deepId);
					}
					if ($deep) {
						$dependencies[] = __('{0} {1} (with {2})', __(Inflector::delimit(Inflector::singularize($class), ' ')), $deepId, $deep);
					}
				}
			} else if (!in_array($class, $ignore)) {
				if ($dependent->count() > 0) {
					$dependencies[] = $dependent->count() . ' ' . __(Inflector::delimit($class, ' '));
				}
			}

			// BelongsToMany associations also create HasMany associations for the join tables.
			// Ignore them when we get there.
			$ignore[] = $through;
		}

		/** @var HasMany $association */
		foreach ($associations->getByType('HasMany') as $association) {
			$class = $association->getName();
			if (substr($class, -12) === 'Translations') {
				continue;
			}
			$foreign_key = $association->getForeignKey();
			$dependent = $association->getTarget()->find()->where(["$class.$foreign_key" => $id]);

			$association_conditions = $association->getConditions();
			if (!empty($association_conditions)) {
				$dependent->andWhere($association_conditions);
			}

			if (in_array($class, $ignoreDeep) || array_key_exists($class, $ignoreDeep)) {
				foreach ($dependent->all()->extract($association->getPrimaryKey())->toArray() as $deepId) {
					if (array_key_exists($class, $ignoreDeep)) {
						$deep = $association->dependencies($deepId, $ignoreDeep[$class]);
					} else {
						$deep = $association->dependencies($deepId);
					}
					if ($deep) {
						$dependencies[] = __('{0} {1} (with {2})', __(Inflector::delimit(Inflector::singularize($class), ' ')), $deepId, $deep);
					}
				}
			} else if (!in_array($class, $ignore)) {
				if ($dependent->count() > 0) {
					$dependencies[] = $dependent->count() . ' ' . __(Inflector::delimit($class, ' '));
				}
			}
		}

		if (!empty($dependencies)) {
			return implode(', ', $dependencies);
		}
		return false;
	}

	/**
	 * @param mixed|Entity $entity The entity to clone, or the ID of the entity to read from the database
	 * @param array $options Options to pass to the get function, if required
	 * @return Entity The entity without any IDs in it. Note that this does not actually clone a
	 * provided Entity, just makes it safe to save such that the result will be new rows in the database.
	 */
	public function cloneWithoutIds($entity, $options = []): Entity {
		if (is_numeric($entity)) {
			$entity = $this->get($entity, $options);
		}

		$entity->unset('id');
		return $this->_cloneWithoutIds($entity);
	}

	protected function _cloneWithoutIds(Entity $entity): Entity {
		// Make sure the entity type matches
		if (!is_a($entity, $this->getEntityClass())) {
			trigger_error('Incorrect entity type: ' . get_class($entity) . ' provided, expected ' . $this->getEntityClass(), E_USER_ERROR);
		}

		// Remove the ID, and set the entity as being new
		$entity->setNew(true);

		foreach ($this->associations() as $association) {
			$name = $association->getProperty();
			if ($entity->has($name)) {
				if (is_a($association, \Cake\ORM\Association\HasMany::class)) {
					// If there's a foreign key associated with it, clear that ID too
					$bindingKey = $association->getBindingKey();
					$foreignKey = $association->getForeignKey();
					foreach ($entity->$name as $associated) {
						$associated->unset($bindingKey);
						$associated->unset($foreignKey);
						$association->getTarget()->_cloneWithoutIds($associated);
					}
				} else if (is_a($association, \Cake\ORM\Association\BelongsToMany::class)) {
					$bindingKey = $association->getBindingKey();
					$foreignKey = $association->getForeignKey();
					foreach ($entity->$name as $associated) {
						$associated->_joinData->unset($bindingKey);
						$associated->_joinData->unset($foreignKey);
						$associated->_joinData->setNew(true);
						$association->getTarget()->_cloneWithoutIds($associated);
					}
				} else if (is_a($association, \Cake\ORM\Association\BelongsTo::class)) {
					// Belongs to records remain unchanged, IDs and all.
				} else {
					$association->getTarget()->_cloneWithoutIds($entity->$name);
				}
			}
		}

		return $entity;
	}

}
