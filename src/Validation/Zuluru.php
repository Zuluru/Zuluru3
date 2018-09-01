<?php
namespace App\Validation;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validation;
use App\Core\UserCache;

/**
 * Validation class
 *
 * Provides custom validation functions
 */
class Zuluru extends Validation {
	/**
	 * Used to compare one field to another.
	 *
	 * @param mixed $check1 The value to find in $field.
	 * @param string $operator Can be either a word or operand
	 *    is greater >, is less <, greater or equal >=
	 *    less or equal <=, is less <, equal to ==, not equal !=
	 * @param string $field The field to check $check against. This field must be present in $context.
	 * @param array $context The validation context.
	 * @return bool Success
	 */
	public static function comparisonWith($check1, $operator, $field, $context) {
		if (!isset($context['data'][$field])) {
			return false;
		}
		$check2 = $context['data'][$field];

		$operator = str_replace([' ', "\t", "\n", "\r", "\0", "\x0B"], '', strtolower($operator));
		switch ($operator) {
			case 'isgreater':
			case '>':
				if ($check1 > $check2) {
					return true;
				}
				break;
			case 'isless':
			case '<':
				if ($check1 < $check2) {
					return true;
				}
				break;
			case 'greaterorequal':
			case '>=':
				if ($check1 >= $check2) {
					return true;
				}
				break;
			case 'lessorequal':
			case '<=':
				if ($check1 <= $check2) {
					return true;
				}
				break;
			case 'equalto':
			case '==':
				if ($check1 == $check2) {
					return true;
				}
				break;
			case 'notequal':
			case '!=':
				if ($check1 != $check2) {
					return true;
				}
				break;
			default:
				static::$errors[] = 'You must define the $operator parameter for Zuluru::comparisonWith()';
		}
		return false;
	}

	/**
	 * Enforce unique team names within leagues instead of divisions,
	 * but not in a way that messes with playoff divisions.
	 */
	public static function teamUnique($name, $context) {
		$teams_table = TableRegistry::get('Teams');
		$duplicate = $teams_table->find()->where(compact('name'));

		if (!empty($context['data']['division_id'])) {
			$divisions_table = TableRegistry::get('Divisions');
			$division = $divisions_table->get($context['data']['division_id']);
		} else if (!empty($context['data']['division'])) {
			$division = $context['data']['division'];
		}

		if (isset($division)) {
			// Look at all divisions in the same league
			$duplicate->andWhere(['division_id IN' => $division->sister_divisions]);
		} else {
			$duplicate->andWhere(['division_id IS' => null]);
		}

		if (isset($context['data']['id'])) {
			$duplicate->andWhere(['id !=' => $context['data']['id']]);
		}

		return ($duplicate->count() == 0);
	}

	public static function franchiseOwner($franchise_id) {
		$groups = UserCache::getInstance()->read('Groups');
		if (collection($groups)->firstMatch(['name' => 'Administrator'])) {
			// Admins need to be considered franchise owner for registration edit purposes
			return true;
		}

		$franchises_table = TableRegistry::get('Franchises');
		try {
			$franchise = $franchises_table->get($franchise_id, [
				'contain' => ['People' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['People.id' => UserCache::getInstance()->currentId()]);
					}
				]]
			]);
		} catch (RecordNotFoundException $ex) {
			return false;
		} catch (InvalidPrimaryKeyException $ex) {
			return false;
		}

		if (collection($groups)->firstMatch(['name' => 'Manager']) && in_array($franchise->affiliate_id, UserCache::getInstance()->read('ManagedAffiliateIDs'))) {
			// Managers of the same affiliate need to be considered franchise owner for registration edit purposes
			return true;
		}

		return !empty($franchise->people);
	}

	public static function franchiseUnique($name, $context) {
		$franchises_table = TableRegistry::get('Franchises');
		$duplicate = $franchises_table->find()->where([
			'name' => $name,
			'affiliate_id' => $context['data']['affiliate_id'],
		]);

		if (isset($context['data']['franchise_id'])) {
			$duplicate->andWhere(['id !=' => $context['data']['franchise_id']]);
		}

		return ($duplicate->count() == 0);
	}

}
