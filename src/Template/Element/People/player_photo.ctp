<?php
/**
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Upload $photo
 */

if ($this->Authorize->can('photo', $person)) {
	$url = $person->photoUrl($photo);
	if (!empty($url)) {
		echo $this->Html->image($url, ['class' => 'thumbnail profile-photo', 'title' => $person['full_name']]);
	}
}
