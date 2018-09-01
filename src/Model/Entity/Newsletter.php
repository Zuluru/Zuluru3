<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Newsletter Entity.
 *
 * @property int $id
 * @property string $name
 * @property string $from_email
 * @property string $to_email
 * @property string $subject
 * @property string $text
 * @property \Cake\I18n\FrozenTime $target
 * @property int $delay
 * @property int $batch_size
 * @property bool $personalize
 * @property \Cake\I18n\FrozenTime $created
 * @property int $mailing_list_id
 * @property string $reply_to
 *
 * @property \App\Model\Entity\MailingList $mailing_list
 * @property \App\Model\Entity\ActivityLog[] $deliveries
 */
class Newsletter extends Entity {

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

}
