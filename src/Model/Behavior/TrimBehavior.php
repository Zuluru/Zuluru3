<?php

/**
 * A behavior that trims spaces from the ends of text fields
 */
namespace App\Model\Behavior;

use ArrayObject;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\Behavior;

class TrimBehavior extends Behavior {
	public function trim(ArrayObject $data) {
		// Check the schema for text types, and trim those fields
		$schema = $this->_table->getSchema();
		foreach ($schema->columns() as $fieldName) {
			$fieldSchema = $schema->getColumn($fieldName);
			if ($this->isTextField($fieldSchema) && $this->isFilled($fieldName, $data)) {
				$data[$fieldName] = trim((string)$data[$fieldName]);
			}
		}
	}

	protected function isTextField($fieldSchema)
	{
		return $fieldSchema['type'] == 'string' || $fieldSchema['type'] == 'text';
	}

	protected function isFilled($fieldName, ArrayObject $data)
	{
		return isset($data[$fieldName]) && !empty($data[$fieldName]);
	}

	/**
	 * Updates data before trying to update the entity.
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options Unused
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		$this->trim($data);
	}

}
