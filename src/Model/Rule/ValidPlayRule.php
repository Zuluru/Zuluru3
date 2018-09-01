<?php
namespace App\Model\Rule;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;

class ValidPlayRule {
	/**
	 * Performs the validity check
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to extract the fields from
	 * @param array $options Options passed to the check
	 * @return bool
	 */
	public function __invoke(EntityInterface $entity, array $options) {
		$field = $options['errorField'];
		$check = $entity->$field;

		$options = array_merge(\App\Config\make_options(array_merge(array_keys(Configure::read("sports.{$entity->game->division->league->sport}.score_options")), ['Start', 'Timeout'])), Configure::read("sports.{$entity->game->division->league->sport}.other_options"));
		return array_key_exists($check, $options);
	}

}
