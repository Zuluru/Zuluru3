<?php
namespace App\Policy;

use App\Model\Entity\UploadType;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class UploadTypePolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.documents')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, UploadType $upload_type) {
		return $identity->isManagerOf($upload_type);
	}

	public function canAdd(IdentityInterface $identity, UploadType $upload_type) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, UploadType $upload_type) {
		return $identity->isManagerOf($upload_type);
	}

	public function canDelete(IdentityInterface $identity, UploadType $upload_type) {
		return $identity->isManagerOf($upload_type);
	}

}
