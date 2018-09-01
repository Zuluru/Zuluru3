<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

if (Configure::read('Perm.is_logged_in')) {
	if (!empty($photo)) {
		$upload_dir = Configure::read('App.paths.uploads');
		if (file_exists ($upload_dir . DS . $photo->filename)) {
			echo $this->Html->image(
				Router::url(['controller' => 'People', 'action' => 'photo', 'person' => $photo->person_id], true),
				['class' => 'thumbnail profile-photo', 'title' => $person['full_name']]
			);
		}
	} else if (Configure::read('feature.gravatar')) {
		$url = 'https://www.gravatar.com/avatar/';
		if ($person->show_gravatar) {
			$url .= md5(strtolower($person->email));
		} else {
			$url .= '00000000000000000000000000000000';
		}
		$url .= "?s=150&d=mm&r=pg";
		echo $this->Html->image($url, ['class' => 'thumbnail profile-photo', 'title' => $person['full_name']]);
	}
}
