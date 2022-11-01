<?php
/**
 * @type $person \App\Model\Entity\Person
 * @type $photo \App\Model\Entity\Upload
 */

if ($this->Authorize->can('photo', $person)) {
	$url = $person->photoUrl($photo);
	if (!empty($url)) {
		echo $this->Html->image($url, ['class' => 'thumbnail profile-photo', 'title' => $person['full_name']]);
	}
}
