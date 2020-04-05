<?php
namespace App\Model\Entity;

use App\Model\Traits\TranslateFieldTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * Affiliate Entity.
 *
 * @property int $id
 * @property string $name
 * @property bool $active
 *
 * @property \App\Model\Entity\Badge[] $badges
 * @property \App\Model\Entity\Category[] $categories
 * @property \App\Model\Entity\Contact[] $contacts
 * @property \App\Model\Entity\Credit[] $credits
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\Franchise[] $franchises
 * @property \App\Model\Entity\Holiday[] $holidays
 * @property \App\Model\Entity\League[] $leagues
 * @property \App\Model\Entity\MailingList[] $mailing_lists
 * @property \App\Model\Entity\Questionnaire[] $questionnaires
 * @property \App\Model\Entity\Question[] $questions
 * @property \App\Model\Entity\Region[] $regions
 * @property \App\Model\Entity\Setting[] $settings
 * @property \App\Model\Entity\Team[] $teams
 * @property \App\Model\Entity\UploadType[] $upload_types
 * @property \App\Model\Entity\Waiver[] $waivers
 * @property \App\Model\Entity\Person[] $people
 */
class Affiliate extends Entity {

	use TranslateTrait;
	use TranslateFieldTrait;

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
