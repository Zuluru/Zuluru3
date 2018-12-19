<?php
namespace App\Policy;

use App\Model\Entity\Upload;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class UploadPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.documents')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canDocument(IdentityInterface $identity, Upload $upload) {
		return $identity->isManagerOf($upload);
	}

	public function canApprove_document(IdentityInterface $identity, Upload $upload) {
		return $identity->isManagerOf($upload);
	}

	public function canEdit_document(IdentityInterface $identity, Upload $upload) {
		return $identity->isManagerOf($upload);
	}

	public function canDelete_document(IdentityInterface $identity, Upload $upload) {
		return $identity->isManagerOf($upload);
	}

}
