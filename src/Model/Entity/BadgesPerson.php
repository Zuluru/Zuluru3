<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * BadgesPerson Entity.
 *
 * @property int $id
 * @property int $badge_id
 * @property int $person_id
 * @property int $nominated_by_id
 * @property int $game_id
 * @property int $team_id
 * @property int $registration_id
 * @property string $reason
 * @property bool $approved
 * @property int $approved_by_id
 * @property bool $visible
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Badge $badge
 * @property \App\Model\Entity\Person $person
 * @property \App\Model\Entity\Person $nominated_by
 * @property \App\Model\Entity\Game $game
 * @property \App\Model\Entity\Team $team
 * @property \App\Model\Entity\Registration $registration
 * @property \App\Model\Entity\Person $approved_by
 */
class BadgesPerson extends Entity {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*' => true,
		'id' => false,
	];

	public function __construct(array $properties = [], array $options = []) {
		parent::__construct($properties, $options);

		// Make sure whatever extra virtual fields we have are included when we convert to arrays
		if (!empty($this->game_id)) {
			$this->game = TableRegistry::getTableLocator()->get('Games')->get($this->game_id, [
				'contain' => [
					'Divisions' => ['Leagues'],
					'GameSlots',
				]
			]);
			$this->_virtual[] = 'game';
		}

		if (!empty($this->registration_id)) {
			$this->registration = TableRegistry::getTableLocator()->get('Registrations')->get($this->registration_id, [
				'contain' => [
					'Events',
				]
			]);
			$this->_virtual[] = 'registration';
		}

		if (!empty($this->team_id)) {
			$this->team = TableRegistry::getTableLocator()->get('Teams')->get($this->team_id, [
				'contain' => [
					'Divisions' => ['Leagues'],
				]
			]);
			$this->_virtual[] = 'team';
		}
	}

}
