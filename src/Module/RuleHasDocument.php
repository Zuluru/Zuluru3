<?php
/**
 * Rule helper for checking whether the user has a required document.
 */
namespace App\Module;

use App\Controller\AppController;
use App\Model\Entity\Team;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class RuleHasDocument extends Rule {

	public $reason = 'have uploaded the required document';

	/**
	 * Document id to look for
	 *
	 * @var int
	 */
	protected $document_id;

	/**
	 * Document name
	 *
	 * @var string
	 */
	protected $document;

	/**
	 * Date to look at
	 *
	 * @var FrozenDate
	 */
	protected $date;

	public function parse($config) {
		$config = array_map('trim', explode(',', $config));
		if (count($config) != 2) {
			$this->parse_error = __('Invalid argument count to HAS_DOCUMENT rule');
			return false;
		}

		foreach ($config as $key => $val) {
			$config[$key] = trim($val, '"\'');
		}

		$model = TableRegistry::getTableLocator()->get('UploadTypes');
		try {
			$this->document_id = $config[0];
			$this->document = $model->field('name', ['UploadTypes.id' => $this->document_id]);
		} catch (RecordNotFoundException $ex) {
			$this->parse_error = __('Invalid document.');
			return false;
		}

		try {
			$this->date = (new FrozenDate($config[1]));
		} catch (\Exception $ex) {
			$this->parse_error = __('Invalid date: {0}.', $config[1]);
			return false;
		}

		return true;
	}

	// Check if the user has uploaded the required document
	public function evaluate($affiliate, $params, Team $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false, $formats = []) {
		$matches = collection($params->uploads)->match(['type_id' => $this->document_id]);
		$unapproved = $matches->match(['approved' => false]);

		if ($unapproved->isEmpty()) {
			if ($text_reason) {
				$this->reason = __('have uploaded the {0}', $this->document);
			} else {
				$url = ['controller' => 'People', 'action' => 'document_upload', '?' => ['type' => $this->document_id]];
				if ($absolute_url) {
					$url = Router::url($url, true);
				} else {
					$url['?']['return'] = AppController::_return();
				}
				$this->reason = [
					'format' => 'have {0}',
					'replacements' => [
						[
							'type' => 'link',
							'link' => __('uploaded the {0}', $this->document),
							'target' => $url,
						],
					],
				];
			}
		} else {
			$this->reason = __('wait until the {0} is approved', $this->document);
		}

		if (!$strict) {
			return true;
		}

		if ($params->has('uploads')) {
			return collection($params->uploads)->some(function ($upload) {
				return $upload->type_id == $this->document_id && $upload->valid_from && $this->date->between($upload->valid_from, $upload->valid_until);
			});
		}
		return false;
	}

	protected function buildQuery(Query $query, $affiliate) {
		$query->leftJoin(['Uploads' => 'uploads'], 'Uploads.person_id = People.id')
			->where([
				'Uploads.type_id' => $this->document_id,
				'Uploads.approved' => true,
				'Uploads.valid_from <=' => $this->date,
				'Uploads.valid_until >=' => $this->date,
			]);

		return true;
	}

	public function desc() {
		return __('have the document');
	}

}
