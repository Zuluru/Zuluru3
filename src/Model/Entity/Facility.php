<?php
namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\Utility\Text;

/**
 * Facility Entity.
 * TODO: Add contact info for admins only
 *
 * @property int $id
 * @property bool $is_open
 * @property string $name
 * @property string $code
 * @property string $location_street
 * @property string $location_city
 * @property string $location_province
 * @property string $parking
 * @property string $entrances
 * @property int $region_id
 * @property string $driving_directions
 * @property string $parking_details
 * @property string $transit_directions
 * @property string $biking_directions
 * @property string $washrooms
 * @property string $public_instructions
 * @property string $site_instructions
 * @property string $sponsor
 * @property string $sport
 *
 * @property \App\Model\Entity\Region $region
 * @property \App\Model\Entity\Field[] $fields
 * @property \App\Model\Entity\Team[] $teams
 *
 * @property array $permits
 */
class Facility extends Entity {

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

	// The following virtual field is NOT included in the _virtual list,
	// as it is infrequently needed.
	protected function _getPermits() {
		// If we haven't read the "code" field, we can't find the permit info
		if (empty($this->code)) {
			return [];
		}

		$permits = [];
		$seasons = \App\Lib\seasons();
		foreach ($seasons as $season => $year) {
			$season_slug = Text::slug(strtolower($season), '_');
			$permit_dir = implode(DS, [Configure::read('App.paths.files'), 'permits', $year, $season_slug]);

			// Default setting is the directory name. This may be overwritten later.
			$permits[$season] = ['dir' => $permit_dir];

			// Auto-detect the permit URLs
			if (is_dir($permit_dir)) {
				if ($dh = opendir($permit_dir)) {
					while (($file = readdir($dh)) !== false) {
						if (fnmatch($this->code . '*', $file)) {
							$permits[$season] = [
								'file' => $file,
								'url' => Configure::read('App.filesBaseUrl') . "permits/$year/$season_slug/$file",
							];
							break;
						}
					}
				}
			}
		}

		return $permits;
	}

}
