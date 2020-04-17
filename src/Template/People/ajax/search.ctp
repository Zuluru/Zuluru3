<?php
if ($this->Authorize->getIdentity()->isManager()) {
	echo $this->element('People/search_results', [
		'extra_url' => [
			__('Change Password') => ['controller' => 'Users', 'action' => 'change_password', 'url_parameter' => 'user', 'url_field' => 'user_id'],
			__('Act As') => ['controller' => 'People', 'action' => 'act_as'],
			__('Add Credit') => ['controller' => 'Credits', 'action' => 'add'],
		],
	]);
} else {
	echo $this->element('People/search_results');
}
