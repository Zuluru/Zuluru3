<?php
if ($this->Authorize->getIdentity()->isManager()) {
	echo $this->element('People/search_results', [
		'extra_url' => [
			__('Change password') => ['controller' => 'Users', 'action' => 'change_password', 'url_parameter' => 'user', 'url_field' => 'user_id'],
			__('Act As') => ['controller' => 'People', 'action' => 'act_as'],
		],
	]);
} else {
	echo $this->element('People/search_results');
}
