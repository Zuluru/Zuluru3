<?php
namespace App\Model\Table;

use App\Cache\Cache;
use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;

class ConfigurationTable extends AppTable {

	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('settings');
	}

	public function loadSystem() {
		$language = I18n::getLocale();

		$config = Cache::remember('config', function () {
			$conditions = [
				'affiliate_id IS' => null,
				'category !=' => 'personal',
			];

			$settings = $this->format($this->find()->where($conditions)->toArray());

			// Add some gender-related pseudo-settings
			if (Configure::read('offerings.genders') === 'Open' || count(Configure::read('options.gender')) <= 2) {
				$settings['gender.column'] = 'gender';
				$settings['gender.label'] = __('Gender Identification');
				$settings['gender.name'] = __('gender');
			} else {
				$settings['gender.column'] = 'roster_designation';
				$settings['gender.label'] = __('Roster Designation');
				$settings['gender.name'] = __('roster designation');
			}
			$options = array_keys(Configure::read("options.{$settings['gender.column']}"));
			$settings['gender.woman'] = array_shift($options);
			$settings['gender.man'] = array_shift($options);
			$settings['gender.order'] = $settings['gender.woman'] < $settings['gender.man'] ? 'ASC' : 'DESC';

			return $settings;
		}, 'long_term', $language);
		Configure::write($config);

		$provinces = Cache::remember('provinces', function () {
			$provinces_table = TableRegistry::getTableLocator()->get('Provinces');
			return $provinces_table->find('list', [
				'keyField' => 'name',
				'valueField' => 'name',
			])->toArray();
		}, 'long_term', $language);
		Configure::write(compact('provinces'));

		$countries = Cache::remember('countries', function () {
			$countries_table = TableRegistry::getTableLocator()->get('Countries');
			return $countries_table->find('list', [
				'keyField' => 'name',
				'valueField' => 'name',
			])->toArray();
		}, 'long_term', $language);
		Configure::write(compact('countries'));

		Configure::write(Cache::remember('roster_roles', function () {
			$roster_roles_table = TableRegistry::getTableLocator()->get('RosterRoles');
			$roles = collection($roster_roles_table->find()
				->where(['active' => true])
				->toArray()
			);

			$configuration = [
				// TODOFUO: How to handle translation of the descriptions?
				'options.roster_role' => $roles->combine('name', 'description')->toArray(),
				'playing_roster_roles' => $roles->match(['is_player' => true])->extract('name')->toArray(),
				'extended_playing_roster_roles' => $roles->match(['is_extended_player' => true])->extract('name')->toArray(),
				'regular_roster_roles' => $roles->match(['is_regular' => true])->extract('name')->toArray(),
				'privileged_roster_roles' => $roles->match(['is_privileged' => true])->extract('name')->toArray(),
				'required_roster_roles' => $roles->match(['is_required' => true])->extract('name')->toArray(),
			];

			return $configuration;
		}, 'long_term', $language));

		Configure::write(Cache::remember('membership_types', function () {
			$membership_types_table = TableRegistry::getTableLocator()->get('MembershipTypes');
			$types = collection($membership_types_table->find()
				->where(['active' => true])
				->toArray()
			);

			$configuration = [
				// TODO: How to handle translation of the descriptions?
				'options.membership_types' => $types->combine('name', 'description')->toArray(),
				'membership_types.priority' => $types->combine('name', 'priority')->toArray(),
				'membership_types.map' => $types->combine('name', 'report_as')->toArray(),
				'membership_types.badge' => $types->combine('name', 'badge')->toArray(),
			];

			return $configuration;
		}, 'long_term', $language));
	}

	public function loadAffiliate($id) {
		if (!$id || !Configure::read('feature.affiliates')) {
			return;
		}

		$config = Cache::remember("config/affiliate/$id", function () use ($id) {
			return $this->format($this->find()->where(['affiliate_id' => $id])->toArray());
		}, 'long_term', I18n::getLocale());
		Configure::write($config);
	}

	public function loadUser($id) {
		if (!$id) {
			return [];
		}

		$config = $this->format($this->find()->where(['person_id' => $id])->toArray());
		Configure::write($config);
	}

	protected function format($settings) {
		$ret = [];
		foreach ($settings as $setting) {
			$ret["$setting->category.$setting->name"] = $setting->value;
		}
		return $ret;
	}

}
