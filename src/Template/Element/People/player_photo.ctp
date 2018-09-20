<?php
/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Upload $photo
 */

$url = $person->photoUrl($photo);
if (!empty($url)) {
	echo $this->Html->image($url, ['class' => 'thumbnail profile-photo', 'title' => $person['full_name']]);
}
