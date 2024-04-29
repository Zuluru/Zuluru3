<?php
namespace App\Policy;

use App\Model\Entity\Upload;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class UploadPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if ($resource->type_id && !Configure::read('feature.documents')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canPhoto(IdentityInterface $identity, Upload $upload) {
		if ($upload->approved || $identity->getIdentifier() == $upload->person_id) {
			return true;
		}

		if ($upload->type_id) {
			return $identity->isManagerOf($upload);
		}

		// TODO: This needs to check manager of the person, not just manager. Add unit test for this case.
		return $identity->isManager();
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
